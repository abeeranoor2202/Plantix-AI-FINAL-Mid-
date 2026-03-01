<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $zones = [
            ['zone_name' => 'Punjab',                      'description' => 'Largest province of Pakistan', 'status' => 'active'],
            ['zone_name' => 'Sindh',                       'description' => 'Province in southern Pakistan', 'status' => 'active'],
            ['zone_name' => 'Khyber Pakhtunkhwa',          'description' => 'Northwestern province of Pakistan', 'status' => 'active'],
            ['zone_name' => 'Balochistan',                 'description' => 'Largest province by area', 'status' => 'active'],
            ['zone_name' => 'Islamabad Capital Territory', 'description' => 'Federal capital territory', 'status' => 'active'],
        ];

        foreach ($zones as $zone) {
            DB::table('zones')->insert(array_merge($zone, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
