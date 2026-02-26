<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropDiseaseReport;
use App\Models\CropPlan;
use App\Models\CropRecommendation;
use App\Models\FertilizerRecommendation;
use App\Models\SeasonalData;
use App\Services\DiseaseDetectionService;
use Illuminate\Http\Request;

/**
 * AdminAiModuleController
 *
 * Provides admin oversight of all AI module submissions:
 * crop recommendations, crop plans, disease reports, fertilizer plans.
 */
class AdminAiModuleController extends Controller
{
    public function __construct(private DiseaseDetectionService $diseaseService) {}

    // ── Dashboard overview ─────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'crop_recommendations'       => CropRecommendation::count(),
            'crop_plans'                 => CropPlan::count(),
            'disease_reports'            => CropDiseaseReport::count(),
            'disease_pending'            => CropDiseaseReport::pending()->count(),
            'fertilizer_recommendations' => FertilizerRecommendation::count(),
        ];

        $recentDiseaseReports = CropDiseaseReport::with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.ai-modules.dashboard', compact('stats', 'recentDiseaseReports'));
    }

    // ── Crop Recommendations ───────────────────────────────────────────────

    public function cropRecommendations(Request $request)
    {
        $query = CropRecommendation::with('user:id,name,email')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $recommendations = $query->paginate(20);
        return view('admin.ai-modules.crop-recommendations', compact('recommendations'));
    }

    public function showCropRecommendation(int $id)
    {
        $recommendation = CropRecommendation::with('user', 'soilTest')->findOrFail($id);
        return view('admin.ai-modules.crop-recommendation-show', compact('recommendation'));
    }

    // ── Crop Plans ─────────────────────────────────────────────────────────

    public function cropPlans(Request $request)
    {
        $query = CropPlan::with('user:id,name,email')->latest();

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        $plans = $query->paginate(20);
        return view('admin.ai-modules.crop-plans', compact('plans'));
    }

    // ── Disease Reports ────────────────────────────────────────────────────

    public function diseaseReports(Request $request)
    {
        $query = CropDiseaseReport::with(['user:id,name,email', 'suggestion'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20);
        return view('admin.ai-modules.disease-reports', compact('reports'));
    }

    public function showDiseaseReport(int $id)
    {
        $report = CropDiseaseReport::with(['user', 'suggestion'])->findOrFail($id);
        return view('admin.ai-modules.disease-report-show', compact('report'));
    }

    /**
     * Expert override: assign disease manually and regenerate suggestion.
     */
    public function assignDisease(Request $request, int $id)
    {
        $request->validate([
            'disease' => 'required|string|max:200',
        ]);

        $report = CropDiseaseReport::findOrFail($id);
        $this->diseaseService->assignDisease($report, $request->disease, auth()->id());

        return redirect()->back()->with('success', 'Disease assigned and treatment suggestion updated.');
    }

    // ── Fertilizer Recommendations ─────────────────────────────────────────

    public function fertilizerRecommendations(Request $request)
    {
        $query = FertilizerRecommendation::with('user:id,name,email')->latest();

        if ($request->filled('crop_type')) {
            $query->where('crop_type', $request->crop_type);
        }

        $recommendations = $query->paginate(20);
        return view('admin.ai-modules.fertilizer-recommendations', compact('recommendations'));
    }

    // ── Seasonal Data CRUD ─────────────────────────────────────────────────

    public function seasonalData(Request $request)
    {
        $data = SeasonalData::when($request->filled('season'), fn($q) => $q->where('season', $request->season))
            ->paginate(20);
        return view('admin.ai-modules.seasonal-data', compact('data'));
    }

    public function storeSeasonalData(Request $request)
    {
        $validated = $request->validate([
            'season'                => 'required|in:Rabi,Kharif,Zaid',
            'crop_name'             => 'required|string|max:100',
            'region'                => 'nullable|string|max:100',
            'sowing_months'         => 'nullable|string|max:50',
            'harvesting_months'     => 'nullable|string|max:50',
            'water_requirement_mm'  => 'nullable|numeric|min:0',
            'soil_type_compatibility' => 'nullable|string|max:200',
            'min_temp_celsius'      => 'nullable|string|max:10',
            'max_temp_celsius'      => 'nullable|string|max:10',
            'avg_yield_tons_per_acre' => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ]);

        SeasonalData::create($validated);
        return redirect()->back()->with('success', 'Seasonal data added successfully.');
    }

    public function updateSeasonalData(Request $request, int $id)
    {
        $data = SeasonalData::findOrFail($id);
        $data->update($request->only([
            'sowing_months', 'harvesting_months', 'water_requirement_mm',
            'avg_yield_tons_per_acre', 'notes', 'is_active',
        ]));
        return redirect()->back()->with('success', 'Seasonal data updated.');
    }

    public function deleteSeasonalData(int $id)
    {
        SeasonalData::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Entry deleted.');
    }
}
