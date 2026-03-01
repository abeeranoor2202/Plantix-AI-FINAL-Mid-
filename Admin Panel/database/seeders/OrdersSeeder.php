<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * OrdersSeeder — 55 orders with items, payments, and status history.
 *
 * Status distribution:
 *   30 delivered  (payment: paid)
 *    8 cancelled  (payment: pending or failed)
 *    5 pending    (payment: pending)
 *    5 preparing / accepted
 *    4 rejected
 *    3 refunded
 */
class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        $now        = Carbon::now();
        $customers  = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $vendors    = DB::table('vendors')->select('id')->orderBy('id')->get()->toArray();
        $products   = DB::table('products')->select('id', 'vendor_id', 'name', 'price', 'discount_price')->get()->groupBy('vendor_id');
        $coupons    = DB::table('coupons')->where('is_active', 1)->pluck('id')->toArray();

        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id') ?? 1;

        // Pre-build order scenarios
        $scenarios = $this->buildScenarios($customers, $vendors, $products, $coupons, $now);

        $orderNumber = 10001;
        foreach ($scenarios as $s) {
            // Calculate totals
            $subtotal  = 0;
            foreach ($s['items'] as &$item) {
                $item['total_price'] = $item['unit_price'] * $item['quantity'];
                $subtotal           += $item['total_price'];
            }
            unset($item);

            $deliveryFee     = $s['payment_method'] === 'cod' ? 200 : 150;
            $discountAmount  = $s['coupon_id'] ? min(round($subtotal * 0.10), 800) : 0;
            $taxAmount       = round($subtotal * 0.05, 2);
            $total           = $subtotal + $deliveryFee + $taxAmount - $discountAmount;
            $total           = max($total, 100);

            $orderId = DB::table('orders')->insertGetId([
                'order_number'      => 'ORD-' . $orderNumber++,
                'user_id'           => $s['user_id'],
                'vendor_id'         => $s['vendor_id'],
                'driver_id'         => null,
                'coupon_id'         => $s['coupon_id'],
                'status'            => $s['status'],
                'subtotal'          => $subtotal,
                'delivery_fee'      => $deliveryFee,
                'tax_amount'        => $taxAmount,
                'discount_amount'   => $discountAmount,
                'total'             => $total,
                'payment_method'    => $s['payment_method'],
                'payment_status'    => $s['payment_status'],
                'delivery_address'  => $s['address'],
                'notes'             => $s['notes'],
                'delivered_at'      => $s['status'] === 'delivered' ? $s['created_at']->copy()->addDays(3) : null,
                'created_at'        => $s['created_at'],
                'updated_at'        => $now,
            ]);

            // Order items
            foreach ($s['items'] as $item) {
                DB::table('order_items')->insert([
                    'order_id'     => $orderId,
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['total_price'],
                    'addons'       => null,
                    'created_at'   => $s['created_at'],
                    'updated_at'   => $now,
                ]);
            }

            // Payment
            $gatewayTxnId = null;
            $gatewayRefundId = null;
            if ($s['payment_method'] === 'stripe' && in_array($s['payment_status'], ['paid', 'refunded'])) {
                $gatewayTxnId = 'pi_3' . strtoupper(substr(md5(uniqid()), 0, 20));
                if ($s['payment_status'] === 'refunded') {
                    $gatewayRefundId = 're_' . strtoupper(substr(md5(uniqid()), 0, 20));
                }
            }

            DB::table('payments')->insert([
                'order_id'               => $orderId,
                'user_id'                => $s['user_id'],
                'gateway'                => $s['payment_method'],
                'gateway_transaction_id' => $gatewayTxnId,
                'gateway_refund_id'      => $gatewayRefundId,
                'amount'                 => $total,
                'currency'               => 'PKR',
                'status'                 => match($s['payment_status']) {
                    'paid'     => 'completed',
                    'refunded' => 'refunded',
                    'failed'   => 'failed',
                    default    => 'pending',
                },
                'gateway_response'       => null,
                'paid_at'                => in_array($s['payment_status'], ['paid', 'refunded'])
                    ? $s['created_at']->copy()->addMinutes(5) : null,
                'created_at'             => $s['created_at'],
                'updated_at'             => $now,
            ]);

            // Status history
            DB::table('order_status_history')->insert([
                'order_id'   => $orderId,
                'status'     => 'pending',
                'notes'      => 'Order placed.',
                'changed_by' => $s['user_id'],
                'created_at' => $s['created_at'],
                'updated_at' => $s['created_at'],
            ]);

            if (in_array($s['status'], ['confirmed', 'processing', 'shipped', 'delivered'])) {
                DB::table('order_status_history')->insert([
                    'order_id'   => $orderId,
                    'status'     => 'confirmed',
                    'notes'      => 'Order accepted by vendor.',
                    'changed_by' => $adminId,
                    'created_at' => $s['created_at']->copy()->addMinutes(30),
                    'updated_at' => $s['created_at']->copy()->addMinutes(30),
                ]);
            }

            if (in_array($s['status'], ['processing', 'shipped', 'delivered'])) {
                DB::table('order_status_history')->insert([
                    'order_id'   => $orderId,
                    'status'     => 'processing',
                    'notes'      => 'Vendor is preparing your order.',
                    'changed_by' => $adminId,
                    'created_at' => $s['created_at']->copy()->addHours(2),
                    'updated_at' => $s['created_at']->copy()->addHours(2),
                ]);
            }

            if ($s['status'] === 'delivered') {
                DB::table('order_status_history')->insert([
                    'order_id'   => $orderId,
                    'status'     => 'delivered',
                    'notes'      => 'Order delivered successfully.',
                    'changed_by' => $adminId,
                    'created_at' => $s['created_at']->copy()->addDays(3),
                    'updated_at' => $s['created_at']->copy()->addDays(3),
                ]);
            }

            if (in_array($s['status'], ['cancelled', 'rejected'])) {
                DB::table('order_status_history')->insert([
                    'order_id'   => $orderId,
                    'status'     => $s['status'],
                    'notes'      => $s['status'] === 'cancelled' ? 'Order cancelled by customer.' : 'Order rejected by vendor — out of stock.',
                    'changed_by' => $s['status'] === 'cancelled' ? $s['user_id'] : $adminId,
                    'created_at' => $s['created_at']->copy()->addHours(1),
                    'updated_at' => $s['created_at']->copy()->addHours(1),
                ]);
            }
        }
    }

    private function buildScenarios(array $customers, array $vendors, $products, array $coupons, Carbon $now): array
    {
        $scenarios   = [];
        $statusPlan  = array_merge(
            array_fill(0, 30, 'delivered'),
            array_fill(0,  8, 'cancelled'),
            array_fill(0,  5, 'pending'),
            array_fill(0,  3, 'confirmed'),
            array_fill(0,  2, 'processing'),
            array_fill(0,  4, 'rejected'),
            array_fill(0,  3, 'refunded')   // mapped as delivered + payment refunded
        );

        $paymentMethods = ['stripe', 'cod', 'wallet'];
        $addresses = [
            'House 12, Street 5, Gulberg III, Lahore',
            'Flat 3B, Block 9, Clifton, Karachi',
            'Plot 45, Sector F-8, Islamabad',
            'Gali 7, Mohalla Hussain Pura, Faisalabad',
            'Village Chak 60 JB, Sahiwal District',
            'Near Qila Kohna Qasim, Multan',
            'Hayatabad Phase 4, Peshawar',
            'Cantt Road, Rawalpindi',
            'Satellite Town, Quetta',
            'Johar Town, Lahore',
        ];

        shuffle($customers);

        foreach ($statusPlan as $i => $status) {
            $vendor  = $vendors[$i % count($vendors)];
            $userId  = $customers[$i % count($customers)];
            $vendorProds = isset($products[$vendor->id]) ? $products[$vendor->id]->values()->toArray() : [];

            if (empty($vendorProds)) {
                // fallback — pick any products
                $vendorProds = DB::table('products')->limit(5)->get()->toArray();
            }

            // Pick 1-3 products
            shuffle($vendorProds);
            $picked = array_slice($vendorProds, 0, rand(1, min(3, count($vendorProds))));

            $items = [];
            foreach ($picked as $prod) {
                $unitPrice = (float) ($prod->discount_price ?? $prod->price);
                $items[] = [
                    'product_id'   => $prod->id,
                    'product_name' => $prod->name,
                    'quantity'     => rand(1, 4),
                    'unit_price'   => $unitPrice,
                ];
            }

            // High-value edge case: every 10th order
            if ($i % 10 === 9) {
                $firstItem         = &$items[0];
                $firstItem['quantity'] = rand(10, 25);
            }

            $payMethod    = $paymentMethods[$i % count($paymentMethods)];
            $payStatus    = match($status) {
                'delivered'                => 'paid',
                'cancelled', 'rejected'    => ($payMethod === 'stripe' && rand(0, 1)) ? 'failed' : 'pending',
                'pending', 'confirmed', 'processing' => 'pending',
                'refunded'                 => 'refunded',
                default                    => 'pending',
            };

            // 'refunded' is a payment state, not an order status…
            $orderStatus = $status === 'refunded' ? 'delivered' : $status;

            $scenarios[] = [
                'user_id'        => $userId,
                'vendor_id'      => $vendor->id,
                'coupon_id'      => ($i % 7 === 0 && ! empty($coupons)) ? $coupons[$i % count($coupons)] : null,
                'status'         => $orderStatus,
                'payment_method' => $payMethod,
                'payment_status' => $payStatus,
                'address'        => $addresses[$i % count($addresses)],
                'notes'          => $i % 5 === 0 ? 'Please deliver before noon.' : null,
                'items'          => $items,
                'created_at'     => $now->copy()->subDays(rand(1, 180)),
            ];
        }

        return $scenarios;
    }
}

