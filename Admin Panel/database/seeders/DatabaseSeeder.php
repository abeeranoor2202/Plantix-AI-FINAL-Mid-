<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            // AI / weather / agriculture modules
            'ai_chat_messages', 'ai_chat_sessions', 'weather_alert_logs', 'weather_logs',
            'user_locations', 'fertilizer_recommendations', 'disease_suggestions',
            'crop_disease_reports', 'crop_plans', 'crop_recommendations', 'soil_tests',
            'farm_profiles', 'seasonal_data',
            // Forum
            'forum_logs', 'forum_flags', 'forum_replies', 'forum_threads', 'forum_categories',
            // Expert ecosystem
            'expert_logs', 'expert_unavailable_dates', 'expert_availability',
            'expert_applications', 'expert_specializations', 'expert_profiles',
            'expert_notification_logs',
            // Appointments (note: appointment_status_history (singular) was DROPPED by migration
            // 2026_03_01_200001; only appointment_status_histories (plural) exists now)
            'appointment_status_histories', 'appointment_reschedules', 'appointment_logs',
            'appointment_slots', 'appointments',
            // Auth / logs / RBAC
            'auth_logs', 'role_logs', 'system_logs', 'files',
            // Reviews & favourites
            'favourite_products', 'favourite_vendors', 'reviews',
            // Returns / refunds / inventory
            'refunds', 'returns', 'return_reasons', 'inventory_logs',
            // Payments & coupons
            'coupon_user_usage', 'payments', 'vendor_earnings',
            // Orders
            'order_status_history', 'cart_items', 'carts', 'order_items', 'orders',
            // Products & stock
            'product_images', 'product_stocks', 'stock_movements', 'stocks',
            'product_attributes', 'products', 'brands',
            // Coupons / taxes / payouts
            'coupons', 'taxes', 'payout_requests', 'payouts', 'wallet_transactions',
            // Vendor applications
            'vendor_applications',
            // Vendors & experts
            'vendors', 'experts',
            // RBAC
            'role_permissions', 'permissions', 'roles',
            // Categories & zones
            'categories', 'user_addresses', 'zone_points', 'zone_areas', 'zones',
            // Misc
            'vendor_store_filters', 'store_filters', 'gift_cards', 'on_board_slides',
            'cms_pages', 'currencies',
            // Users last (most tables FK to it)
            'users',
        ];

        foreach ($tables as $t) {
            if (DB::getSchemaBuilder()->hasTable($t)) {
                DB::table($t)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call([
            AdminRbacSeeder::class,           // roles + permissions (no FK deps)
            UsersSeeder::class,               // customer / vendor / expert users
            AdminSuperUserSeeder::class,      // super-admin created HERE so it exists for FK refs below
            CategoriesSeeder::class,          // product categories
            BrandSeeder::class,               // product brands
            VendorsSeeder::class,             // vendor profiles (needs users + categories + admin for reviewed_by)
            ProductsSeeder::class,            // products (needs vendors + categories + brands)
            ProductStockSeeder::class,        // product_stocks + product_images
            TaxesAndCouponsSeeder::class,     // coupons (needs vendors)
            OrdersSeeder::class,              // orders + items + payments + history (needs admin)
            MiscSeeder::class,                // on_board_slides
            ReturnReasonSeeder::class,        // return_reasons
            ExpertsSeeder::class,             // experts + expert_profiles + specializations
            AppointmentSeeder::class,         // appointments (needs experts + users + admin)
            ExpertSlotSeeder::class,          // appointment_slots (needs experts + appointments)
            ForumCategorySeeder::class,       // forum_categories
            ForumSeeder::class,               // forum_threads + forum_replies
            ReviewSeeder::class,              // reviews (needs delivered orders)
            UserLocationSeeder::class,        // user_locations
            FarmProfileSeeder::class,         // farm_profiles
            CropActivitySeeder::class,        // crop plans / soil tests / crop activity
            DiseaseReportSeeder::class,       // crop_disease_reports
            SeasonalDataSeeder::class,
            ForumFlagSeeder::class,           // forum_flags (needs replies + users)
            ComprehensivePakistanAgriSeeder::class,
        ]);
    }
}
