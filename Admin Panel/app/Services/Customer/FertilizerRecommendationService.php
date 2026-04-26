<?php

namespace App\Services\Customer;

use App\Models\FertilizerRecommendation;
use App\Models\User;
use App\Services\FertilizerPredictionService;

/**
 * FertilizerRecommendationService
 *
 * AI-backed fertilizer recommendation service.
 * Uses Flask model inference and persists recommendation snapshots.
 */
class FertilizerRecommendationService
{
    public function __construct(private readonly FertilizerPredictionService $predictionApi) {}

    /**
     * Generate a fertilizer plan based on soil N, P, K values.
     *
     * @param  User     $user
     * @param  array    $input  {nitrogen, phosphorus, potassium}
     * @param  int|null $soilTestId
     * @return FertilizerRecommendation
     */
    public function recommend(User $user, array $input, ?int $soilTestId = null): FertilizerRecommendation
    {
        $soilN = (float)($input['nitrogen']   ?? 0);
        $soilP = (float)($input['phosphorus'] ?? 0);
        $soilK = (float)($input['potassium']  ?? 0);

        $apiResult = $this->predictionApi->predict([
            'nitrogen'    => $soilN,
            'phosphorus'  => $soilP,
            'potassium'   => $soilK,
        ]);

        $recommendedFertilizer = (string) ($apiResult['fertilizer'] ?? 'Urea');
        $confidenceScore = isset($apiResult['confidence']) ? (float) $apiResult['confidence'] : null;

        $plan         = $this->buildPlan($recommendedFertilizer, $soilN, $soilP, $soilK, $confidenceScore);
        $cost         = $this->estimateCost($plan);
        $instructions = $this->buildInstructions($recommendedFertilizer, $confidenceScore, $apiResult);

        return FertilizerRecommendation::create([
            'user_id'                   => $user->id,
            'soil_test_id'              => $soilTestId,
            'nitrogen'                  => $soilN,
            'phosphorus'                => $soilP,
            'potassium'                 => $soilK,
            'fertilizer_plan'           => $plan,
            'application_instructions'  => $instructions,
            'estimated_cost_pkr'        => $cost,
            'model_version'             => (string) ($apiResult['model_version'] ?? 'flask-fertilizer-v1'),
        ]);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function buildPlan(string $fertilizerName, string $crop, float $n, float $p, float $k, ?float $confidence): array
    {
        $baseDose = $this->estimatePrimaryDose($n, $p, $k);
        $confidencePercent = $confidence === null ? null : round($confidence * 100, 2);

        return [[
            'name'             => $fertilizerName,
            'type'             => 'AI Recommended',
            'dose_kg_per_acre' => $baseDose,
            'timing'           => 'Basal application at land preparation',
            'notes'            => 'Recommended by ML model for ' . $crop . ($confidencePercent !== null ? (' (confidence: ' . $confidencePercent . '%).') : '.'),
        ]];
    }

    private function estimateCost(array $plan): float
    {
        $fertPriceMap = [
            'Urea' => 4650,
            'DAP' => 12500,
            'Fourteen-Thirty Five-Fourteen' => 10200,
            'Twenty Eight-Twenty Eight' => 9800,
            'Seventeen-Seventeen-Seventeen' => 9200,
            'Twenty-Twenty' => 8800,
            'Ten-Twenty Six-Twenty Six' => 9300,
            'Ammonium Sulphate' => 5200,
            'Agricultural Lime' => 1300,
        ];

        $total = 0;
        foreach ($plan as $item) {
            $pricePerBag  = $fertPriceMap[$item['name']] ?? 4000;
            $dosePerAcre  = (float)($item['dose_kg_per_acre'] ?? 0);
            $bags         = ceil($dosePerAcre / 50);
            $total       += $bags * $pricePerBag;
        }
        return round($total, 2);
    }

    private function estimatePrimaryDose(float $n, float $p, float $k): float
    {
        $deficit = max(0, 42 - $n) + max(0, 42 - $p) + max(0, 42 - $k);
        $dose = round(max(10, min(60, $deficit / 2.5)), 1);
        return $dose;
    }


    private function buildInstructions(string $crop, string $fertilizer, ?float $confidence, array $apiResult): string
    {
        $confidenceText = $confidence === null ? 'N/A' : round($confidence * 100, 2) . '%';
        $lines = [
            "AI Fertilizer Plan for {$crop}",
            "",
            "Primary fertilizer: {$fertilizer}",
            "Model confidence: {$confidenceText}",
            "Request ID: " . (string) ($apiResult['request_id'] ?? 'n/a'),
            "",
            "Application Strategy:",
            "• Apply on moist soil and irrigate lightly within 24 hours.",
            "• Prefer split doses to reduce nutrient loss.",
            "• Avoid application before heavy rainfall events.",
            "",
            "Record all applications with date, product, and quantity in your farm diary.",
        ];
        return implode("\n", $lines);
    }
}


