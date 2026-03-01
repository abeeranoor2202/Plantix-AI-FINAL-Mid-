<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MiscSeeder — seeds: currencies, on_board_slides, store_filters, cms_pages, gift_cards
 */
class MiscSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Currencies ───────────────────────────────────────────────────────
        $currencies = [
            ['code' => 'PKR', 'name' => 'Pakistani Rupee',    'symbol' => '₨',  'is_default' => 1, 'is_active' => 1, 'exchange_rate' => 1.000000],
            ['code' => 'USD', 'name' => 'US Dollar',          'symbol' => '$',  'is_default' => 0, 'is_active' => 1, 'exchange_rate' => 0.003556],
            ['code' => 'GBP', 'name' => 'British Pound',      'symbol' => '£',  'is_default' => 0, 'is_active' => 1, 'exchange_rate' => 0.002801],
            ['code' => 'AED', 'name' => 'UAE Dirham',         'symbol' => 'د.إ','is_default' => 0, 'is_active' => 1, 'exchange_rate' => 0.013054],
            ['code' => 'SAR', 'name' => 'Saudi Riyal',        'symbol' => 'ر.س','is_default' => 0, 'is_active' => 1, 'exchange_rate' => 0.013332],
        ];
        foreach ($currencies as $c) {
            DB::table('currencies')->insert(array_merge($c, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── On-Boarding Slides ────────────────────────────────────────────────
        $slides = [
            ['title' => 'Welcome to Plantix',           'description' => 'Your one-stop agriculture marketplace. Buy seeds, tools, fertilizers and more.', 'sort_order' => 1],
            ['title' => 'Consult Expert Agronomists',   'description' => 'Book appointments with certified agricultural experts from across Pakistan.',     'sort_order' => 2],
            ['title' => 'AI Disease Detection',         'description' => 'Upload a photo of your crop to identify plant diseases instantly.',               'sort_order' => 3],
            ['title' => 'Smart Crop Planning',          'description' => 'Get personalised crop recommendations based on your soil and climate data.',       'sort_order' => 4],
            ['title' => 'Community Forum',              'description' => 'Join thousands of farmers sharing knowledge and solving problems together.',       'sort_order' => 5],
        ];
        foreach ($slides as $s) {
            DB::table('on_board_slides')->insert(array_merge($s, [
                'image' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ── Store Filters ─────────────────────────────────────────────────────
        $filters = [
            ['name' => 'Top Rated',        'sort_order' => 1],
            ['name' => 'Free Delivery',    'sort_order' => 2],
            ['name' => 'Organic',          'sort_order' => 3],
            ['name' => 'On Sale',          'sort_order' => 4],
            ['name' => 'New Arrivals',     'sort_order' => 5],
            ['name' => 'Fast Dispatch',    'sort_order' => 6],
        ];
        foreach ($filters as $f) {
            DB::table('store_filters')->insert(array_merge($f, [
                'icon' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ── CMS Pages ─────────────────────────────────────────────────────────
        $pages = [
            ['slug' => 'privacy-policy',    'title' => 'Privacy Policy',    'content' => '<h2>Privacy Policy</h2><p>Plantix is committed to protecting your personal data...</p>'],
            ['slug' => 'terms-of-service',  'title' => 'Terms of Service',  'content' => '<h2>Terms of Service</h2><p>By using Plantix you agree to these terms...</p>'],
            ['slug' => 'refund-policy',     'title' => 'Refund Policy',     'content' => '<h2>Refund Policy</h2><p>Orders may be returned within 7 days of delivery...</p>'],
            ['slug' => 'about-us',          'title' => 'About Us',          'content' => '<h2>About Plantix</h2><p>Plantix is Pakistan\'s first AI-powered agricultural platform...</p>'],
            ['slug' => 'contact-us',        'title' => 'Contact Us',        'content' => '<h2>Contact</h2><p>Email: support@plantix.com | Phone: +92 300 0000000</p>'],
        ];
        foreach ($pages as $p) {
            DB::table('cms_pages')->insert(array_merge($p, ['is_active' => 1, 'created_at' => $now, 'updated_at' => $now]));
        }
    }
}
