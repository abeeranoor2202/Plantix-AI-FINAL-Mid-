<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FertilizerRecommendationRequest;
use App\Models\FertilizerRecommendation;
use App\Models\SoilTest;
use App\Services\Customer\FertilizerRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FertilizerRecommendationController extends Controller
{
    public function __construct(private FertilizerRecommendationService $service) {}

    /**
     * Show fertilizer recommendation page.
     */
    public function index(Request $request)
    {
        $recommendations = collect();
        $soilTests       = collect();

        if (Auth::check()) {
            $recommendations = FertilizerRecommendation::where('user_id', Auth::id())
                ->latest()
                ->take(10)
                ->get();

            $soilTests = SoilTest::where('user_id', Auth::id())
                ->latest()
                ->take(5)
                ->get(['id', 'created_at', 'nitrogen', 'phosphorus', 'potassium', 'ph_level']);
        }

        return view('customer.fertilizer-recommendation', compact('recommendations', 'soilTests'));
    }

    /**
     * Generate fertilizer recommendation.
     */
    public function recommend(FertilizerRecommendationRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        // If a soil test ID is provided, merge that data
        $soilTestId = null;
        if ($request->filled('soil_test_id') && $user) {
            $soilTest = SoilTest::where('id', $data['soil_test_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($soilTest) {
                $soilTestId = $soilTest->id;
                // Merge soil test data (form values override if provided)
                $data = array_merge([
                    'nitrogen'   => $soilTest->nitrogen,
                    'phosphorus' => $soilTest->phosphorus,
                    'potassium'  => $soilTest->potassium,
                ], $data);
            }
        }

        $recommendation = $this->service->recommend($user, $data, $soilTestId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                         => $recommendation->id,
                    'fertilizer_plan'            => $recommendation->fertilizer_plan,
                    'application_instructions'   => $recommendation->application_instructions,
                    'estimated_cost_pkr'         => $recommendation->estimated_cost_pkr,
                ],
            ]);
        }

        return redirect()->back()->with([
            'recommendation' => $recommendation,
            'success'        => 'Fertilizer plan generated successfully!',
        ]);
    }

    /**
     * View a specific recommendation.
     */
    public function show(int $id)
    {
        $recommendation = FertilizerRecommendation::where('user_id', Auth::id())->findOrFail($id);
        $soilTests = collect();
        return view('customer.fertilizer-recommendation', compact('recommendation', 'soilTests'));
    }

    /**
     * History (AJAX).
     */
    public function history(Request $request)
    {
        $list = FertilizerRecommendation::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get(['id', 'estimated_cost_pkr', 'created_at']);

        return response()->json(['success' => true, 'data' => $list]);
    }
}

