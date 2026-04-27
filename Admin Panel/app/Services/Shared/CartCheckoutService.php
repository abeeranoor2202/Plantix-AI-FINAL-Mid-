<?php

namespace App\Services\Shared;

use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentSucceeded;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CouponUserUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderShippedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use App\Services\Shared\StripeService;

/**
 * CartCheckoutService ΓÇö production-ready Stripe payment flow.
 *
 * Correct Stripe flow:
 *  1. Validate cart + stock (optimistic pre-check)
 *  2. Validate coupon
 *  3. Create order in status=pending_payment (NO stock deduction yet)
 *  4. Create Stripe PaymentIntent (idempotent key per order)
 *  5. Return client_secret to frontend
 *
 *  On payment_intent.succeeded webhook:
 *  6. DB transaction with SELECT...FOR UPDATE on product rows
 *  7. Verify stock again (prevent oversell)
 *  8. Deduct stock atomically
 *  9. Move order -> confirmed, payment -> completed
 * 10. Clear cart
 * 11. Queue notification
 *
 * Race condition: Two buyers, last item in stock.
 *  - Both pass optimistic stock check.
 *  - Both create orders.
 *  - Both get Stripe intents.
 *  - Both pay.
 *  - First webhook transaction acquires lockForUpdate ΓÇö decrements to 0.
 *  - Second webhook transaction acquires lock ΓÇö sees stock_quantity < needed.
 *  - Second order moved to cancelled + payment marked pending_refund.
 *  - Admin alert sent. Stripe refund issued separately.
 */
class CartCheckoutService
{
    public function __construct(
        private readonly StockService $stock,
        private readonly CouponService $couponService,
        private readonly StripeService $stripeService,
        private readonly MarketplacePayoutService $payouts,
    ) {}

    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
    // Step 1-5: Initiate checkout (create order + Stripe PI)
    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ

    /**
     * Create an order in pending_payment state and a Stripe PaymentIntent.
     * Does NOT deduct stock.
     *
     * @return array{order: Order, client_secret: string}
     * @throws ValidationException|\Throwable
     */
    public function initiate(User $user, array $data): array
    {
        if (! \App\Models\Setting::get('stripe_enabled', true)) {
            throw ValidationException::withMessages(['payment' => 'Stripe payment is currently disabled.']);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $cart = Cart::with('items.product.stock')
                    ->where('user_id', $user->id)
                    ->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (! $product || $product->status !== 'active') {
                throw ValidationException::withMessages(['cart' => "'{$product?->name}' is no longer available."]);
            }

            if ($product->vendor && $product->vendor->is_suspended ?? false) {
                throw ValidationException::withMessages(['cart' => "Vendor for '{$product->name}' is suspended."]);
            }

            $this->stock->assertPhysicalStock($product, $item->quantity);
        }

        // Coupon validation
        $coupon         = null;
        $discountAmount = 0.00;

        if (! empty($data['coupon_code'])) {
            $coupon         = $this->validateCoupon($data['coupon_code'], $user, $cart);
            $discountAmount = $this->calculateDiscount($coupon, $cart);
        }

        // Create order (no stock deduction)
        $order = DB::transaction(function () use ($user, $cart, $data, $coupon, $discountAmount) {
            $subtotal    = (float) $cart->subtotal;
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0.00);
            $taxAmount   = round($subtotal * (float) config('plantix.tax_rate', 0.0), 2);
            $total       = max(0.0, $subtotal + $deliveryFee + $taxAmount - $discountAmount);
            $vendorId    = $this->resolveOrderVendorId($cart);

            $order = Order::create([
                'user_id'          => $user->id,
                'vendor_id'        => $vendorId,
                'coupon_id'        => $coupon?->id,
                'status'           => Order::STATUS_PENDING_PAYMENT,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total'            => $total,
                'payment_method'   => 'stripe',
                'payment_status'   => 'pending',
                'delivery_address' => $data['delivery_address'],
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item->product_id,
                    'product_name'    => $item->product->name,
                    'quantity'        => $item->quantity,
                    'unit_price'      => $item->unit_price,
                    'discount_amount' => 0.00,
                    'total_price'     => $item->unit_price * $item->quantity,
                    'addons'          => $item->addons,
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
                CouponUserUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id'   => $user->id,
                    'order_id'  => $order->id,
                ]);
                CouponUsage::create([
                    'coupon_id'       => $coupon->id,
                    'user_id'         => $user->id,
                    'order_id'        => $order->id,
                    'discount_amount' => $discountAmount,
                    'used_at'         => now(),
                ]);
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => Order::STATUS_PENDING_PAYMENT,
                'notes'      => 'Order created. Awaiting payment.',
                'changed_by' => $user->id,
            ]);

            return $order;
        });

        // Create Stripe Checkout Session
        try {
            $checkout = $this->stripeService->createOrderCheckoutSession($order, [
                'order_number' => $order->order_number,
            ]);

            $intent = $checkout['paymentIntent'] ?? null;

            DB::transaction(function () use ($order, $checkout, $intent) {
                $order->update([
                    'payment_intent_id' => $intent?->id,
                ]);

                Payment::updateOrCreate(
                    ['order_id' => $order->id, 'gateway' => 'stripe'],
                    [
                        'user_id'                 => $order->user_id,
                        'gateway_transaction_id'  => $intent?->id,
                        'stripe_session_id'       => $checkout['session']->id,
                        'stripe_payment_intent_id'=> $intent?->id,
                        'payment_type'            => 'product',
                        'amount'                  => $order->total,
                        'currency'                => strtolower(config('plantix.currency_code', 'usd')),
                        'status'                  => 'pending',
                        'metadata'                => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'checkout_url' => $checkout['checkout_url'] ?? null,
                        ],
                    ]
                );
            });
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $order->update(['status' => Order::STATUS_CANCELLED]);
            Log::error('Stripe PI creation failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'payment' => 'Payment service unavailable. Please try again.',
            ]);
        }

        return [
            'order'         => $order->fresh(['items', 'vendor']),
            'client_secret' => $intent?->client_secret,
            'checkout_url'  => $checkout['checkout_url'] ?? null,
        ];
    }

    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
    // Step 6-11: Confirm after webhook
    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ

    /**
     * Confirm payment after payment_intent.succeeded webhook.
     * Atomically deducts stock. Idempotent.
     */
    public function confirmPayment(string $paymentIntentId): Order
    {
        return DB::transaction(function () use ($paymentIntentId) {
            $payment = Payment::where('gateway_transaction_id', $paymentIntentId)
                               ->orWhere('stripe_payment_intent_id', $paymentIntentId)
                               ->lockForUpdate()
                               ->firstOrFail();

            if ($payment->status === 'completed') {
                return Order::findOrFail($payment->order_id);
            }

            $order = Order::lockForUpdate()->findOrFail($payment->order_id);

            if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
                Log::warning('Stripe webhook: order not in pending_payment', [
                    'order_id' => $order->id, 'status' => $order->status,
                ]);
                return $order;
            }

            // Deduct stock with row-level locking
            $order->loadMissing('items.product');
            foreach ($order->items as $item) {
                if (! $item->product) continue;

                $locked = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($locked->track_stock && $locked->stock_quantity < $item->quantity) {
                    // Stock exhausted after payment ΓÇö cancel + alert admin
                    $order->update([
                        'status'         => Order::STATUS_CANCELLED,
                        'payment_status' => 'pending_refund',
                    ]);
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status'   => Order::STATUS_CANCELLED,
                        'notes'    => "Stock exhausted for '{$locked->name}' after payment. Manual refund required.",
                    ]);
                    $payment->update(['status' => 'completed']);

                    $cart = Cart::where('user_id', $order->user_id)->with('items.product')->first();
                    if ($cart) {
                        foreach ($cart->items as $cartItem) {
                            if ($cartItem->product) {
                                $this->stock->releaseReservedStock(
                                    product: $cartItem->product,
                                    qty: (int) $cartItem->quantity,
                                    reference: 'cart:' . $cart->id,
                                    initiatedBy: $order->user_id,
                                );
                            }
                        }
                    }

                    Log::error('Stock exhausted post-payment', [
                        'order_id'   => $order->id,
                        'product_id' => $locked->id,
                    ]);
                    return $order;
                }

                $this->stock->decrementStock($locked, $item->quantity, $order->id, $order->user_id);
            }

            $payment->update(['status' => 'completed', 'paid_at' => now()]);

            $order->update([
                'status'         => Order::STATUS_CONFIRMED,
                'payment_status' => 'paid',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status'   => Order::STATUS_CONFIRMED,
                'notes'    => 'Payment confirmed. Stock deducted.',
            ]);

            Cart::where('user_id', $order->user_id)->get()->each(function ($cart) {
                $cart->items()->delete();
                $cart->delete();
            });

            event(new PaymentSucceeded(
                order: $order,
                payment: $payment,
                amount: (float) $payment->amount,
                transactionId: $paymentIntentId,
            ));

            try {
                $this->payouts->settleOrder($order->fresh(['vendor.author']));
            } catch (\Throwable $e) {
                Log::error('Order payout settlement failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * Handle payment_intent.payment_failed webhook.
     * Moves order to payment_failed, reverses coupon usage.
     */
    public function handlePaymentFailed(string $paymentIntentId): void
    {
        DB::transaction(function () use ($paymentIntentId) {
            $payment = Payment::where('gateway_transaction_id', $paymentIntentId)
                               ->orWhere('stripe_payment_intent_id', $paymentIntentId)
                               ->lockForUpdate()->first();

            if (! $payment || $payment->status !== 'pending') return;

            $payment->update(['status' => 'failed']);

            $order = Order::lockForUpdate()->find($payment->order_id);
            if (! $order) return;

            $order->update([
                'status'         => Order::STATUS_PAYMENT_FAILED,
                'payment_status' => 'failed',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status'   => Order::STATUS_PAYMENT_FAILED,
                'notes'    => 'Stripe payment failed.',
            ]);

            if ($order->coupon_id) {
                Coupon::where('id', $order->coupon_id)->decrement('used_count');
                CouponUserUsage::where('order_id', $order->id)->delete();
            }

            event(new PaymentFailed(
                order: $order,
                amount: (float) $payment->amount,
                failureReason: 'Stripe payment failed during checkout.',
                transactionId: $paymentIntentId,
            ));

            $cart = Cart::where('user_id', $order->user_id)->with('items.product')->first();
            if ($cart) {
                foreach ($cart->items as $item) {
                    if ($item->product) {
                        $this->stock->releaseReservedStock(
                            product: $item->product,
                            qty: (int) $item->quantity,
                            reference: 'cart:' . $cart->id,
                            initiatedBy: $order->user_id,
                        );
                    }
                }
            }
        });
    }

    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
    // Status transition (Admin/Vendor)
    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ

    /**
     * @throws \DomainException
     */
    public function updateStatus(Order $order, string $newStatus, ?string $notes, User $changedBy): Order
    {
        $isAdmin = ($changedBy->role === 'admin');

        if (! $order->canTransitionTo($newStatus)) {
            if ($isAdmin && $order->adminCanForceTo($newStatus)) {
                // allowed
            } else {
                throw new \DomainException(
                    "Cannot transition order #{$order->id} from '{$order->status}' to '{$newStatus}'."
                );
            }
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === Order::STATUS_DELIVERED && is_null($order->delivered_at)) {
            $updateData['delivered_at'] = now();
        }

        DB::transaction(function () use ($order, $newStatus, $notes, $changedBy, $updateData) {
            $order->update($updateData);

            if (in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_REJECTED], true) && $order->isPaid()) {
                $order->loadMissing('items.product');
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $this->stock->restoreStock(
                            product:     $item->product,
                            qty:         $item->quantity,
                            reason:      'cancel',
                            orderId:     $order->id,
                            returnId:    null,
                            initiatedBy: $changedBy->id,
                        );
                    }
                }

                if ($order->coupon_id) {
                    Coupon::where('id', $order->coupon_id)->decrement('used_count');
                    CouponUserUsage::where('order_id', $order->id)->delete();
                }
            } elseif (
                in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_REJECTED], true)
                && $order->getOriginal('status') === Order::STATUS_PENDING_PAYMENT
            ) {
                $cart = Cart::where('user_id', $order->user_id)->with('items.product')->first();
                if ($cart) {
                    foreach ($cart->items as $item) {
                        if ($item->product) {
                            $this->stock->releaseReservedStock(
                                product: $item->product,
                                qty: (int) $item->quantity,
                                reference: 'cart:' . $cart->id,
                                initiatedBy: $changedBy->id,
                            );
                        }
                    }
                }
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $newStatus,
                'notes'      => $notes,
                'changed_by' => $changedBy->id,
            ]);
        });

        $freshOrder = $order->fresh(['items', 'vendor']);

        try {
            $user = $freshOrder->user;
            // Send specific notifications for key statuses
            if ($newStatus === Order::STATUS_SHIPPED) {
                $user->notify(new OrderShippedNotification($freshOrder));
            } elseif ($newStatus === Order::STATUS_DELIVERED) {
                $user->notify(new OrderDeliveredNotification($freshOrder));
            } else {
                $user->notify(new OrderStatusChangedNotification($freshOrder, $newStatus));
            }
        } catch (\Throwable $e) {
            Log::error('Status notification failed', ['order_id' => $order->id, 'status' => $newStatus]);
        }

        return $freshOrder;
    }

    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
    // COD order placement
    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ

    /**
     * Place a COD order. Stock deducted immediately.
     *
     * @throws ValidationException|\Throwable
     */
    public function placeCodOrder(User $user, array $data): Order
    {
        if (! \App\Models\Setting::get('cod_enabled', true)) {
            throw ValidationException::withMessages(['payment' => 'Cash on Delivery is currently disabled.']);
        }

        $cart = Cart::with('items.product.stock')->where('user_id', $user->id)->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        foreach ($cart->items as $item) {
            if (! $item->product || $item->product->status !== 'active') {
                throw ValidationException::withMessages([
                    'cart' => "Product '{$item->product?->name}' is unavailable.",
                ]);
            }
            $this->stock->assertPhysicalStock($item->product, $item->quantity);
        }

        $coupon         = null;
        $discountAmount = 0.00;

        if (! empty($data['coupon_code'])) {
            $coupon         = $this->validateCoupon($data['coupon_code'], $user, $cart);
            $discountAmount = $this->calculateDiscount($coupon, $cart);
        }

        $order = DB::transaction(function () use ($user, $cart, $data, $coupon, $discountAmount) {
            $subtotal    = (float) $cart->subtotal;
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0.00);
            $taxAmount   = round($subtotal * (float) config('plantix.tax_rate', 0.0), 2);
            $total       = max(0.0, $subtotal + $deliveryFee + $taxAmount - $discountAmount);
            $vendorId    = $this->resolveOrderVendorId($cart);

            $order = Order::create([
                'user_id'          => $user->id,
                'vendor_id'        => $vendorId,
                'coupon_id'        => $coupon?->id,
                'status'           => Order::STATUS_PENDING,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total'            => $total,
                'payment_method'   => 'cod',
                'payment_status'   => 'pending',
                'delivery_address' => $data['delivery_address'],
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $locked = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($locked->track_stock && $locked->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Insufficient stock for '{$locked->name}'.",
                    ]);
                }

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $locked->name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'total_price'  => $item->unit_price * $item->quantity,
                    'addons'       => $item->addons,
                ]);

                $this->stock->decrementStock($locked, $item->quantity, $order->id, $user->id);
            }

            if ($coupon) {
                $coupon->increment('used_count');
                CouponUserUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id'   => $user->id,
                    'order_id'  => $order->id,
                ]);
                CouponUsage::create([
                    'coupon_id'       => $coupon->id,
                    'user_id'         => $user->id,
                    'order_id'        => $order->id,
                    'discount_amount' => $discountAmount,
                    'used_at'         => now(),
                ]);
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => Order::STATUS_PENDING,
                'notes'      => 'COD order placed.',
                'changed_by' => $user->id,
            ]);

            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        try {
            $order->user->notify(new OrderPlacedNotification($order));
        } catch (\Throwable $e) {
            Log::error('COD order notification failed', ['order_id' => $order->id]);
        }

        return $order->fresh(['items', 'vendor']);
    }

    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
    // Private helpers
    // ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ

    private function validateCoupon(string $code, User $user, Cart $cart): Coupon
    {
        return $this->couponService->findAndValidateForCart($code, $user, $cart);
    }

    private function calculateDiscount(Coupon $coupon, Cart $cart): float
    {
        return $this->couponService->calculateDiscountForCart($coupon, $cart);
    }

    private function resolveOrderVendorId(Cart $cart): int
    {
        $vendorId = $cart->items
            ->first(fn ($item) => (int) ($item->product?->vendor_id ?? 0) > 0)
            ?->product
            ?->vendor_id;

        return (int) ($vendorId ?: $cart->vendor_id);
    }
}
