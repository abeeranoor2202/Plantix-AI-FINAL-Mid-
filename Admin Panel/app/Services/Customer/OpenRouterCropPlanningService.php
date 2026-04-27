<?php

namespace App\Services\Customer;

use App\Models\CropPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenRouterCropPlanningService
 *
 * Generates AI-powered, detailed crop plans using OpenRouter's LLM API.
 * Falls back to the static CropPlanningService if the API is unavailable
 * or the key is not configured.
 *
 * OpenRouter docs: https://openrouter.ai/docs
 * API endpoint   : POST https://openrouter.ai/api/v1/chat/completions
 */
class OpenRouterCropPlanningService
{
    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    private const SYSTEM_PROMPT = <<<PROMPT
You are an expert agronomist and crop planning specialist for Pakistan's agricultural context.
Your task is to generate a comprehensive, practical crop cultivation plan.

You MUST respond with a single valid JSON object — no markdown fences, no extra text.

The JSON must follow this exact structure:
{
  "overview": "2-3 sentence summary of the plan",
  "suitability": {
    "score": <integer 0-100>,
    "label": "<Excellent|Good|Fair|Poor>",
    "notes": ["note1", "note2"]
  },
  "land_preparation": {
    "steps": ["step1", "step2"],
    "timing": "when to do it"
  },
  "sowing": {
    "seed_rate": "amount per acre",
    "spacing": "row and plant spacing",
    "depth": "sowing depth",
    "best_time": "optimal sowing window"
  },
  "fertilizer_schedule": [
    {"stage": "Basal (at sowing)", "fertilizer": "name", "dose": "amount/acre", "notes": "application tip"},
    {"stage": "Top-dress 1", "fertilizer": "name", "dose": "amount/acre", "notes": "timing"}
  ],
  "irrigation_plan": [
    {"stage": "stage name", "timing": "when", "amount": "mm or inches", "notes": "tip"}
  ],
  "pest_disease_management": [
    {"threat": "name", "symptoms": "brief", "control": "organic or chemical remedy"}
  ],
  "harvest": {
    "days_to_maturity": "range",
    "indicators": "how to know it is ready",
    "method": "manual or combine",
    "post_harvest": "storage or curing tip"
  },
  "expected_yield": {
    "range": "min-max per acre",
    "unit": "maunds or tons",
    "revenue_estimate_pkr": "approximate PKR range"
  },
  "key_tips": ["tip1", "tip2", "tip3"]
}
PROMPT;

    public function __construct(
        private readonly CropPlanningService $fallback,
    ) {}

    /**
     * Generate an AI crop plan. Saves to DB and returns the CropPlan model.
     *
     * @param  User     $user
     * @param  array    $input  {primary_crop, season, year, farm_size_acres, soil_type, irrigation_type, climate, water_availability, ...}
     * @param  int|null $farmProfileId
     * @return CropPlan
     */
    public function generate(User $user, array $input, ?int $farmProfileId = null): CropPlan
    {
        $apiKey = config('plantix.openrouter_api_key');

        if (empty($apiKey)) {
            Log::info('OpenRouter key not set — using static CropPlanningService fallback.');
            return $this->fallback->generate($user, $input, $farmProfileId);
        }

        try {
            $aiData = $this->callOpenRouter($input);
            return $this->persistPlan($user, $input, $farmProfileId, $aiData);
        } catch (\Throwable $e) {
            Log::error('OpenRouter crop planning failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'input'   => $input,
            ]);
            // Graceful fallback to static service
            return $this->fallback->generate($user, $input, $farmProfileId);
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Call OpenRouter and return the parsed JSON plan array.
     */
    private function callOpenRouter(array $input): array
    {
        $model   = config('plantix.openrouter_model', 'google/gemma-3-27b-it:free');
        $timeout = (int) config('plantix.openrouter_timeout', 30);

        $userPrompt = $this->buildUserPrompt($input);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('plantix.openrouter_api_key'),
            'HTTP-Referer'  => config('app.url', 'http://localhost'),
            'X-Title'       => config('app.name', 'Plantix AI'),
            'Content-Type'  => 'application/json',
        ])
        ->timeout($timeout)
        ->post(self::OPENROUTER_URL, [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.4,
            'max_tokens'  => 1800,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'OpenRouter API error ' . $response->status() . ': ' . $response->body()
            );
        }

        $raw = $response->json('choices.0.message.content', '');

        // Strip any accidental markdown fences
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/', '', $raw);

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new \RuntimeException('OpenRouter returned invalid JSON: ' . substr($raw, 0, 300));
        }

        return $data;
    }

    /**
     * Build the user-facing prompt from the input parameters.
     */
    private function buildUserPrompt(array $input): string
    {
        $crop     = $input['primary_crop']    ?? 'wheat';
        $season   = $input['season']          ?? 'Rabi';
        $year     = $input['year']            ?? now()->year;
        $acres    = $input['farm_size_acres'] ?? 1;
        $soil     = $input['soil_type']       ?? 'loamy';
        $irr      = $input['irrigation_type'] ?? 'canal';
        $climate  = $input['climate']         ?? 'subtropical';
        $water    = $input['water_availability'] ?? 'moderate';

        return <<<PROMPT
Generate a complete crop cultivation plan for the following farm:

- Crop: {$crop}
- Season: {$season} {$year}
- Farm size: {$acres} acres
- Soil type: {$soil}
- Irrigation type: {$irr}
- Climate: {$climate}
- Water availability: {$water}
- Location context: Pakistan (Punjab/Sindh/KPK region)

Provide practical, actionable advice specific to Pakistani farming conditions.
Include local fertilizer brand names (Urea, DAP, SOP, MOP, Zarkhez) and local pest/disease names.
All fertilizer doses should be in kg/acre. Revenue estimates in PKR.
PROMPT;
    }

    /**
     * Persist the AI-generated plan into the crop_plans table.
     */
    private function persistPlan(User $user, array $input, ?int $farmProfileId, array $ai): CropPlan
    {
        $season  = $input['season']          ?? 'Rabi';
        $year    = (int) ($input['year']     ?? now()->year);
        $crop    = $input['primary_crop']    ?? '';
        $acres   = (float) ($input['farm_size_acres'] ?? 1.0);
        $soil    = $input['soil_type']       ?? 'loamy';

        // Build crop_schedule from AI fertilizer + irrigation data
        $schedule = $this->buildScheduleFromAi($ai, $crop, $season, $year);

        // Build water_plan from AI irrigation_plan
        $waterPlan = $this->buildWaterPlanFromAi($ai, $acres);

        // Extract yield / revenue
        $yieldRange = $ai['expected_yield']['range'] ?? '1-2';
        $yieldTons  = $this->parseYieldTons($yieldRange, $acres);
        $revenue    = $this->parseRevenuePkr($ai['expected_yield']['revenue_estimate_pkr'] ?? '0');

        // Soil suitability notes
        $suitabilityNotes = implode(' ', $ai['suitability']['notes'] ?? []);
        if ($ai['suitability']['label'] ?? null) {
            $suitabilityNotes = "Suitability: {$ai['suitability']['label']} ({$ai['suitability']['score']}%). " . $suitabilityNotes;
        }

        // Recommendations text
        $recommendations = $ai['overview'] ?? '';
        if (!empty($ai['key_tips'])) {
            $recommendations .= "\n\nKey Tips:\n• " . implode("\n• ", $ai['key_tips']);
        }

        return CropPlan::create([
            'user_id'                => $user->id,
            'farm_profile_id'        => $farmProfileId,
            'season'                 => $season,
            'year'                   => $year,
            'primary_crop'           => $crop,
            'crop_schedule'          => $schedule,
            'water_plan'             => $waterPlan,
            'expected_yield_tons'    => $yieldTons,
            'estimated_revenue'      => $revenue,
            'soil_suitability_notes' => $suitabilityNotes,
            'recommendations'        => $recommendations,
            'status'                 => 'active',
            // Store full AI response for rich frontend rendering
            'ai_plan_data'           => $ai,
        ]);
    }

    private function buildScheduleFromAi(array $ai, string $crop, string $season, int $year): array
    {
        $schedule = [];
        $startDates = ['Rabi' => "{$year}-10-01", 'Kharif' => "{$year}-06-01", 'Zaid' => "{$year}-03-01"];
        $startDate  = new \DateTime($startDates[$season] ?? "{$year}-10-01");

        // Land prep
        if (!empty($ai['land_preparation']['steps'])) {
            $schedule[] = [
                'phase'      => 'Land Preparation',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => (clone $startDate)->modify('+13 days')->format('Y-m-d'),
                'notes'      => implode('; ', $ai['land_preparation']['steps']),
                'crop'       => $crop,
            ];
        }

        // Sowing
        if (!empty($ai['sowing'])) {
            $sowStart = (clone $startDate)->modify('+14 days');
            $schedule[] = [
                'phase'      => 'Sowing',
                'start_date' => $sowStart->format('Y-m-d'),
                'end_date'   => (clone $sowStart)->modify('+13 days')->format('Y-m-d'),
                'notes'      => "Seed rate: {$ai['sowing']['seed_rate']}. Spacing: {$ai['sowing']['spacing']}. Depth: {$ai['sowing']['depth']}.",
                'crop'       => $crop,
            ];
        }

        // Fertilizer stages
        foreach ($ai['fertilizer_schedule'] ?? [] as $i => $fert) {
            $offset = 28 + ($i * 21);
            $stageStart = (clone $startDate)->modify("+{$offset} days");
            $schedule[] = [
                'phase'      => $fert['stage'] ?? "Fertilizer Stage " . ($i + 1),
                'start_date' => $stageStart->format('Y-m-d'),
                'end_date'   => (clone $stageStart)->modify('+6 days')->format('Y-m-d'),
                'notes'      => "{$fert['fertilizer']} @ {$fert['dose']}. {$fert['notes']}",
                'crop'       => $crop,
            ];
        }

        // Harvest
        if (!empty($ai['harvest'])) {
            $harvestStart = (clone $startDate)->modify('+140 days');
            $schedule[] = [
                'phase'      => 'Harvest',
                'start_date' => $harvestStart->format('Y-m-d'),
                'end_date'   => (clone $harvestStart)->modify('+13 days')->format('Y-m-d'),
                'notes'      => "{$ai['harvest']['indicators']} Method: {$ai['harvest']['method']}. {$ai['harvest']['post_harvest']}",
                'crop'       => $crop,
            ];
        }

        return $schedule;
    }

    private function buildWaterPlanFromAi(array $ai, float $acres): array
    {
        $plan = [];
        foreach ($ai['irrigation_plan'] ?? [] as $i => $irr) {
            $plan[] = [
                'week'                   => $i + 1,
                'stage'                  => $irr['stage'] ?? "Irrigation " . ($i + 1),
                'timing'                 => $irr['timing'] ?? '',
                'irrigation_mm'          => $irr['amount'] ?? '50mm',
                'irrigation_acre_inches' => round($acres * 2, 2),
                'note'                   => $irr['notes'] ?? null,
            ];
        }
        return $plan;
    }

    private function parseYieldTons(string $range, float $acres): float
    {
        // Extract first number from strings like "25-35 maunds/acre" or "1.5-2 tons"
        preg_match('/[\d.]+/', $range, $m);
        $perAcre = isset($m[0]) ? (float) $m[0] : 1.5;

        // Convert maunds to tons if needed (1 maund ≈ 0.04 tons)
        if (stripos($range, 'maund') !== false) {
            $perAcre = $perAcre * 0.04;
        }

        return round($perAcre * $acres, 3);
    }

    private function parseRevenuePkr(string $revenueStr): float
    {
        // Extract first number from strings like "PKR 50,000-80,000" or "50000-80000"
        $clean = str_replace([',', 'PKR', 'Rs', ' '], '', $revenueStr);
        preg_match('/[\d.]+/', $clean, $m);
        return isset($m[0]) ? (float) $m[0] : 0.0;
    }
}
