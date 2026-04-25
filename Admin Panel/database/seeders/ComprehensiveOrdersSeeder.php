<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ComprehensiveOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing order-related data to start fresh if requested, 
        // but we'll just append more diverse data to ensure "all types" are covered.
        
        $now = Carbon::now();
        $customers = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $vendors = DB::table('vendors')->pluck('id')->toArray();
        $products = DB::table('products')->select('id', 'vendor_id', 'name', 'price', 'discount_price')->get()->groupBy('vendor_id');
        $coupons = DB::table('coupons')->where('is_active', 1)->pluck('id')->toArray();
        $returnReasons = DB::table('return_reasons')->pluck('id')->toArray();
        $adminId = DB::table('users')->where('role', 'admin')->value('id') ?? 1;

        if (empty($customers) || empty($vendors) || empty($products)) {
            return;
        }

        $orderNumberBase = 20000;
        
        // Define Scenarios for "All Types"
        $scenarios = [
            // 1. Standard Life Cycle
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cod', 'count' => 5],
            ['status' => 'confirmed', 'payment_status' => 'pending', 'payment_method' => 'cod', 'count' => 3],
            ['status' => 'processing', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'count' => 3],
            ['status' => 'shipped', 'payment_status' => 'paid', 'payment_method' => 'wallet', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'count' => 10],
            ['status' => 'cancelled', 'payment_status' => 'pending', 'payment_method' => 'cod', 'count' => 3],
            ['status' => 'rejected', 'payment_status' => 'failed', 'payment_method' => 'stripe', 'count' => 2],

            // 2. Disputed Orders (Active)
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'dispute' => 'pending', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'dispute' => 'vendor_responded', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'dispute' => 'escalated', 'count' => 1],

            // 3. Disputed Orders (Resolved)
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'dispute' => 'resolved', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'dispute' => 'rejected', 'count' => 1],

            // 4. Returned Orders (In Table)
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'cod', 'return' => 'pending', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'return' => 'approved', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'wallet', 'return' => 'completed', 'count' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'stripe', 'return' => 'rejected', 'count' => 1],
        ];

        foreach ($scenarios as $scenario) {
            for ($i = 0; $i < $scenario['count']; $i++) {
                $vendorId = $vendors[array_rand($vendors)];
                $userId = $customers[array_rand($customers)];
                $vendorProds = $products[$vendorId] ?? collect([]);
                
                if ($vendorProds->isEmpty()) continue;

                $picked = $vendorProds->random(min(rand(1, 3), $vendorProds->count()));
                $subtotal = 0;
                $itemsData = [];

                foreach ($picked as $prod) {
                    $qty = rand(1, 3);
                    $price = $prod->discount_price ?? $prod->price;
                    $itemTotal = $price * $qty;
                    $subtotal += $itemTotal;
                    $itemsData[] = [
                        'product_id' => $prod->id,
                        'product_name' => $prod->name,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $itemTotal,
                        'created_at' => $now->copy()->subDays(rand(1, 30)),
                        'updated_at' => $now,
                    ];
                }

                $tax = round($subtotal * 0.05, 2);
                $delivery = 150;
                $discount = (rand(1, 10) > 8 && !empty($coupons)) ? 100 : 0;
                $total = $subtotal + $tax + $delivery - $discount;

                $disputeStatus = $scenario['dispute'] ?? 'none';
                
                $orderId = DB::table('orders')->insertGetId([
                    'order_number' => 'ORD-' . ($orderNumberBase++),
                    'user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'status' => $scenario['status'],
                    'dispute_status' => $disputeStatus,
                    'disputed_at' => ($disputeStatus !== 'none') ? $now->copy()->subDays(rand(1, 5)) : null,
                    'dispute_reason' => ($disputeStatus !== 'none') ? 'Items were damaged during transit or quality is not as described.' : null,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $delivery,
                    'tax_amount' => $tax,
                    'discount_amount' => $discount,
                    'total' => $total,
                    'payment_method' => $scenario['payment_method'],
                    'payment_status' => $scenario['payment_status'],
                    'delivery_address' => 'Sample Address, Street ' . rand(1, 50) . ', City ' . rand(1, 5),
                    'created_at' => $now->copy()->subDays(rand(5, 60)),
                    'updated_at' => $now,
                ]);

                foreach ($itemsData as $item) {
                    $item['order_id'] = $orderId;
                    DB::table('order_items')->insert($item);
                }

                // If Return scenario
                if (isset($scenario['return']) && !empty($returnReasons)) {
                    DB::table('returns')->insert([
                        'order_id' => $orderId,
                        'user_id' => $userId,
                        'return_reason_id' => $returnReasons[array_rand($returnReasons)],
                        'description' => 'Product quality is not up to the mark. I want a refund or replacement.',
                        'status' => $scenario['return'],
                        'requested_at' => $now->copy()->subDays(rand(1, 3)),
                        'created_at' => $now->copy()->subDays(rand(1, 3)),
                        'updated_at' => $now,
                    ]);
                }

                // Add some status history
                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'notes' => 'Order placed successfully.',
                    'changed_by' => $userId,
                    'created_at' => $now->copy()->subDays(60),
                ]);

                if ($scenario['status'] !== 'pending') {
                    DB::table('order_status_history')->insert([
                        'order_id' => $orderId,
                        'status' => $scenario['status'],
                        'notes' => 'Status updated to ' . $scenario['status'],
                        'changed_by' => $adminId,
                        'created_at' => $now->copy()->subDays(rand(1, 59)),
                    ]);
                }
            }
        }
    }
}
