<?php

namespace App\Services\Shared;

use App\Events\OrderStatusUpdated;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUserUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * CartCheckoutService
 *
 * Encapsulates the full order-placement flow:
 *  1. Validate cart & stock
 *  2. Apply coupon / discount
 *  3. Persist order + items inside a DB transaction
 *  4. Decrement stock
 *  5. Clear cart
 *  6. Dispatch notifications via queue
 */
class CartCheckoutService
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    /**
     * Place an order from the authenticated user's cart.
     *
     * @param  User   $user
     * @param  array  $data  Validated checkout payload
     * @return Order
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function placeOrder(User $user, array $data): Order
    {
        $cart = Cart::with('items.product.stock')->where('user_id', $user->id)->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        // ── Stock pre-check (optimistic, confirmed inside transaction) ────────
        foreach ($cart->items as $item) {
            $this->stock->assertSufficientStock($item->product, $item->quantity);
        }

        // ── Coupon validation ─────────────────────────────────────────────────
        $coupon         = null;
        $discountAmount = 0.00;

        if (! empty($data['coupon_code'])) {
            $coupon         = $this->validateCoupon($data['coupon_code'], $user, $cart);
            $discountAmount = $this->calculateDiscount($coupon, $cart->subtotal);
        }

        // ── DB transaction ────────────────────────────────────────────────────
        $order = DB::transaction(function () use ($user, $cart, $data, $coupon, $discountAmount) {

            $subtotal   = $cart->subtotal;
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0.00);
            $taxAmount  = round($subtotal * config('plantix.tax_rate', 0.0), 2);
            $total      = max(0, $subtotal + $deliveryFee + $taxAmount - $discountAmount);

            $order = Order::create([
                'user_id'          => $user->id,
                'vendor_id'        => $cart->vendor_id,
                'coupon_id'        => $coupon?->id,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total'            => $total,
                'payment_method'   => $data['payment_method'] ?? 'cod',
                'payment_status'   => 'pending',
                'delivery_address' => $data['delivery_address'],
                'notes'            => $data['notes'] ?? null,
            ]);

            // ── Persist order items & decrement stock (pessimistic lock prevents oversell race) ──
            foreach ($cart->items as $item) {
                $lockedProduct = Product::lockForUpdate()->find($item->product_id);
                if (! $lockedProduct || ($lockedProduct->stock?->quantity ?? 0) < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Insufficient stock for '{$item->product->name}'. Please update your cart.",
                    ]);
                }

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'total_price'  => $item->unit_price * $item->quantity,
                    'addons'       => $item->addons,
                ]);

                $this->stock->decrementStock($lockedProduct, $item->quantity);
            }

            // ── Increment coupon usage + record per-user usage ───────────────────────
            if ($coupon) {
                $coupon->increment('used_count');
                CouponUserUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id'   => $user->id,
                    'order_id'  => $order->id,
                ]);
            }

            // ── Status history seed ───────────────────────────────────────────
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'notes'      => 'Order placed by customer.',
                'changed_by' => $user->id,
            ]);

            // ── Clear cart ────────────────────────────────────────────────────
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        // ── Post-transaction: fire events & notifications (queued) ─────────--
        try {
            $order->user->notify(new OrderPlacedNotification($order));
            event(new OrderStatusUpdated($order));
        } catch (\Throwable $e) {
            Log::error('Post-order notification failed: ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        return $order->fresh(['items', 'vendor']);
    }

    /**
     * Update order status (Admin or Vendor).
     */
    public function updateStatus(Order $order, string $newStatus, ?string $notes, User $changedBy): Order
    {
        $updateData = ['status' => $newStatus];

        // Stamp delivered_at when transitioning to delivered
        if ($newStatus === 'delivered' && is_null($order->delivered_at)) {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'status'     => $newStatus,
            'notes'      => $notes,
            'changed_by' => $changedBy->id,
        ]);

        try {
            $order->user->notify(new OrderStatusChangedNotification($order, $newStatus));
            event(new OrderStatusUpdated($order));
        } catch (\Throwable $e) {
            Log::error('Status notification failed: ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        return $order->fresh();
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function validateCoupon(string $code, User $user, Cart $cart): Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
        }

        if (! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is no longer active.']);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        // Per-user usage check — prevent using the same coupon multiple times
        $alreadyUsed = CouponUserUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyUsed) {
            throw ValidationException::withMessages(['coupon_code' => 'You have already used this coupon.']);
        }

        if ($coupon->min_order_value && $cart->subtotal < $coupon->min_order_value) {
            throw ValidationException::withMessages([
                'coupon_code' => "Order subtotal must be at least {$coupon->min_order_value} to use this coupon.",
            ]);
        }

        return $coupon;
    }

    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ($coupon->discount / 100);
            return $coupon->max_discount ? min($discount, (float) $coupon->max_discount) : $discount;
        }

        // Fixed amount
        return min((float) $coupon->discount, $subtotal);
    }
}


