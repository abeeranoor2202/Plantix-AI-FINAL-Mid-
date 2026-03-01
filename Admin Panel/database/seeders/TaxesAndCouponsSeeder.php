<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxesAndCouponsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Taxes ─────────────────────────────────────────────────────────────
        $taxes = [
            ['name' => 'General Sales Tax (GST)',      'rate' => 17.00, 'type' => 'exclusive', 'is_active' => 1],
            ['name' => 'Agriculture Input Exemption',  'rate' =>  0.00, 'type' => 'exclusive', 'is_active' => 1],
            ['name' => 'Federal Excise Duty (FED)',    'rate' =>  5.00, 'type' => 'inclusive', 'is_active' => 1],
        ];
        foreach ($taxes as $t) {
            DB::table('taxes')->insert(array_merge($t, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── Coupons ───────────────────────────────────────────────────────────
        $vendors = DB::table('vendors')->pluck('id')->toArray();
        $v1 = $vendors[0] ?? null;
        $v2 = $vendors[1] ?? null;
        $v6 = $vendors[5] ?? null;

        $coupons = [
            // Sitewide
            ['vendor_id' => null, 'code' => 'WELCOME10',  'type' => 'percentage', 'value' => 10, 'min_order' =>  500, 'max_discount' =>  500, 'usage_limit' => 200, 'is_active' => 1, 'starts_at' => $now->copy()->subDays(30), 'expires_at' => $now->copy()->addDays(180)],
            ['vendor_id' => null, 'code' => 'KISAN20',    'type' => 'percentage', 'value' => 20, 'min_order' => 2000, 'max_discount' => 1000, 'usage_limit' => 100, 'is_active' => 1, 'starts_at' => $now->copy()->subDays(10), 'expires_at' => $now->copy()->addDays(60)],
            ['vendor_id' => null, 'code' => 'FLAT500',    'type' => 'fixed',      'value' => 500,'min_order' => 5000, 'max_discount' => null,  'usage_limit' =>  50, 'is_active' => 1, 'starts_at' => $now->copy()->subDays(5),  'expires_at' => $now->copy()->addDays(30)],
            ['vendor_id' => null, 'code' => 'HARVEST15',  'type' => 'percentage', 'value' => 15, 'min_order' => 1000, 'max_discount' =>  800, 'usage_limit' => 300, 'is_active' => 1, 'starts_at' => $now->copy()->subMonths(2), 'expires_at' => $now->copy()->subDays(1)],  // expired
            ['vendor_id' => null, 'code' => 'SEEDSALE',   'type' => 'percentage', 'value' => 25, 'min_order' => 1500, 'max_discount' => 1200, 'usage_limit' => null,'is_active' => 0, 'starts_at' => $now->copy()->addDays(10), 'expires_at' => $now->copy()->addDays(40)],  // inactive
            // Vendor-specific
            ['vendor_id' => $v1,'code' => 'GREENH10',     'type' => 'percentage', 'value' => 10, 'min_order' => 800,  'max_discount' =>  400, 'usage_limit' =>  80, 'is_active' => 1, 'starts_at' => $now->copy()->subDays(15), 'expires_at' => $now->copy()->addDays(45)],
            ['vendor_id' => $v2,'code' => 'FERT200',      'type' => 'fixed',      'value' => 200,'min_order' => 3000, 'max_discount' => null,  'usage_limit' =>  60, 'is_active' => 1, 'starts_at' => $now->copy()->subDays(20), 'expires_at' => $now->copy()->addDays(40)],
            ['vendor_id' => $v6,'code' => 'ORGANIC15',    'type' => 'percentage', 'value' => 15, 'min_order' => 1200, 'max_discount' =>  600, 'usage_limit' => null,'is_active' => 1, 'starts_at' => $now->copy()->subDays(7),  'expires_at' => $now->copy()->addDays(90)],
        ];

        foreach ($coupons as $c) {
            DB::table('coupons')->insert(array_merge($c, [
                'used_count' => rand(0, min(10, $c['usage_limit'] ?? 10)),
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
