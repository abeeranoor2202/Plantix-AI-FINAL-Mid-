<?php

namespace App\Services\Customer;

use App\Models\CropRecommendation;
use App\Models\SoilTest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * CropRecommendationService
 *
 * Rule-based engine structured for ML API drop-in replacement.
 * Decision logic based on FAO crop-soil nutrient guidelines.
 */
class CropRecommendationService
{
    /**
     * Supported crops with their ideal soil ranges.
     * Format: [N_min, N_max, P_min, P_max, K_min, K_max, pH_min, pH_max, temp_min, temp_max, rainfall_min]
     */
    private const CROP_MATRIX = [
        'Wheat'     => ['N' => [60,120], 'P' => [25,50],  'K' => [30,60],  'pH' => [6.0,7.5], 'temp' => [10,25], 'rain' => [250]],
        'Rice'      => ['N' => [80,120], 'P' => [30,60],  'K' => [40,80],  'pH' => [5.5,7.0], 'temp' => [20,35], 'rain' => [1000]],
        'Maize'     => ['N' => [70,140], 'P' => [30,55],  'K' => [35,70],  'pH' => [5.8,7.0], 'temp' => [18,27], 'rain' => [500]],
        'Cotton'    => ['N' => [60,100], 'P' => [20,45],  'K' => [30,55],  'pH' => [6.0,7.5], 'temp' => [20,35], 'rain' => [500]],
        'Sugarcane' => ['N' => [80,140], 'P' => [25,50],  'K' => [50,100], 'pH' => [6.0,7.5], 'temp' => [20,35], 'rain' => [1000]],
        'Potato'    => ['N' => [60,100], 'P' => [40,70],  'K' => [60,100], 'pH' => [5.0,6.5], 'temp' => [10,20], 'rain' => [400]],
        'Tomato'    => ['N' => [50,100], 'P' => [35,60],  'K' => [40,80],  'pH' => [5.5,7.0], 'temp' => [18,28], 'rain' => [400]],
        'Onion'     => ['N' => [40,80],  'P' => [25,50],  'K' => [30,70],  'pH' => [5.8,7.0], 'temp' => [12,24], 'rain' => [300]],
        'Mango'     => ['N' => [30,60],  'P' => [10,30],  'K' => [30,60],  'pH' => [5.5,7.5], 'temp' => [24,35], 'rain' => [750]],
        'Banana'    => ['N' => [80,160], 'P' => [30,60],  'K' => [80,150], 'pH' => [5.5,7.0], 'temp' => [20,35], 'rain' => [1200]],
        'Chickpea'  => ['N' => [15,40],  'P' => [40,80],  'K' => [20,50],  'pH' => [5.5,7.5], 'temp' => [10,25], 'rain' => [300]],
        'Lentil'    => ['N' => [10,30],  'P' => [30,60],  'K' => [20,40],  'pH' => [5.8,7.0], 'temp' => [15,25], 'rain' => [300]],
        'Groundnut' => ['N' => [20,40],  'P' => [40,80],  'K' => [25,55],  'pH' => [5.5,7.0], 'temp' => [20,30], 'rain' => [500]],
        'Sunflower' => ['N' => [40,80],  'P' => [20,45],  'K' => [30,60],  'pH' => [6.0,7.5], 'temp' => [20,28], 'rain' => [400]],
        'Sorghum'   => ['N' => [50,100], 'P' => [15,35],  'K' => [20,40],  'pH' => [5.5,7.5], 'temp' => [20,35], 'rain' => [300]],
    ];

    /**
     * Generate crop recommendations for a user.
     *
     * @param  User   $user
     * @param  array  $input  ['nitrogen','phosphorus','potassium','ph_level','humidity','rainfall_mm','temperature']
     * @param  int|null $soilTestId
     * @return CropRecommendation
     */
    public function recommend(User $user, array $input, ?int $soilTestId = null): CropRecommendation
    {
        $scored = $this->scoreAllCrops($input);

        // Sort by confidence descending, take top 5
        arsort($scored);
        $top = array_slice($scored, 0, 5, true);

        $recommendedCrops = [];
        foreach ($top as $crop => $confidence) {
            $recommendedCrops[] = [
                'name'       => $crop,
                'confidence' => round($confidence, 1),
                'notes'      => $this->getCropNote($crop, $input),
            ];
        }

        $explanation = $this->buildExplanation($input, $recommendedCrops);

        $attributes = [
            'user_id'           => $user->id ?? null,
            'soil_test_id'      => $soilTestId,
            'nitrogen'          => $input['nitrogen'] ?? null,
            'phosphorus'        => $input['phosphorus'] ?? null,
            'potassium'         => $input['potassium'] ?? null,
            'ph_level'          => $input['ph_level'] ?? null,
            'humidity'          => $input['humidity'] ?? null,
            'rainfall_mm'       => $input['rainfall_mm'] ?? null,
            'temperature'       => $input['temperature'] ?? null,
            'recommended_crops' => $recommendedCrops,
            'explanation'       => $explanation,
            'model_version'     => 'rule-based-v2',
            'status'            => 'completed',
        ];

        // Only persist to the database for authenticated users.
        // Guest users (null user_id) get a transient in-memory result to avoid
        // FK constraint violations and unnecessary DB bloat.
        if (($user->id ?? null) !== null) {
            return CropRecommendation::create($attributes);
        }

        return new CropRecommendation($attributes);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function scoreAllCrops(array $input): array
    {
        $scores = [];
        $N    = (float)($input['nitrogen']    ?? 0);
        $P    = (float)($input['phosphorus']  ?? 0);
        $K    = (float)($input['potassium']   ?? 0);
        $pH   = (float)($input['ph_level']    ?? 7.0);
        $temp = (float)($input['temperature'] ?? 25);
        $rain = (float)($input['rainfall_mm'] ?? 500);

        foreach (self::CROP_MATRIX as $crop => $ranges) {
            $score = 0;
            $factors = 0;

            // Nitrogen score
            $score += $this->rangeScore($N, $ranges['N'][0], $ranges['N'][1]);
            $factors++;

            // Phosphorus score
            $score += $this->rangeScore($P, $ranges['P'][0], $ranges['P'][1]);
            $factors++;

            // Potassium score
            $score += $this->rangeScore($K, $ranges['K'][0], $ranges['K'][1]);
            $factors++;

            // pH score
            $score += $this->rangeScore($pH, $ranges['pH'][0], $ranges['pH'][1]);
            $factors++;

            // Temperature score
            $score += $this->rangeScore($temp, $ranges['temp'][0], $ranges['temp'][1]);
            $factors++;

            // Rainfall score (minimum required)
            $score += ($rain >= $ranges['rain'][0]) ? 100 : (($rain / $ranges['rain'][0]) * 100);
            $factors++;

            $scores[$crop] = $factors > 0 ? round($score / $factors, 2) : 0;
        }

        return $scores;
    }

    /**
     * Score how well a value falls within [min, max].
     * Returns 100 if inside range, penalised outside.
     */
    private function rangeScore(float $value, float $min, float $max): float
    {
        if ($value >= $min && $value <= $max) {
            return 100.0;
        }
        $range = $max - $min;
        if ($range == 0) {
            return $value == $min ? 100.0 : 0.0;
        }
        $deviation = $value < $min ? ($min - $value) : ($value - $max);
        $penalty = min(100, ($deviation / $range) * 120);
        return max(0, 100 - $penalty);
    }

    private function getCropNote(string $crop, array $input): string
    {
        $notes = [
            'Wheat'     => 'Best for Rabi season (October–April). Ensure adequate irrigation.',
            'Rice'      => 'Requires standing water and tropical temperatures.',
            'Maize'     => 'Versatile crop; ideal for both food and fodder.',
            'Cotton'    => 'High nutrient demand; monitor for bollworm.',
            'Sugarcane' => 'Long duration crop (10–12 months); high water requirement.',
            'Potato'    => 'Cool weather crop; hill farming recommended.',
            'Tomato'    => 'High-value vegetable; drip irrigation advised.',
            'Onion'     => 'Low water requirement; good market value.',
            'Mango'     => 'Perennial; long investment but high returns.',
            'Banana'    => 'Tropical; requires warm climate year-round.',
            'Chickpea'  => 'Nitrogen-fixing legume; improves soil health.',
            'Lentil'    => 'Cool-weather legume; low input crop.',
            'Groundnut' => 'Drought-tolerant; good oilseed alternative.',
            'Sunflower'  => 'Short duration oilseed; good for crop rotation.',
            'Sorghum'   => 'Drought-tolerant cereal; excellent for dry areas.',
        ];
        return $notes[$crop] ?? 'Suitable for your soil conditions.';
    }

    private function buildExplanation(array $input, array $crops): string
    {
        $top = $crops[0]['name'] ?? 'Unknown';
        $n = $input['nitrogen'] ?? 'N/A';
        $ph = $input['ph_level'] ?? 'N/A';
        $temp = $input['temperature'] ?? 'N/A';
        return "Based on your soil analysis (N={$n} kg/ha, pH={$ph}, Temperature={$temp}°C), "
             . "{$top} is the most suitable crop for your conditions. "
             . "The recommendation engine analyzed 15 major crops using nutrient range scoring, "
             . "climate compatibility, and rainfall requirements per FAO guidelines.";
    }
}


