<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CropPlanRequest;
use App\Models\CropPlan;
use App\Models\FarmProfile;
use App\Models\SeasonalData;
use App\Services\CropPlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropPlanningController extends Controller
{
    public function __construct(private CropPlanningService $service) {}

    /**
     * Show crop planning page.
     */
    public function index(Request $request)
    {
        $plans      = collect();
        $farmProfiles = collect();
        $seasonalCrops = SeasonalData::active()->distinct()->pluck('crop_name')->sort()->values();

        if (Auth::check()) {
            $plans = CropPlan::where('user_id', Auth::id())
                ->latest()
                ->take(10)
                ->get();

            $farmProfiles = FarmProfile::where('user_id', Auth::id())->get();
        }

        return view('pages.crop-planning', compact('plans', 'farmProfiles', 'seasonalCrops'));
    }

    /**
     * Generate a new crop plan.
     */
    public function generate(CropPlanRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $farmProfileId = null;
        if ($request->filled('farm_profile_id') && $user) {
            $farmProfileId = FarmProfile::where('id', $data['farm_profile_id'])
                ->where('user_id', $user->id)
                ->value('id');
        }

        $plan = $this->service->generate($user, $data, $farmProfileId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                     => $plan->id,
                    'primary_crop'           => $plan->primary_crop,
                    'season'                 => $plan->season,
                    'crop_schedule'          => $plan->crop_schedule,
                    'water_plan'             => $plan->water_plan,
                    'expected_yield_tons'    => $plan->expected_yield_tons,
                    'estimated_revenue'      => $plan->estimated_revenue,
                    'soil_suitability_notes' => $plan->soil_suitability_notes,
                    'recommendations'        => $plan->recommendations,
                ],
            ]);
        }

        return redirect()->back()->with([
            'plan'    => $plan,
            'success' => 'Crop plan generated successfully!',
        ]);
    }

    /**
     * View a specific crop plan.
     */
    public function show(int $id)
    {
        $plan = CropPlan::where('user_id', Auth::id())->findOrFail($id);
        $seasonalCrops = SeasonalData::active()->distinct()->pluck('crop_name')->sort()->values();

        return view('pages.crop-planning', compact('plan', 'seasonalCrops'));
    }

    /**
     * Update a plan's status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:draft,active,completed,archived']);

        $plan = CropPlan::where('user_id', Auth::id())->findOrFail($id);
        $plan->update(['status' => $request->status]);

        return response()->json(['success' => true, 'status' => $plan->status]);
    }

    /**
     * Delete a crop plan.
     */
    public function destroy(int $id)
    {
        CropPlan::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
