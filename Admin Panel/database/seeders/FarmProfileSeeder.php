<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FarmProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now       = Carbon::now();
        $customers = DB::table('users')->where('role', 'user')->limit(30)->pluck('id')->toArray();

        $soilTypes    = ['loamy', 'clay', 'sandy', 'silt', 'peat'];
        $waterSources = ['rain', 'irrigation', 'both'];
        $climateZones = ['subtropical_humid', 'subtropical_arid', 'temperate', 'semi_arid', 'arid'];
        $prevCrops    = [
            ['wheat', 'rice'],
            ['cotton', 'wheat'],
            ['maize', 'sugarcane'],
            ['tomato', 'onion', 'spinach'],
            ['sunflower', 'canola'],
        ];

        foreach ($customers as $idx => $userId) {
            $farmId = DB::table('farm_profiles')->insertGetId([
                'user_id'          => $userId,
                'farm_name'        => 'Farm ' . ($idx + 1),
                'location'         => ['Lahore', 'Faisalabad', 'Multan', 'Gujranwala', 'Sahiwal'][$idx % 5] . ' District',
                'farm_size_acres'  => round(rand(10, 200) * 0.5, 1),
                'soil_type'        => $soilTypes[$idx % count($soilTypes)],
                'water_source'     => $waterSources[$idx % count($waterSources)],
                'climate_zone'     => $climateZones[$idx % count($climateZones)],
                'previous_crops'   => json_encode($prevCrops[$idx % count($prevCrops)]),
                'notes'            => null,
                'created_at'       => $now->copy()->subDays(rand(30, 200)),
                'updated_at'       => $now,
            ]);

            // Insert soil test for each farm
            DB::table('soil_tests')->insert([
                'user_id'         => $userId,
                'farm_profile_id' => $farmId,
                'nitrogen'        => round(rand(100, 350) * 0.01, 2),
                'phosphorus'      => round(rand(50, 200) * 0.01, 2),
                'potassium'       => round(rand(80, 300) * 0.01, 2),
                'ph_level'        => round(rand(55, 85) * 0.1, 1),
                'organic_matter'  => round(rand(5, 40) * 0.1, 1),
                'humidity'        => round(rand(40, 80) * 0.1, 1),
                'rainfall_mm'     => round(rand(200, 1000) * 1.0, 0),
                'temperature'     => round(rand(15, 40) * 1.0, 1),
                'lab_report'      => null,
                'tested_at'       => $now->copy()->subDays(rand(10, 180)),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }
}
