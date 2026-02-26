<?php

namespace App\Services;

use App\Models\CropPlan;
use App\Models\FarmProfile;
use App\Models\SeasonalData;
use App\Models\User;

/**
 * CropPlanningService
 *
 * Generates seasonal crop plans with water usage, phasing timeline,
 * soil suitability assessment, and revenue estimates.
 */
class CropPlanningService
{
    /**
     * Generate a crop plan for a user.
     *
     * @param User        $user
     * @param array       $input  {season, year, primary_crop, farm_size_acres, soil_type, ...}
     * @param int|null    $farmProfileId
     * @return CropPlan
     */
    public function generate(User $user, array $input, ?int $farmProfileId = null): CropPlan
    {
        $season       = $input['season'] ?? 'Rabi';
        $year         = (int)($input['year'] ?? now()->year);
        $primaryCrop  = $input['primary_crop'] ?? '';
        $farmAcres    = (float)($input['farm_size_acres'] ?? 1.0);
        $soilType     = $input['soil_type'] ?? 'loamy';

        // Load seasonal data for this crop + season
        $seasonal = SeasonalData::where('crop_name', $primaryCrop)
            ->where('season', $season)
            ->where('is_active', true)
            ->first();

        // Build the crop schedule (phase-by-phase)
        $schedule     = $this->buildSchedule($primaryCrop, $season, $year, $seasonal);
        $waterPlan    = $this->buildWaterPlan($primaryCrop, $farmAcres, $seasonal);
        $yieldTons    = $this->estimateYield($seasonal, $farmAcres);
        $revenue      = $this->estimateRevenue($primaryCrop, $yieldTons);
        $suitability  = $this->assessSoilSuitability($soilType, $seasonal);
        $recommendations = $this->buildRecommendations($primaryCrop, $soilType, $season, $seasonal);

        return CropPlan::create([
            'user_id'                  => $user->id,
            'farm_profile_id'          => $farmProfileId,
            'season'                   => $season,
            'year'                     => $year,
            'primary_crop'             => $primaryCrop,
            'crop_schedule'            => $schedule,
            'water_plan'               => $waterPlan,
            'expected_yield_tons'      => $yieldTons,
            'estimated_revenue'        => $revenue,
            'soil_suitability_notes'   => $suitability,
            'recommendations'          => $recommendations,
            'status'                   => 'active',
        ]);
    }

    // ── Private builders ──────────────────────────────────────────────────────

    private function buildSchedule(string $crop, string $season, int $year, ?SeasonalData $data): array
    {
        // Default phase templates per season
        $templates = [
            'Rabi' => [
                ['phase' => 'Land Preparation',  'start_week' => 1,  'end_week' => 2,  'notes' => 'Deep ploughing, leveling, FYM application'],
                ['phase' => 'Sowing',            'start_week' => 3,  'end_week' => 4,  'notes' => 'Seed treatment, row sowing at 20–25 cm spacing'],
                ['phase' => 'Germination',        'start_week' => 5,  'end_week' => 6,  'notes' => 'First irrigation if moisture below 60%'],
                ['phase' => 'Vegetative Growth', 'start_week' => 7,  'end_week' => 14, 'notes' => 'Fertilizer top-dressing, weeding'],
                ['phase' => 'Flowering',          'start_week' => 15, 'end_week' => 18, 'notes' => 'Protect from frost, maintain moisture'],
                ['phase' => 'Grain Filling',      'start_week' => 19, 'end_week' => 22, 'notes' => 'Reduce nitrogen, maintain potassium'],
                ['phase' => 'Maturity',           'start_week' => 23, 'end_week' => 24, 'notes' => 'Monitor for field dryness before harvest'],
                ['phase' => 'Harvest',            'start_week' => 25, 'end_week' => 26, 'notes' => 'Combine or manual harvesting, threshing'],
            ],
            'Kharif' => [
                ['phase' => 'Land Preparation',  'start_week' => 1,  'end_week' => 2,  'notes' => 'Ploughing, bund making for flood irrigation'],
                ['phase' => 'Sowing / Transplanting', 'start_week' => 3, 'end_week' => 5, 'notes' => 'Direct seeding or nursery transplanting'],
                ['phase' => 'Tillering',          'start_week' => 6,  'end_week' => 10, 'notes' => '1st weeding + nitrogen split dose'],
                ['phase' => 'Vegetative Growth', 'start_week' => 11, 'end_week' => 16, 'notes' => '2nd N dose, pest scouting'],
                ['phase' => 'Flowering / Panicle', 'start_week' => 17, 'end_week' => 20, 'notes' => 'Critical water period, avoid water stress'],
                ['phase' => 'Grain Filling',      'start_week' => 21, 'end_week' => 24, 'notes' => 'Intermittent irrigation, fungicide if needed'],
                ['phase' => 'Maturity',           'start_week' => 25, 'end_week' => 26, 'notes' => 'Drain fields 2 weeks before harvest'],
                ['phase' => 'Harvest',            'start_week' => 27, 'end_week' => 28, 'notes' => 'Combine at 18–20% moisture content'],
            ],
            'Zaid' => [
                ['phase' => 'Land Preparation',  'start_week' => 1,  'end_week' => 1,  'notes' => 'Rapid tillage, residue incorporation'],
                ['phase' => 'Sowing',            'start_week' => 2,  'end_week' => 3,  'notes' => 'Short-duration variety selection critical'],
                ['phase' => 'Vegetative Growth', 'start_week' => 4,  'end_week' => 8,  'notes' => 'High temperature management, shade nets if needed'],
                ['phase' => 'Flowering',          'start_week' => 9,  'end_week' => 10, 'notes' => 'Ensure pollination, avoid heat stress'],
                ['phase' => 'Pod / Fruit Fill',   'start_week' => 11, 'end_week' => 12, 'notes' => 'Frequent irrigation every 5–7 days'],
                ['phase' => 'Harvest',            'start_week' => 13, 'end_week' => 14, 'notes' => 'Staggered harvest for vegetables'],
            ],
        ];

        $phases = $templates[$season] ?? $templates['Rabi'];

        // Determine season start date
        $startDates = ['Rabi' => "{$year}-10-01", 'Kharif' => "{$year}-06-01", 'Zaid' => "{$year}-03-01"];
        $startDate  = new \DateTime($startDates[$season] ?? "{$year}-10-01");

        foreach ($phases as &$phase) {
            $sowDate   = (clone $startDate)->modify('+' . (($phase['start_week'] - 1) * 7) . ' days');
            $endDate   = (clone $startDate)->modify('+' . (($phase['end_week']) * 7 - 1) . ' days');
            $phase['start_date'] = $sowDate->format('Y-m-d');
            $phase['end_date']   = $endDate->format('Y-m-d');
            $phase['crop']       = $crop;
        }

        return $phases;
    }

    private function buildWaterPlan(string $crop, float $acres, ?SeasonalData $data): array
    {
        $totalMm   = $data ? (float)$data->water_requirement_mm : 500;
        $weeks     = 26;
        $perWeekMm = round($totalMm / $weeks, 1);

        $plan = [];
        for ($week = 1; $week <= $weeks; $week++) {
            // Boost water during flowering/grain fill weeks
            $multiplier = ($week >= 15 && $week <= 22) ? 1.3 : 1.0;
            $plan[] = [
                'week'            => $week,
                'irrigation_mm'   => round($perWeekMm * $multiplier, 1),
                'irrigation_acre_inches' => round(($perWeekMm * $multiplier * $acres) / 25.4, 2),
                'note'            => $week === 1 ? 'Pre-sowing irrigation' : null,
            ];
        }
        return $plan;
    }

    private function estimateYield(?SeasonalData $data, float $acres): float
    {
        $avgYield = $data ? (float)$data->avg_yield_tons_per_acre : 1.5;
        return round($avgYield * $acres, 3);
    }

    private function estimateRevenue(string $crop, float $yieldTons): float
    {
        // Market reference prices (PKR per ton) – configurable via settings later
        $prices = [
            'Wheat' => 56000, 'Rice' => 70000, 'Maize' => 40000, 'Cotton' => 120000,
            'Sugarcane' => 4000, 'Potato' => 30000, 'Tomato' => 40000, 'Onion' => 35000,
            'Mango' => 80000, 'Banana' => 50000, 'Chickpea' => 120000, 'Lentil' => 130000,
            'Groundnut' => 90000, 'Sunflower' => 70000, 'Sorghum' => 35000,
        ];
        $pricePerTon = $prices[$crop] ?? 50000;
        return round($yieldTons * $pricePerTon, 2);
    }

    private function assessSoilSuitability(string $soilType, ?SeasonalData $data): string
    {
        $compatibility = $data?->soil_type_compatibility ?? '';
        $soilType = strtolower($soilType);

        if ($compatibility && str_contains(strtolower($compatibility), $soilType)) {
            return "Your {$soilType} soil is well-suited for this crop. {$compatibility}";
        }

        $generic = [
            'loamy'  => 'Loamy soil is ideal for most crops due to balanced drainage and nutrient retention.',
            'clay'   => 'Clay soil retains moisture well but may need organic matter amendments for aeration.',
            'sandy'  => 'Sandy soil drains fast; increase irrigation frequency and add compost.',
            'silt'   => 'Silty soil has good fertility but risk of compaction; avoid heavy machinery.',
            'peat'   => 'Peaty soil is high in organic matter; ensure pH adjustment with lime.',
        ];
        return $generic[$soilType] ?? 'Soil assessment based on standard guidelines. Consider a lab test for precision.';
    }

    private function buildRecommendations(string $crop, string $soilType, string $season, ?SeasonalData $data): string
    {
        $sow    = $data?->sowing_months ?? 'October';
        $harv   = $data?->harvesting_months ?? 'April';
        $water  = $data?->water_requirement_mm ?? 500;

        return "Sow {$crop} in {$sow} and harvest in {$harv}. "
             . "Total water requirement is approximately {$water} mm/season. "
             . "Apply balanced NPK fertilizer at sowing (1/3 N, full P, full K) and top-dress remaining nitrogen in splits. "
             . "Monitor for pest and disease pressure especially during flowering. "
             . "Maintain a crop journal to track actual vs planned inputs for future optimisation.";
    }
}
