<?php

namespace App\Services;

use App\Models\FertilizerRecommendation;
use App\Models\SoilTest;
use App\Models\User;

/**
 * FertilizerRecommendationService
 *
 * Rule-based engine following FAO/NARC Pakistan fertilizer guidelines.
 * Produces a detailed split-application fertilizer plan.
 */
class FertilizerRecommendationService
{
    /**
     * Crop-specific nutrient removal rates and recommended application (kg/ha per ton yield).
     * Format: [N_rate, P2O5_rate, K2O_rate, base_dose_N, base_dose_P, base_dose_K]
     */
    private const CROP_NUTRIENT_GUIDE = [
        'Wheat'    => ['N' => 120, 'P' => 90,  'K' => 60,  'target_yield' => 5],
        'Rice'     => ['N' => 120, 'P' => 60,  'K' => 40,  'target_yield' => 5],
        'Maize'    => ['N' => 150, 'P' => 80,  'K' => 60,  'target_yield' => 6],
        'Cotton'   => ['N' => 100, 'P' => 60,  'K' => 50,  'target_yield' => 4],
        'Potato'   => ['N' => 200, 'P' => 150, 'K' => 200, 'target_yield' => 25],
        'Tomato'   => ['N' => 180, 'P' => 100, 'K' => 150, 'target_yield' => 30],
        'Onion'    => ['N' => 100, 'P' => 80,  'K' => 80,  'target_yield' => 20],
        'Sugarcane'=> ['N' => 250, 'P' => 120, 'K' => 150, 'target_yield' => 80],
        'Chickpea' => ['N' => 20,  'P' => 80,  'K' => 40,  'target_yield' => 2],
        'Groundnut'=> ['N' => 25,  'P' => 60,  'K' => 60,  'target_yield' => 3],
        'Sunflower'=> ['N' => 80,  'P' => 60,  'K' => 60,  'target_yield' => 3],
        'Sorghum'  => ['N' => 100, 'P' => 50,  'K' => 40,  'target_yield' => 4],
    ];

    /**
     * Common commercial fertilizers available in Pakistan.
     * [{name, type, N%, P2O5%, K2O%, price_pkr_per_bag_50kg}]
     */
    private const FERTILIZERS = [
        ['name' => 'Urea (46-0-0)',         'type' => 'nitrogen',   'N' => 46, 'P' => 0,  'K' => 0,  'price' => 3200],
        ['name' => 'DAP (18-46-0)',          'type' => 'phosphorus', 'N' => 18, 'P' => 46, 'K' => 0,  'price' => 6000],
        ['name' => 'MOP / SOP (0-0-60)',     'type' => 'potassium',  'N' => 0,  'P' => 0,  'K' => 60, 'price' => 5000],
        ['name' => 'NP (23-23-0)',           'type' => 'compound',   'N' => 23, 'P' => 23, 'K' => 0,  'price' => 5200],
        ['name' => 'NPK (15-15-15)',         'type' => 'compound',   'N' => 15, 'P' => 15, 'K' => 15, 'price' => 5500],
        ['name' => 'SOP (0-0-50)',           'type' => 'potassium',  'N' => 0,  'P' => 0,  'K' => 50, 'price' => 4800],
        ['name' => 'Ammonium Sulphate (21%)', 'type' => 'nitrogen',  'N' => 21, 'P' => 0,  'K' => 0,  'price' => 3000],
    ];

    /**
     * Generate a fertilizer plan for the given crop and soil conditions.
     *
     * @param  User     $user
     * @param  array    $input  {crop_type, growth_stage, nitrogen, phosphorus, potassium, ph_level, temperature, humidity}
     * @param  int|null $soilTestId
     * @return FertilizerRecommendation
     */
    public function recommend(User $user, array $input, ?int $soilTestId = null): FertilizerRecommendation
    {
        $crop         = $input['crop_type'] ?? 'Wheat';
        $growthStage  = $input['growth_stage'] ?? 'pre-sowing';
        $soilN        = (float)($input['nitrogen']   ?? 0);
        $soilP        = (float)($input['phosphorus'] ?? 0);
        $soilK        = (float)($input['potassium']  ?? 0);
        $pH           = (float)($input['ph_level']   ?? 7.0);

        $guide = self::CROP_NUTRIENT_GUIDE[$crop] ?? self::CROP_NUTRIENT_GUIDE['Wheat'];

        // Calculate deficiencies
        $requiredN = $guide['N'];
        $requiredP = $guide['P'];
        $requiredK = $guide['K'];

        $defN = max(0, $requiredN - $soilN);
        $defP = max(0, $requiredP - $soilP);
        $defK = max(0, $requiredK - $soilK);

        // pH correction recommendation
        $phNote = $this->getPHNote($pH);

        // Build fertilizer plan
        $plan = $this->buildPlan($crop, $defN, $defP, $defK, $growthStage, $guide);

        // Estimate cost
        $cost = $this->estimateCost($plan);

        // Build instructions
        $instructions = $this->buildInstructions($crop, $growthStage, $phNote, $defN, $defP, $defK);

        return FertilizerRecommendation::create([
            'user_id'                   => $user->id,
            'soil_test_id'              => $soilTestId,
            'crop_type'                 => $crop,
            'growth_stage'              => $growthStage,
            'nitrogen'                  => $soilN,
            'phosphorus'                => $soilP,
            'potassium'                 => $soilK,
            'ph_level'                  => $pH,
            'temperature'               => $input['temperature'] ?? null,
            'humidity'                  => $input['humidity'] ?? null,
            'fertilizer_plan'           => $plan,
            'application_instructions'  => $instructions,
            'estimated_cost_pkr'        => $cost,
            'model_version'             => 'rule-based-v2',
        ]);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function buildPlan(
        string $crop, float $defN, float $defP, float $defK,
        string $stage, array $guide
    ): array {
        $plan = [];

        // Nitrogen split: 1/3 basal + 1/3 tillering/vegetative + 1/3 top-dress
        if ($defN > 0) {
            $ureaKgPerHa = round($defN / 0.46, 1);
            $plan[] = [
                'name'              => 'Urea (46-0-0)',
                'type'              => 'Nitrogenous',
                'dose_kg_per_acre'  => round($ureaKgPerHa / 2.471, 1),
                'timing'            => 'Basal (33%) + Side-dress at 4 weeks (33%) + Flag leaf (33%)',
                'notes'             => 'Do not apply in flood; incorporate before irrigation. pH 6-7.5 optimal.',
            ];
        }

        // Phosphorus: full dose basal
        if ($defP > 0) {
            $dapKgPerHa = round($defP / 0.46, 1);
            $plan[] = [
                'name'              => 'DAP (18-46-0)',
                'type'              => 'Phosphatic',
                'dose_kg_per_acre'  => round($dapKgPerHa / 2.471, 1),
                'timing'            => 'Full basal dose at sowing/transplanting',
                'notes'             => 'Place in furrow for direct root contact. Avoid alkaline conditions.',
            ];
        }

        // Potassium: full dose basal (split if >80 kg/ha)
        if ($defK > 0) {
            $mopKgPerHa = round($defK / 0.60, 1);
            $split      = $defK > 80 ? 'Half basal + Half at 4 weeks' : 'Full basal dose';
            $plan[] = [
                'name'              => 'MOP / SOP (0-0-60)',
                'type'              => 'Potassic',
                'dose_kg_per_acre'  => round($mopKgPerHa / 2.471, 1),
                'timing'            => $split,
                'notes'             => 'Potassium enhances drought tolerance and grain quality.',
            ];
        }

        // Micronutrient flag – zinc deficiency common in Pakistan soils
        $plan[] = [
            'name'              => 'Zinc Sulphate (33% Zn)',
            'type'              => 'Micronutrient',
            'dose_kg_per_acre'  => 5.0,
            'timing'            => 'Basal once per 3 seasons',
            'notes'             => 'Zinc deficiency is widespread in Pakistani soils. Apply if not done in last 3 years.',
        ];

        return $plan;
    }

    private function estimateCost(array $plan): float
    {
        $fertPriceMap = [];
        foreach (self::FERTILIZERS as $f) {
            $fertPriceMap[$f['name']] = $f['price'];
        }

        $total = 0;
        foreach ($plan as $item) {
            $pricePerBag  = $fertPriceMap[$item['name']] ?? 4000;
            $dosePerAcre  = (float)($item['dose_kg_per_acre'] ?? 0);
            $bags         = ceil($dosePerAcre / 50);
            $total       += $bags * $pricePerBag;
        }
        return round($total, 2);
    }

    private function getPHNote(float $pH): string
    {
        if ($pH < 5.5) {
            return 'Soil is strongly acidic (pH ' . $pH . '). Apply agricultural lime @ 500-1000 kg/acre to raise pH.';
        } elseif ($pH < 6.0) {
            return 'Soil is moderately acidic (pH ' . $pH . '). Light liming recommended.';
        } elseif ($pH > 8.5) {
            return 'Soil is strongly alkaline (pH ' . $pH . '). Apply gypsum @ 250-500 kg/acre + organic matter.';
        } elseif ($pH > 7.5) {
            return 'Soil is slightly alkaline (pH ' . $pH . '). Elemental sulphur application @ 50 kg/acre advised.';
        }
        return 'Soil pH (' . $pH . ') is within optimal range (6.0–7.5). No pH correction needed.';
    }

    private function buildInstructions(
        string $crop, string $stage, string $phNote,
        float $defN, float $defP, float $defK
    ): string {
        $lines = [
            "Fertilizer Plan for {$crop} (Growth Stage: {$stage})",
            "",
            "pH Assessment: {$phNote}",
            "",
            "Application Strategy:",
            "• Basal dose: Apply phosphatic and potassic fertilizers at sowing. Incorporate with first tillage.",
            "• Nitrogen management: Split application is critical. Never apply full N at once to avoid volatilization.",
            "• Timing: Apply fertilizers in the morning or evening, not during peak heat.",
            "• Moisture: Ensure adequate soil moisture at application or irrigate within 24 hours.",
            "",
            "Deficiency Summary:",
            "• Nitrogen deficit: {$defN} kg/ha → Apply recommended urea amount.",
            "• Phosphorus deficit: {$defP} kg/ha → Apply DAP as primary source.",
            "• Potassium deficit: {$defK} kg/ha → Apply MOP/SOP accordingly.",
            "",
            "Record all applications with date, product, and quantity in your farm diary.",
        ];
        return implode("\n", $lines);
    }
}
