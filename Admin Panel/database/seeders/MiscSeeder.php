<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MiscSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Currency ─────────────────────────────────────────────
        DB::table('currencies')->insert([
            [
                'code'          => 'PKR',
                'name'          => 'Pakistani Rupee',
                'symbol'        => '₨',
                'is_default'    => true,
                'is_active'     => true,
                'exchange_rate' => 1.000000,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'USD',
                'name'          => 'US Dollar',
                'symbol'        => '$',
                'is_default'    => false,
                'is_active'     => true,
                'exchange_rate' => 0.003584,  // 1 PKR ≈ 0.003584 USD
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'code'          => 'AED',
                'name'          => 'UAE Dirham',
                'symbol'        => 'د.إ',
                'is_default'    => false,
                'is_active'     => true,
                'exchange_rate' => 0.013163,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);

        // ── CMS Pages ─────────────────────────────────────────────
        DB::table('cms_pages')->insert([
            [
                'slug'       => 'privacy-policy',
                'title'      => 'Privacy Policy',
                'content'    => '<h2>Privacy Policy – Plantix AI</h2><p>We collect minimum data required to provide agri-input ordering services to Pakistani farmers. Your data is never sold to third parties. Data stored on Firebase is secured with row-level security rules. For queries contact: privacy@plantixai.com.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'terms-and-conditions',
                'title'      => 'Terms & Conditions',
                'content'    => '<h2>Terms & Conditions – Plantix AI</h2><p>By using Plantix AI you agree to purchase agri-inputs from registered vendors only. All prices are inclusive of applicable GST. Delivery SLAs are subject to zone availability. Disputes must be raised within 48 hours of delivery.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'return-policy',
                'title'      => 'Return & Refund Policy',
                'content'    => '<h2>Return & Refund Policy</h2><p>Seeds and fertilizers can be returned within 7 days if packaging is intact. Pesticides and insecticides are non-returnable once opened due to safety regulations. Refunds are processed within 3–5 working days via the original payment method.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'       => 'about-us',
                'title'      => 'About Us',
                'content'    => '<h2>About Plantix AI</h2><p>Plantix AI is Pakistan\'s first AI-powered agri-input marketplace. We connect farmers in Punjab, Sindh, KPK and Balochistan with certified vendors of seeds, fertilizers, pesticides and insecticides. Our AI crop advisor helps diagnose plant diseases in real time.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // ── On-Board Slides ──────────────────────────────────────
        DB::table('on_board_slides')->insert([
            [
                'title'       => 'Welcome to Plantix AI',
                'description' => 'Pakistan\'s first AI-powered agri-input marketplace. Seeds, fertilizers, pesticides – all in one app.',
                'image'       => null,
                'sort_order'  => 1,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'title'       => 'Order Certified Inputs',
                'description' => 'Buy PARC-certified seeds, FCCI-approved fertilizers and DRAP-registered pesticides from trusted vendors.',
                'image'       => null,
                'sort_order'  => 2,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'title'       => 'AI Crop Disease Diagnosis',
                'description' => 'Photograph your crop and let our AI identify diseases and recommend the right pesticide or treatment.',
                'image'       => null,
                'sort_order'  => 3,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'title'       => 'Fast Delivery to Your Farm',
                'description' => 'Get agri-inputs delivered to your farm gate across all major agricultural districts of Pakistan.',
                'image'       => null,
                'sort_order'  => 4,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        // ── Store Filters ────────────────────────────────────────
        $filters = [
            ['name' => 'Certified Organic',     'sort_order' => 1],
            ['name' => 'PARC Recommended',       'sort_order' => 2],
            ['name' => 'DRAP Registered',        'sort_order' => 3],
            ['name' => 'Fast Delivery',          'sort_order' => 4],
            ['name' => 'Wholesale Available',    'sort_order' => 5],
            ['name' => 'New Arrivals',           'sort_order' => 6],
            ['name' => 'Top Rated',              'sort_order' => 7],
            ['name' => 'On Sale',                'sort_order' => 8],
        ];

        foreach ($filters as $f) {
            $filterId = DB::table('store_filters')->insertGetId([
                'name'       => $f['name'],
                'icon'       => null,
                'is_active'  => true,
                'sort_order' => $f['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Assign filters to vendors
        $vendors  = DB::table('vendors')->pluck('id')->toArray();
        $allFilters = DB::table('store_filters')->pluck('id')->toArray();

        foreach ($vendors as $vid) {
            // Each vendor gets 2–4 random filters
            $assigned = collect($allFilters)->shuffle()->take(rand(2, 4))->toArray();
            foreach ($assigned as $fid) {
                DB::table('vendor_store_filters')->insertOrIgnore([
                    'vendor_id'       => $vid,
                    'store_filter_id' => $fid,
                ]);
            }
        }

        // ── Gift Cards ───────────────────────────────────────────
        $customerIds = DB::table('users')->where('role', 'user')->pluck('id')->toArray();

        $giftCards = [
            ['code' => 'GIFT-PKR-1000-A1B2', 'amount' => 1000.00],
            ['code' => 'GIFT-PKR-2000-C3D4', 'amount' => 2000.00],
            ['code' => 'GIFT-PKR-500-E5F6',  'amount' => 500.00],
            ['code' => 'GIFT-PKR-5000-G7H8', 'amount' => 5000.00],
            ['code' => 'GIFT-PKR-1500-I9J0', 'amount' => 1500.00],
        ];

        foreach ($giftCards as $g) {
            $buyer      = $customerIds[array_rand($customerIds)];
            $redeemed   = rand(0, 1);
            $redeemer   = $redeemed ? $customerIds[array_rand($customerIds)] : null;
            $remaining  = $redeemed ? 0.00 : $g['amount'];

            DB::table('gift_cards')->insert([
                'code'             => $g['code'],
                'amount'           => $g['amount'],
                'remaining_amount' => $remaining,
                'purchased_by'     => $buyer,
                'redeemed_by'      => $redeemer,
                'redeemed_at'      => $redeemed ? $now->copy()->subDays(rand(1, 10)) : null,
                'expires_at'       => $now->copy()->addDays(90),
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $this->command->info('MiscSeeder: currencies, CMS pages, on-board slides, store filters, and gift cards inserted.');
    }
}
