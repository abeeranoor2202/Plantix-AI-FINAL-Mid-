<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDiseaseDetection;
use App\Models\CropDiseaseReport;
use App\Models\CropPlan;
use App\Services\Customer\CropPlanningService;
use App\Services\Customer\CropRecommendationService;
use App\Services\Customer\DiseaseDetectionService;
use App\Services\Customer\FertilizerRecommendationService;
use App\Services\Customer\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CustomerAiApiController
 *
 * REST endpoints for all AI / ML features consumed by the mobile / SPA front end.
 *
 * All routes are guarded by `auth:sanctum` (Bearer token).
 *
 * Prefix : /api/customer/ai
 */
class CustomerAiApiController extends Controller
{
    public function __construct(
        private readonly CropRecommendationService    $cropRec,
        private readonly FertilizerRecommendationService $fertRec,
        private readonly DiseaseDetectionService      $diseaseService,
        private readonly WeatherService               $weather,
        private readonly CropPlanningService          $cropPlanning,
    ) {}

    // =========================================================================
    // Crop Recommendation  POST /api/customer/ai/crop-recommendation
    // =========================================================================
    public function cropRecommendation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nitrogen'     => 'required|numeric|min:0|max:500',
            'phosphorus'   => 'required|numeric|min:0|max:500',
            'potassium'    => 'required|numeric|min:0|max:500',
            'ph_level'     => 'required|numeric|min:0|max:14',
            'humidity'     => 'required|numeric|min:0|max:100',
            'rainfall_mm'  => 'required|numeric|min:0',
            'temperature'  => 'required|numeric|min:-10|max:60',
            'soil_test_id' => 'nullable|integer|exists:soil_tests,id',
        ]);

        try {
            $recommendation = $this->cropRec->recommend(
                $request->user(),
                $data,
                $data['soil_test_id'] ?? null,
            );

            $topCrop = $recommendation->recommended_crops[0] ?? [];

            return response()->json([
                'success'        => true,
                'recommendation' => [
                    'id' => $recommendation->id,
                    'crop' => (string) ($topCrop['crop'] ?? $recommendation->top_crop ?? ''),
                    'confidence' => isset($topCrop['confidence_score']) ? (float) $topCrop['confidence_score'] : null,
                    'confidence_percent' => (float) ($topCrop['confidence'] ?? 0),
                    'request_id' => $topCrop['request_id'] ?? null,
                    'record_id' => $topCrop['record_id'] ?? null,
                    'recommended_crops' => $recommendation->recommended_crops,
                    'explanation' => $recommendation->explanation,
                    'created_at'  => $recommendation->created_at->toISOString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Crop recommendation API error: ' . $e->getMessage(), ['user' => $request->user()->id]);
            return response()->json(['success' => false, 'message' => 'Unable to generate recommendation. Please try again.'], 500);
        }
    }

    // =========================================================================
    // Fertilizer Recommendation  POST /api/customer/ai/fertilizer-recommendation
    // =========================================================================
    public function fertilizerRecommendation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nitrogen'     => 'required|numeric|min:0|max:500',
            'phosphorus'   => 'required|numeric|min:0|max:500',
            'potassium'    => 'required|numeric|min:0|max:500',
            'crop_type'    => 'nullable|string|max:100',
            'soil_test_id' => 'nullable|integer|exists:soil_tests,id',
        ]);

        try {
            $recommendation = $this->fertRec->recommend(
                $request->user(),
                $data,
                $data['soil_test_id'] ?? null,
            );

            return response()->json([
                'success'        => true,
                'recommendation' => [
                    'id' => $recommendation->id,
                    'crop_type' => $recommendation->crop_type,
                    'fertilizer_plan' => $recommendation->fertilizer_plan,
                    'application_instructions' => $recommendation->application_instructions,
                    'estimated_cost_pkr' => (float) $recommendation->estimated_cost_pkr,
                    'model_version' => $recommendation->model_version,
                    'created_at' => $recommendation->created_at->toISOString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Fertilizer recommendation API error: ' . $e->getMessage(), ['user' => $request->user()->id]);
            return response()->json(['success' => false, 'message' => 'Unable to generate recommendation. Please try again.'], 500);
        }
    }

    // =========================================================================
    // Disease Detection — Submit  POST /api/customer/ai/disease-detection
    // =========================================================================
    public function diseaseDetectionStore(Request $request): JsonResponse
    {
        $request->validate([
            'image'            => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'crop_name'        => 'nullable|string|max:100',
            'user_description' => 'nullable|string|max:1000',
        ]);

        try {
            // Create a pending report and dispatch async job
            $report = $this->diseaseService->createPendingReport(
                $request->user(),
                $request->file('image'),
                $request->only('crop_name', 'user_description'),
            );

            ProcessDiseaseDetection::dispatch($report);

            return response()->json([
                'success'   => true,
                'message'   => 'Image uploaded. Detection is processing.',
                'report_id' => $report->id,
                'status'    => $report->status,
            ], 202);
        } catch (\Throwable $e) {
            Log::error('Disease detection submit API error: ' . $e->getMessage(), ['user' => $request->user()->id]);
            return response()->json(['success' => false, 'message' => 'Failed to process image. Please try again.'], 500);
        }
    }

    // =========================================================================
    // Disease Detection — Status  GET /api/customer/ai/disease-detection/{reportId}/status
    // =========================================================================
    public function diseaseDetectionStatus(Request $request, int $reportId): JsonResponse
    {
        $report = CropDiseaseReport::with('suggestion')
            ->where('user_id', $request->user()->id)
            ->findOrFail($reportId);

        $payload = [
            'report_id'  => $report->id,
            'status'     => $report->status,    // pending | processing | processed | manual_review
            'crop_name'  => $report->crop_name,
            'image_url'  => $report->image_path ? asset('storage/' . $report->image_path) : null,
            'created_at' => $report->created_at->toISOString(),
        ];

        if ($report->status === 'processed' && $report->suggestion) {
            $payload['result'] = [
                'disease'          => $report->detected_disease,
                'confidence'       => $report->confidence_score,
                'disease_name'     => $report->suggestion->disease_name,
                'description'      => $report->suggestion->description,
                'organic_remedy'   => $report->suggestion->organic_remedy,
                'chemical_remedy'  => $report->suggestion->chemical_remedy,
                'prevention_tips'  => $report->suggestion->prevention_tips,
                'recommended_products' => $report->suggestion->recommended_products ?? [],
            ];
        }

        if ($report->status === 'manual_review') {
            $payload['message'] = 'Our system flagged this for expert review. An expert will respond shortly.';
        }

        return response()->json(['success' => true, 'data' => $payload]);
    }

    // =========================================================================
    // Weather  GET /api/customer/weather
    // =========================================================================
    public function weather(Request $request): JsonResponse
    {
        $city = $request->query('city');
        $lat  = $request->query('lat');
        $lon  = $request->query('lon');

        try {
            if ($city) {
                $weatherData = $this->weather->getWeatherForCity($city);
            } elseif ($lat && $lon) {
                $weatherData = $this->weather->getWeatherByCoords((float) $lat, (float) $lon);
            } else {
                $weatherData = $this->weather->getWeatherForUser($request->user());
            }

            $alert = $this->weather->checkAgricultureAlert($weatherData, $request->user());

            return response()->json([
                'success' => true,
                'weather' => $weatherData,
                'alert'   => $alert,
            ]);
        } catch (\Throwable $e) {
            Log::error('Weather API error: ' . $e->getMessage(), ['user' => $request->user()->id]);
            return response()->json(['success' => false, 'message' => 'Weather data unavailable.'], 503);
        }
    }

    // =========================================================================
    // Crop Plans — List  GET /api/customer/crop-plans
    // =========================================================================
    public function cropPlansIndex(Request $request): JsonResponse
    {
        $plans = CropPlan::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'plans'   => $plans->map(fn ($p) => [
                'id'           => $p->id,
                'season'       => $p->season,
                'year'         => $p->year,
                'primary_crop' => $p->primary_crop,
                'farm_acres'   => $p->farm_size_acres,
                'status'       => $p->status,
                'created_at'   => $p->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page'    => $plans->lastPage(),
                'total'        => $plans->total(),
            ],
        ]);
    }

    // =========================================================================
    // Crop Plans — Generate  POST /api/customer/crop-plans
    // =========================================================================
    public function cropPlansStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season'           => 'required|in:Rabi,Kharif,Zaid,Summer,Winter',
            'year'             => 'required|integer|min:2000|max:2050',
            'primary_crop'     => 'required|string|max:100',
            'farm_size_acres'  => 'required|numeric|min:0.1|max:100000',
            'soil_type'        => 'nullable|string|max:50',
            'irrigation_type'  => 'nullable|string|max:50',
            'farm_profile_id'  => 'nullable|integer|exists:farm_profiles,id',
        ]);

        try {
            $plan = $this->cropPlanning->generate(
                $request->user(),
                $data,
                $data['farm_profile_id'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Crop plan generated successfully.',
                'plan'    => [
                    'id'           => $plan->id,
                    'season'       => $plan->season,
                    'year'         => $plan->year,
                    'primary_crop' => $plan->primary_crop,
                    'schedule'     => $plan->schedule,
                    'water_plan'   => $plan->water_plan,
                    'estimated_yield_tons'    => $plan->estimated_yield_tons,
                    'estimated_revenue_pkr'   => $plan->estimated_revenue_pkr,
                    'soil_suitability'        => $plan->soil_suitability,
                    'recommendations'         => $plan->recommendations,
                    'created_at'              => $plan->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Crop plan generate API error: ' . $e->getMessage(), ['user' => $request->user()->id]);
            return response()->json(['success' => false, 'message' => 'Unable to generate crop plan. Please try again.'], 500);
        }
    }
}
