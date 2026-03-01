<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FertilizerRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $now   = Carbon::now();
        $tests = DB::table('soil_tests')->limit(20)->get();

        $plans = [
            'wheat' => [
                'vegetative' => [
                    ['name' => 'Urea',             'type' => 'nitrogen',   'dose_kg_per_acre' => 50, 'timing' => '2 weeks after sowing',      'notes' => 'Top-dress when soil is moist.'],
                    ['name' => 'DAP',               'type' => 'phosphorus', 'dose_kg_per_acre' => 25, 'timing' => 'Basal at sowing',           'notes' => 'Incorporate into seed row.'],
                    ['name' => 'Micronutrient Mix', 'type' => 'micro',      'dose_kg_per_acre' =>  2, 'timing' => '30 days after sowing',      'notes' => 'Foliar spray to correct zinc.'],
                ],
            ],
            'tomato' => [
                'flowering' => [
                    ['name' => 'NPK 15-15-15',       'type' => 'compound',   'dose_kg_per_acre' => 20, 'timing' => 'At transplanting',          'notes' => 'Mix into planting hole soil.'],
                    ['name' => 'Calcium Nitrate',     'type' => 'calcium',    'dose_kg_per_acre' => 15, 'timing' => '3 weeks after transplant',  'notes' => 'Prevents blossom-end rot.'],
                    ['name' => 'Potassium Sulphate',  'type' => 'potassium',  'dose_kg_per_acre' => 10, 'timing' => 'At flower initiation',     'notes' => 'Improves fruit quality.'],
                ],
            ],
            'rice' => [
                'seedling' => [
                    ['name' => 'DAP',  'type' => 'phosphorus', 'dose_kg_per_acre' => 30, 'timing' => 'Basal',                     'notes' => 'Broadcast before transplanting.'],
                    ['name' => 'Urea', 'type' => 'nitrogen',   'dose_kg_per_acre' => 25, 'timing' => '10–14 DAS',                 'notes' => 'First top-dress at tillering initiation.'],
                    ['name' => 'Urea', 'type' => 'nitrogen',   'dose_kg_per_acre' => 25, 'timing' => '35–40 DAS',                 'notes' => 'Second top-dress at panicle initiation.'],
                ],
            ],
            'cotton' => [
                'vegetative' => [
                    ['name' => 'Urea',             'type' => 'nitrogen',   'dose_kg_per_acre' => 40, 'timing' => '3 weeks after emergence', 'notes' => 'Avoid contact with stem.'],
                    ['name' => 'Potassium Sulphate','type' => 'potassium', 'dose_kg_per_acre' => 20, 'timing' => '40 DAS',                 'notes' => 'Side dress cotton rows.'],
                    ['name' => 'Boron Supplement', 'type' => 'micro',      'dose_kg_per_acre' =>  1, 'timing' => '50 DAS',                 'notes' => 'Foliar spray to improve boll retention.'],
                ],
            ],
        ];

        $cropTypes    = array_keys($plans);
        $stageOptions = ['seedling', 'vegetative', 'flowering', 'fruiting'];

        foreach ($tests as $idx => $test) {
            $crop  = $cropTypes[$idx % count($cropTypes)];
            $stage = $stageOptions[$idx % count($stageOptions)];

            // Pick plan if exact crop+stage exists, otherwise fall back to first stage
            $cropPlans  = $plans[$crop];
            $planStages = array_keys($cropPlans);
            $useStage   = in_array($stage, $planStages) ? $stage : $planStages[0];
            $plan       = $cropPlans[$useStage];

            $estimatedCost = collect($plan)->sum(fn ($p) => $p['dose_kg_per_acre'] * 100);

            DB::table('fertilizer_recommendations')->insert([
                'user_id'                   => $test->user_id,
                'soil_test_id'              => $test->id,
                'crop_type'                 => $crop,
                'growth_stage'              => $stage,
                'nitrogen'                  => $test->nitrogen,
                'phosphorus'                => $test->phosphorus,
                'potassium'                 => $test->potassium,
                'ph_level'                  => $test->ph_level,
                'temperature'               => $test->temperature,
                'humidity'                  => $test->humidity,
                'fertilizer_plan'           => json_encode($plan),
                'application_instructions'  => 'Apply fertiliser when soil moisture is adequate. Avoid application before heavy rain. Re-test soil after one full crop cycle.',
                'estimated_cost_pkr'        => $estimatedCost,
                'model_version'             => 'rule-based-v1',
                'created_at'                => $now->copy()->subDays(rand(5, 80)),
                'updated_at'                => $now,
            ]);
        }
    }
}
