<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Each seeder is called in dependency order:
     *  1. Users            – required by all other seeders
     *  2. Zones            – required by vendors
     *  3. Categories       – required by vendors & products
     *  4. Roles/Perms      – assigns role_id to admin users
     *  5. Vendors          – required by products, coupons, orders
     *  6. Products         – required by orders
     *  7. Taxes & Coupons  – coupons reference vendors
     *  8. Orders           – references users, vendors, products, coupons
     *  9. Wallet/Payouts   – references users, vendors, orders
     * 10. Misc             – gift cards, on-board slides, store filters, currencies, CMS
     *
     * @return void
     */
    public function run(): void
    {
        // Disable FK checks so truncation order doesn't matter
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate all seeded tables in reverse-dependency order (children first)
        $tables = [
            // New Plantix modules (children first)
            'forum_ai_suggestions',
            'ai_chat_messages',
            'ai_chat_sessions',
            'weather_alert_logs',
            'weather_logs',
            'user_locations',
            'disease_suggestions',
            'crop_disease_reports',
            'fertilizer_recommendations',
            'seasonal_data',
            'crop_plans',
            'crop_recommendations',
            'soil_tests',
            'farm_profiles',
            'forum_replies',
            'forum_threads',
            'forum_categories',
            'wishlists',
            'user_addresses',
            'refunds',
            'returns',
            'return_reasons',
            'appointments',
            'experts',
            'order_status_history',
            'cart_items',
            'carts',
            'product_images',
            'product_stocks',
            'brands',
            // Existing tables
            'payout_requests',
            'payouts',
            'wallet_transactions',
            'order_items',
            'orders',
            'coupons',
            'taxes',
            'product_attributes',
            'products',
            'vendors',
            'role_permissions',
            'permissions',
            'role',
            'categories',
            'zone_points',
            'zones',
            'vendor_store_filters',
            'store_filters',
            'gift_cards',
            'on_board_slides',
            'cms_pages',
            'currencies',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call([
            UsersSeeder::class,
            ZonesSeeder::class,
            CategoriesSeeder::class,
            RolesPermissionsSeeder::class,
            VendorsSeeder::class,
            ProductsSeeder::class,
            TaxesAndCouponsSeeder::class,
            OrdersSeeder::class,
            WalletAndPayoutsSeeder::class,
            MiscSeeder::class,
            // ── New Plantix modules ──────────────────────
            BrandSeeder::class,
            ForumCategorySeeder::class,
            ReturnReasonSeeder::class,
            ExpertSeeder::class,
            SeasonalDataSeeder::class,
        ]);
    }
}
