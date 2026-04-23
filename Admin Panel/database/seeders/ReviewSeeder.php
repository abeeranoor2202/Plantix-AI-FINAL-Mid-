<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ReviewSeeder — one review per delivered order.
 *
 * Fixes applied:
 *  - Added 'status' column (added by migration 2026_03_01_999999 with default 'approved').
 *    Without this, the DB default handles it, but explicit insert avoids any edge cases.
 *  - Unique constraint is now (user_id, order_id, product_id) per migration
 *    2026_03_01_999999. The seeder already groups by order+product so this is respected.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $comments = [
            5 => [
                'Excellent quality! Product exactly as described. Fast delivery.',
                'Very satisfied with this purchase. Will order again.',
                'Top-notch product and very professional vendor.',
                'Great value for money. Highly recommended to all farmers.',
                'Delivered on time and packaging was perfect. 5 stars!',
            ],
            4 => [
                'Good product, minor delay in delivery but worth it.',
                'Product quality is decent. A bit expensive but effective.',
                'Mostly satisfied. Packaging could be better.',
                'Works as described. Good vendor communication.',
                'Solid product. Delivery was a day late but acceptable.',
            ],
            3 => [
                'Average quality. Does the job but nothing special.',
                'Okay product. Expected a bit more for the price.',
                'Neutral experience. Delivery was fine, product is average.',
                'It works but I have seen better quality elsewhere.',
                'Satisfactory but not outstanding.',
            ],
            2 => [
                'Disappointed with the product quality. Expected better.',
                'Packaging was damaged on arrival. Product still usable.',
                'Not worth the price. Would not recommend.',
                'Delivery was very late and product did not match images.',
            ],
            1 => [
                'Very poor quality. Not as described at all.',
                'Complete waste of money. Will be returning this.',
                'Terrible experience. Product was damaged and vendor unresponsive.',
            ],
        ];

        // Only review delivered orders — unique constraint: (user_id, order_id, product_id)
        $deliveredOrders = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->where('o.status', 'delivered')
            ->select('o.id as order_id', 'o.user_id', 'o.vendor_id', 'oi.product_id', 'o.created_at')
            ->groupBy('o.id', 'o.user_id', 'o.vendor_id', 'oi.product_id', 'o.created_at')
            ->limit(55)
            ->get();

        $reviewed = []; // track user_id+order_id+product_id to avoid duplicates

        foreach ($deliveredOrders as $o) {
            $key = $o->user_id . '_' . $o->order_id . '_' . $o->product_id;
            if (isset($reviewed[$key])) {
                continue;
            }
            $reviewed[$key] = true;

            $rating = $this->weightedRating();
            $pool   = $comments[$rating];

            DB::table('reviews')->insert([
                'user_id'    => $o->user_id,
                'vendor_id'  => $o->vendor_id,
                'product_id' => $o->product_id,
                'order_id'   => $o->order_id,
                'rating'     => $rating,
                'comment'    => $pool[array_rand($pool)],
                'is_active'  => 1,
                'status'     => 'approved',  // column added by 2026_03_01_999999
                'created_at' => Carbon::parse($o->created_at)->addDays(rand(3, 10)),
                'updated_at' => $now,
            ]);
        }
    }

    /** Weighted: 5★ 40%, 4★ 30%, 3★ 15%, 2★ 10%, 1★ 5% */
    private function weightedRating(): int
    {
        $r = rand(1, 100);
        if ($r <= 40) return 5;
        if ($r <= 70) return 4;
        if ($r <= 85) return 3;
        if ($r <= 95) return 2;
        return 1;
    }
}
