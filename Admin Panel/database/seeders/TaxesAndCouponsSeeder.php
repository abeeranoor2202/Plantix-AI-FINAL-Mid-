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

        // ── Taxes ────────────────────────────────────────────────
        DB::table('taxes')->insert([
            [
                'name'       => 'GST Pakistan (17%)',
                'rate'       => 17.00,
                'type'       => 'exclusive',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Sindh Sales Tax (13%)',
                'rate'       => 13.00,
                'type'       => 'exclusive',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Punjab Agri Input Tax (5%)',
                'rate'       => 5.00,
                'type'       => 'inclusive',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Advance Income Tax (0.5%)',
                'rate'       => 0.50,
                'type'       => 'exclusive',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // ── Coupons ──────────────────────────────────────────────
        $vendors = DB::table('vendors')->pluck('id')->toArray();

        $coupons = [
            [
                'code'        => 'KISAAN20',
                'type'        => 'percentage',
                'value'       => 20.00,
                'min_order'   => 2000.00,
                'max_disc'    => 1000.00,
                'usage_limit' => 200,
                'vendor_id'   => null,
                'starts'      => $now->copy()->subDays(5),
                'expires'     => $now->copy()->addDays(30),
                'desc'        => '20% off for all farmers on orders above Rs 2,000',
            ],
            [
                'code'        => 'SEEDPK10',
                'type'        => 'percentage',
                'value'       => 10.00,
                'min_order'   => 1000.00,
                'max_disc'    => 500.00,
                'usage_limit' => 150,
                'vendor_id'   => $vendors[0] ?? null,
                'starts'      => $now->copy()->subDays(2),
                'expires'     => $now->copy()->addDays(15),
                'desc'        => '10% off on seeds at Punjab Seeds Centre',
            ],
            [
                'code'        => 'UREA500',
                'type'        => 'fixed',
                'value'       => 500.00,
                'min_order'   => 5000.00,
                'max_disc'    => null,
                'usage_limit' => 100,
                'vendor_id'   => $vendors[1] ?? null,
                'starts'      => $now->copy()->subDay(),
                'expires'     => $now->copy()->addDays(20),
                'desc'        => 'Rs 500 flat discount on fertilizer orders above Rs 5,000',
            ],
            [
                'code'        => 'RABI2026',
                'type'        => 'percentage',
                'value'       => 15.00,
                'min_order'   => 3000.00,
                'max_disc'    => 1500.00,
                'usage_limit' => 300,
                'vendor_id'   => null,
                'starts'      => $now->copy()->subDays(10),
                'expires'     => $now->copy()->addDays(45),
                'desc'        => 'Rabi 2026 seasonal offer – 15% off on all products',
            ],
            [
                'code'        => 'COTTON25',
                'type'        => 'percentage',
                'value'       => 25.00,
                'min_order'   => 4000.00,
                'max_disc'    => 2000.00,
                'usage_limit' => 80,
                'vendor_id'   => $vendors[3] ?? null,
                'starts'      => $now->copy()->subDays(3),
                'expires'     => $now->copy()->addDays(25),
                'desc'        => '25% off insecticide products for cotton belt farmers',
            ],
            [
                'code'        => 'WELCOME200',
                'type'        => 'fixed',
                'value'       => 200.00,
                'min_order'   => 800.00,
                'max_disc'    => null,
                'usage_limit' => 500,
                'vendor_id'   => null,
                'starts'      => $now->copy()->subDays(30),
                'expires'     => $now->copy()->addDays(60),
                'desc'        => 'New user welcome coupon – Rs 200 off on first order',
            ],
            [
                'code'        => 'FUNGIPK12',
                'type'        => 'percentage',
                'value'       => 12.00,
                'min_order'   => 1500.00,
                'max_disc'    => 800.00,
                'usage_limit' => 60,
                'vendor_id'   => $vendors[9] ?? null,
                'starts'      => $now->copy()->subDays(1),
                'expires'     => $now->copy()->addDays(20),
                'desc'        => '12% off on all fungicide products',
            ],
            [
                'code'        => 'ORGANIC30',
                'type'        => 'percentage',
                'value'       => 30.00,
                'min_order'   => 2500.00,
                'max_disc'    => 2500.00,
                'usage_limit' => 50,
                'vendor_id'   => $vendors[7] ?? null,
                'starts'      => $now->copy(),
                'expires'     => $now->copy()->addDays(14),
                'desc'        => '30% off on bio-pesticides this fortnight',
            ],
        ];

        foreach ($coupons as $c) {
            DB::table('coupons')->insert([
                'vendor_id'    => $c['vendor_id'],
                'code'         => $c['code'],
                'type'         => $c['type'],
                'value'        => $c['value'],
                'min_order'    => $c['min_order'],
                'max_discount' => $c['max_disc'],
                'usage_limit'  => $c['usage_limit'],
                'used_count'   => rand(0, (int) ($c['usage_limit'] * 0.25)),
                'starts_at'    => $c['starts'],
                'expires_at'   => $c['expires'],
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        $this->command->info('TaxesAndCouponsSeeder: ' . DB::table('taxes')->count() . ' taxes, ' . DB::table('coupons')->count() . ' coupons inserted.');
    }
}
