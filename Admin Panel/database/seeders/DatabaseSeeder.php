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
            'ai_chat_messages','ai_chat_sessions','weather_alert_logs','weather_logs',
            'user_locations','fertilizer_recommendations','disease_suggestions',
            'crop_disease_reports','crop_plans','crop_recommendations','soil_tests',
            'farm_profiles','seasonal_data','forum_logs','forum_flags','forum_replies',
            'forum_threads','forum_categories','expert_logs','expert_unavailable_dates',
            'expert_availability','expert_applications','expert_specializations',
            'expert_profiles','expert_notification_logs','appointment_status_history',
            'appointment_status_histories','appointment_reschedules','appointment_logs',
            'appointment_slots','appointments','auth_logs','role_logs','system_logs',
            'files','favourite_products','favourite_vendors','reviews','refunds',
            'returns','inventory_logs','coupon_user_usage','payments',
            'order_status_history','cart_items','carts','order_items','orders',
            'product_images','product_stocks','product_attributes','products','brands',
            'coupons','taxes','payout_requests','payouts','wallet_transactions',
            'vendors','experts','role_permissions','permissions','roles','categories',
            'user_addresses','zone_points','zone_areas','zones','vendor_store_filters',
            'store_filters','return_reasons','gift_cards','on_board_slides',
            'cms_pages','currencies','users',
        ];

        foreach ($tables as $t) {
            if (DB::getSchemaBuilder()->hasTable($t)) {
                DB::table($t)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call([
            AdminRbacSeeder::class,
            UsersSeeder::class,
            ZonesSeeder::class,
            CategoriesSeeder::class,
            BrandSeeder::class,
            VendorsSeeder::class,
            ProductsSeeder::class,
            ProductStockSeeder::class,
            TaxesAndCouponsSeeder::class,
            OrdersSeeder::class,
            WalletAndPayoutsSeeder::class,
            MiscSeeder::class,
            ReturnReasonSeeder::class,
            ExpertsSeeder::class,
            AppointmentSeeder::class,
            ForumCategorySeeder::class,
            ForumSeeder::class,
            ReviewSeeder::class,
            UserLocationSeeder::class,
            FarmProfileSeeder::class,
            CropActivitySeeder::class,
            DiseaseReportSeeder::class,
            FertilizerRecommendationSeeder::class,
            SeasonalDataSeeder::class,
            AdminSuperUserSeeder::class,
        ]);
    }
}
