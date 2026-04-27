<?php

namespace App\Services\Customer;

use App\Models\CropPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * OpenRouterCropPlanningService
 *
 * Generates AI-powered crop plans using OpenRouter LLM API.
 * Falls back to CropPlanningService if the key is missing or the API fails.
 *
 * OpenRouter docs : https://openrouter.ai/docs
 * Endpoint        : POST https://openrouter.ai/api/v1/chat/completions
 */
class OpenRouterCropPlanningService
{
    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Keywords that must appear in a valid crop-planning request.
     * If none of these are present in the combined input string, the request
     * is considered off-topic and rejected before hitting the API.
     */
    private const AGRICULTURE_KEYWORDS = [
        'crop', 'wheat', 'rice', 'maize', 'corn', 'cotton', 'sugarcane',
        'potato', 'tomato', 'onion', 'mango', 'citrus', 'sunflower',
        'soybean', 'barley', 'sorghum', 'millet', 'vegetable', 'fruit',
        'farm', 'soil', 'fertilizer', 'irrigation', 'harvest', 'sowing',
        'season', 'rabi', 'kharif', 'zaid', 'acre', 'yield', 'pest',
        'disease', 'agri', 'cultivation', 'plantation', 'field',
    ];

    private const SYSTEM_PROMPT = 'You are an expert agronomist for Pakistan. Generate a comprehensive crop cultivation plan. Respond ONLY with a single valid JSON object — no markdown fences, no extra text. Use this exact structure: {"overview":"string","suitability":{"score":0,"label":"string","notes":[]},"land_preparation":{"steps":[],"timing":"string"},"sowing":{"seed_rate":"string","spacing":"string","depth":"string","best_time":"string"},"fertilizer_schedule":[{"stage":"string","fertilizer":"string","dose":"string","notes":"string"}],"irrigation_plan":[{"stage":"string","timing":"string","amount":"string","notes":"string"}],"pest_disease_management":[{"threat":"string","symptoms":"string","control":"string"}],"harvest":{"days_to_maturity":"string","indicators":"string","method":"string","post_harvest":"string"},"expected_yield":{"range":"string","unit":"string","revenue_estimate_pkr":"string"},"key_tips":[]}';

    public function __construct(
        private readonly CropPlanningService $fallback,
    ) {}

    public function generate(User $user, array $input, ?int $farmProfileId = null): CropPlan
    {
        $apiKey = config('plantix.openrouter_api_key');

        if (empty($apiKey)) {
            Log::info('OpenRouter key not configured — using static CropPlanningService fallback.');
            return $this->fallback->generate($user, $input, $farmProfileId);
        }

        $this->validateAgricultureInput($input);

        try {
            $aiData = $this->callOpenRouter($input);
            return $this->persistPlan($user, $input, $farmProfileId, $aiData);
        } catch (InvalidArgumentException $e) {
            // Off-topic / invalid input — re-throw so the controller can return a 422
            throw $e;
        } catch (\Throwable $e) {
            Log::error('OpenRouter crop planning failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'input'   => $input,
            ]);
            return $this->fallback->generate($user, $input, $farmProfileId);
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Reject requests that have nothing to do with agriculture.
     * Checks the combined input values against a whitelist of agri-keywords.
     *
     * @throws InvalidArgumentException if the input is off-topic.
     */
    private function validateAgricultureInput(array $input): void
    {
        // Build a single lowercase string from all input values for scanning
        $haystack = strtolower(implode(' ', array_map('strval', $input)));

        foreach (self::AGRICULTURE_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return; // at least one agri keyword found — valid request
            }
        }

        throw new InvalidArgumentException(
            'This service only generates agricultural crop plans. '
            . 'Please provide valid farming details (crop, season, soil type, etc.).'
        );
    }

    private function callOpenRouter(array $input): array
    {
        $model   = config('plantix.openrouter_model', 'google/gemini-2.0-flash-001');
        $timeout = (int) config('plantix.openrouter_timeout', 30);

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
                ['role' => 'user',   'content' => $this->buildUserPrompt($input)],
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

    private function buildUserPrompt(array $input): string
    {
        $crop    = $input['primary_crop']       ?? 'wheat';
        $season  = $input['season']             ?? 'Rabi';
        $year    = $input['year']               ?? now()->year;
        $acres   = $input['farm_size_acres']    ?? 1;
        $soil    = $input['soil_type']          ?? 'loamy';
        $irr     = $input['irrigation_type']    ?? 'canal';
        $climate = $input['climate']            ?? 'subtropical';
        $water   = $input['water_availability'] ?? 'moderate';

        return "Generate a complete crop cultivation plan:\n"
             . "- Crop: {$crop}\n"
             . "- Season: {$season} {$year}\n"
             . "- Farm size: {$acres} acres\n"
             . "- Soil type: {$soil}\n"
             . "- Irrigation: {$irr}\n"
             . "- Climate: {$climate}\n"
             . "- Water availability: {$water}\n"
             . "- Location: Pakistan (Punjab/Sindh/KPK)\n"
             . "Use local fertilizer brands (Urea, DAP, SOP, MOP). Doses in kg/acre. Revenue in PKR.";
    }

    private function persistPlan(User $user, array $input, ?int $farmProfileId, array $ai): CropPlan
    {
        $season = $input['season']          ?? 'Rabi';
        $year   = (int) ($input['year']     ?? now()->year);
        $crop   = $input['primary_crop']    ?? '';
        $acres  = (float) ($input['farm_size_acres'] ?? 1.0);

        $schedule  = $this->buildScheduleFromAi($ai, $crop, $season, $year);
        $waterPlan = $this->buildWaterPlanFromAi($ai, $acres);

        $yieldRange = $ai['expected_yield']['range'] ?? '1-2';
        $yieldTons  = $this->parseYieldTons($yieldRange, $acres);
        $revenue    = $this->parseRevenuePkr($ai['expected_yield']['revenue_estimate_pkr'] ?? '0');

        $suitabilityNotes = implode(' ', $ai['suitability']['notes'] ?? []);
        if (!empty($ai['suitability']['label'])) {
            $suitabilityNotes = "Suitability: {$ai['suitability']['label']} ({$ai['suitability']['score']}%). " . $suitabilityNotes;
        }

        $recommendations = $ai['overview'] ?? '';
        if (!empty($ai['key_tips'])) {
            $recommendations .= "\n\nKey Tips:\n- " . implode("\n- ", $ai['key_tips']);
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
            'ai_plan_data'           => $ai,
            'ai_model'               => config('plantix.openrouter_model'),
        ]);
    }

    private function buildScheduleFromAi(array $ai, string $crop, string $season, int $year): array
    {
        $schedule   = [];
        $startDates = ['Rabi' => "{$year}-10-01", 'Kharif' => "{$year}-06-01", 'Zaid' => "{$year}-03-01"];
        $startDate  = new \DateTime($startDates[$season] ?? "{$year}-10-01");

        if (!empty($ai['land_preparation']['steps'])) {
            $schedule[] = [
                'phase'      => 'Land Preparation',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => (clone $startDate)->modify('+13 days')->format('Y-m-d'),
                'notes'      => implode('; ', $ai['land_preparation']['steps']),
                'crop'       => $crop,
            ];
        }

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

        foreach ($ai['fertilizer_schedule'] ?? [] as $i => $fert) {
            $offset     = 28 + ($i * 21);
            $stageStart = (clone $startDate)->modify("+{$offset} days");
            $schedule[] = [
                'phase'      => $fert['stage'] ?? 'Fertilizer Stage ' . ($i + 1),
                'start_date' => $stageStart->format('Y-m-d'),
                'end_date'   => (clone $stageStart)->modify('+6 days')->format('Y-m-d'),
                'notes'      => "{$fert['fertilizer']} @ {$fert['dose']}. {$fert['notes']}",
                'crop'       => $crop,
            ];
        }

        if (!empty($ai['harvest'])) {
            $harvestStart = (clone $startDate)->modify('+140 days');
            $schedule[]   = [
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
                'stage'                  => $irr['stage'] ?? 'Irrigation ' . ($i + 1),
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
        preg_match('/[\d.]+/', $range, $m);
        $perAcre = isset($m[0]) ? (float) $m[0] : 1.5;

        if (stripos($range, 'maund') !== false) {
            $perAcre = $perAcre * 0.04;
        }

        return round($perAcre * $acres, 3);
    }

    private function parseRevenuePkr(string $revenueStr): float
    {
        $clean = str_replace([',', 'PKR', 'Rs', ' '], '', $revenueStr);
        preg_match('/[\d.]+/', $clean, $m);
        return isset($m[0]) ? (float) $m[0] : 0.0;
    }
}