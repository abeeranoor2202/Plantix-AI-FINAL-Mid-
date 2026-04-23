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

        $recommendedFertilizers = [
            'Urea',
            'DAP',
            'Fourteen-Thirty Five-Fourteen',
            'Twenty Eight-Twenty Eight',
            'Seventeen-Seventeen-Seventeen',
            'Twenty-Twenty',
            'Ten-Twenty Six-Twenty Six',
        ];

        $cropTypes    = ['wheat', 'rice', 'maize', 'cotton', 'potato', 'tomato'];
        $stageOptions = ['seedling', 'vegetative', 'flowering', 'fruiting'];

        foreach ($tests as $idx => $test) {
            $crop  = $cropTypes[$idx % count($cropTypes)];
            $stage = $stageOptions[$idx % count($stageOptions)];

            $fertilizer = $recommendedFertilizers[$idx % count($recommendedFertilizers)];
            $dose = max(8, min(55, round((130 - (float) $test->nitrogen) / 3.2, 1)));

            $plan = [[
                'name' => $fertilizer,
                'type' => 'ai-recommended',
                'dose_kg_per_acre' => $dose,
                'timing' => match ($stage) {
                    'seedling' => 'Apply light starter dose at seedling establishment',
                    'vegetative' => 'Split in two equal applications during vegetative growth',
                    'flowering' => 'Apply before flowering with irrigation support',
                    default => 'Apply maintenance dose only if deficiency symptoms appear',
                },
                'notes' => 'Generated for seeding realism using fertilizer model label set.',
            ]];

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
                'model_version'             => 'flask-fertilizer-v1',
                'created_at'                => $now->copy()->subDays(rand(5, 80)),
                'updated_at'                => $now,
            ]);
        }
    }
}
