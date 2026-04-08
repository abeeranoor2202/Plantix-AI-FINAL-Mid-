<?php

namespace App\Services\Shared;

use App\Events\OrderStatusUpdated;
use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Coupon;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly WalletService       $wallet,
        private readonly NotificationService $notifications,
    ) {}

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    /**
     * Place a new order.
     *
     * @param  User    $user
     * @param  Vendor  $vendor
     * @param  array   $items   [ ['product'=>Product, 'qty'=>int, 'addons'=>[]] ]
     * @param  array   $data    delivery_address, delivery_lat, delivery_lng, notes,
     *                          payment_method, coupon_code
     */
    public function place(User $user, Vendor $vendor, array $items, array $data): Order
    {
        return DB::transaction(function () use ($user, $vendor, $items, $data) {
            $subtotal = collect($items)->sum(
                fn($i) => $i['product']->effective_price * $i['qty']
            );

            $discountAmount = 0;
            $coupon         = null;
            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();
                if ($coupon) {
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                }
            }

            $taxAmount   = $subtotal * (config('app.tax_rate', 0) / 100);
            $deliveryFee = $vendor->delivery_fee;
            $total       = $subtotal + $deliveryFee + $taxAmount - $discountAmount;

            $order = Order::create([
                'order_number'    => 'ORD-' . strtoupper(Str::random(8)),
                'user_id'         => $user->id,
                'vendor_id'       => $vendor->id,
                'coupon_id'       => $coupon?->id,
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'delivery_fee'    => $deliveryFee,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'total'           => $total,
                'payment_method'  => $data['payment_method'] ?? null,
                'payment_status'  => 'pending',
                'delivery_address'=> $data['delivery_address'] ?? null,
                'delivery_lat'    => $data['delivery_lat'] ?? null,
                'delivery_lng'    => $data['delivery_lng'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $product->id,
                    'product_name'=> $product->name,          // snapshot
                    'quantity'    => $item['qty'],
                    'unit_price'  => $product->effective_price,
                    'total_price' => $product->effective_price * $item['qty'],
                    'addons'      => $item['addons'] ?? null,
                ]);
            }

            // Notify vendor
            SendOrderNotification::dispatch($order, 'pending');

            return $order;
        });
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    public function updateStatus(Order $order, string $newStatus, ?int $driverId = null): Order
    {
        $this->guardTransition($order->status, $newStatus);

        DB::transaction(function () use ($order, $newStatus, $driverId) {
            $updates = ['status' => $newStatus];
            if ($driverId) $updates['driver_id'] = $driverId;

            $order->update($updates);

            if ($newStatus === 'delivered') {
                $this->wallet->settleVendorPayout($order);
                $this->markOrderPaid($order);
            }

            if (in_array($newStatus, ['rejected', 'cancelled'])) {
                $this->processRefundIfPaid($order);
            }
        });

        // Broadcast + push notification (runs in queue)
        event(new OrderStatusUpdated($order->fresh()));
        SendOrderNotification::dispatch($order->fresh(), $newStatus);

        return $order->fresh();
    }

    private function markOrderPaid(Order $order): void
    {
        if ($order->payment_method !== 'cod') {
            $order->update(['payment_status' => 'paid']);
        }
    }

    private function processRefundIfPaid(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            $order->update(['payment_status' => 'refunded']);
        }
    }

    /**
     * Enforce valid status transition paths.
     */
    private function guardTransition(string $current, string $next): void
    {
        $allowed = [
            'pending'          => ['confirmed', 'cancelled', 'rejected'],
            'confirmed'        => ['processing', 'cancelled'],
            'processing'       => ['shipped', 'cancelled'],
            'shipped'          => ['delivered'],
            'delivered'        => ['return_requested'],
            'return_requested' => ['returned', 'delivered'],
            'returned'         => [],
            'rejected'         => [],
            'cancelled'        => [],
        ];

        if (!in_array($next, $allowed[$current] ?? [])) {
            throw new \InvalidArgumentException(
                "Cannot transition order from '{$current}' to '{$next}'."
            );
        }
    }
}


