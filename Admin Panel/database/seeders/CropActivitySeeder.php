<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CropActivitySeeder extends Seeder
{
    public function run(): void
    {
        $now   = Carbon::now();
        $tests = DB::table('soil_tests')->limit(25)->get();
        $farms = DB::table('farm_profiles')->limit(20)->get()->keyBy('id');

        $cropSets = [
            ['crops' => [['name' => 'wheat', 'confidence' => 92, 'notes' => 'Highly suitable for your soil and climate.'], ['name' => 'canola', 'confidence' => 75, 'notes' => 'Good for winter season rotation.']]],
            ['crops' => [['name' => 'rice', 'confidence' => 88, 'notes' => 'Clay soil retains water well for paddy.'], ['name' => 'sugarcane', 'confidence' => 70, 'notes' => 'Suitable if irrigation is available.']]],
            ['crops' => [['name' => 'maize', 'confidence' => 85, 'notes' => 'Sandy loam is ideal for maize.'], ['name' => 'sunflower', 'confidence' => 78, 'notes' => 'Drought-tolerant and profitable.']]],
            ['crops' => [['name' => 'tomato', 'confidence' => 90, 'notes' => 'Excellent for your soil pH and fertility.'], ['name' => 'onion', 'confidence' => 82, 'notes' => 'Good drainage present.']]],
            ['crops' => [['name' => 'cotton', 'confidence' => 89, 'notes' => 'High potassium level suits cotton.'], ['name' => 'wheat', 'confidence' => 84, 'notes' => 'Great rotation crop.']]],
        ];

        foreach ($tests as $idx => $test) {
            $cropSet = $cropSets[$idx % count($cropSets)];

            // Crop recommendation
            $recId = DB::table('crop_recommendations')->insertGetId([
                'user_id'            => $test->user_id,
                'soil_test_id'       => $test->id,
                'nitrogen'           => $test->nitrogen,
                'phosphorus'         => $test->phosphorus,
                'potassium'          => $test->potassium,
                'ph_level'           => $test->ph_level,
                'humidity'           => $test->humidity,
                'rainfall_mm'        => $test->rainfall_mm,
                'temperature'        => $test->temperature,
                'recommended_crops'  => json_encode($cropSet['crops']),
                'explanation'        => 'Based on your soil nutrient profile and climate conditions, these crops are most suitable for the upcoming season.',
                'model_version'      => 'rule-based-v1',
                'status'             => 'completed',
                'created_at'         => $now->copy()->subDays(rand(5, 100)),
                'updated_at'         => $now,
            ]);

            // Crop plan
            if (isset($farms[$test->farm_profile_id])) {
                $farm    = $farms[$test->farm_profile_id];
                $primary = $cropSet['crops'][0]['name'];
                DB::table('crop_plans')->insert([
                    'user_id'               => $test->user_id,
                    'farm_profile_id'       => $test->farm_profile_id,
                    'season'                => ['Kharif', 'Rabi', 'Zaid'][$idx % 3],
                    'year'                  => 2025,
                    'primary_crop'          => $primary,
                    'crop_schedule'         => json_encode([
                        ['crop' => $primary, 'start_week' => 1, 'end_week' => 4, 'phase' => 'Land preparation & sowing', 'notes' => 'Bed preparation and seed treatment.'],
                        ['crop' => $primary, 'start_week' => 5, 'end_week' => 10, 'phase' => 'Vegetative growth', 'notes' => 'First irrigation and fertiliser top-dress.'],
                        ['crop' => $primary, 'start_week' => 11, 'end_week' => 16, 'phase' => 'Flowering & fruiting', 'notes' => 'Pest scouting and disease management.'],
                        ['crop' => $primary, 'start_week' => 17, 'end_week' => 20, 'phase' => 'Harvest', 'notes' => 'Harvest at optimum maturity.'],
                    ]),
                    'water_plan'            => json_encode([
                        ['week' => 2, 'irrigation_mm' => 60],
                        ['week' => 6, 'irrigation_mm' => 80],
                        ['week' => 10, 'irrigation_mm' => 80],
                        ['week' => 14, 'irrigation_mm' => 60],
                    ]),
                    'expected_yield_tons'   => round($farm->farm_size_acres * [2.5, 3.0, 2.0, 1.8][$idx % 4], 2),
                    'estimated_revenue'     => round($farm->farm_size_acres * [35000, 45000, 28000, 25000][$idx % 4], 0),
                    'soil_suitability_notes' => 'Soil is suitable. Minor pH correction recommended before sowing.',
                    'recommendations'       => 'Apply zinc sulfate 10 kg/acre as basal dose.',
                    'status'               => 'active',
                    'created_at'           => $now->copy()->subDays(rand(5, 80)),
                    'updated_at'           => $now,
                ]);
            }
        }
    }
}
