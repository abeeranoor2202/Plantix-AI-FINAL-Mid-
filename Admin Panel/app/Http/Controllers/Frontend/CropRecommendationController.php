<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CropRecommendationRequest;
use App\Models\CropRecommendation;
use App\Services\Customer\CropRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropRecommendationController extends Controller
{
    public function __construct(private CropRecommendationService $service) {}

    /**
     * Show the crop recommendation form + history.
     */
    public function index(Request $request)
    {
        $history = Auth::check()
            ? CropRecommendation::where('user_id', Auth::id())
                ->latest()
                ->take(10)
                ->get()
            : collect();

        return view('customer.crop-recommendation', compact('history'));
    }

    /**
     * Process the recommendation form and return results.
     */
    public function recommend(CropRecommendationRequest $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $recommendation = $this->service->recommend($user, $request->validated());
        $topCrop = $recommendation->recommended_crops[0] ?? [];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id' => $recommendation->id,
                    'crop' => (string) ($topCrop['crop'] ?? $recommendation->top_crop ?? ''),
                    'confidence' => isset($topCrop['confidence_score']) ? (float) $topCrop['confidence_score'] : null,
                    'confidence_percent' => (float) ($topCrop['confidence'] ?? 0),
                    'request_id' => $topCrop['request_id'] ?? null,
                    'record_id' => $topCrop['record_id'] ?? null,
                    'recommended_crops' => $recommendation->recommended_crops,
                    'explanation' => $recommendation->explanation,
                    'top_crop' => $recommendation->top_crop,
                ],
            ]);
        }

        return redirect()->back()->with([
            'recommendation' => $recommendation,
            'success'        => 'Crop recommendation generated successfully!',
        ]);
    }

    /**
     * View a specific historical recommendation.
     */
    public function show(int $id)
    {
        $recommendation = CropRecommendation::where('user_id', Auth::id())
            ->findOrFail($id);

        return view('customer.crop-recommendation', compact('recommendation'))->with([
            'viewing_history' => true,
        ]);
    }

    /**
     * Return recommendation history as JSON (AJAX).
     */
    public function history(Request $request)
    {
        $history = CropRecommendation::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get(['id', 'nitrogen', 'phosphorus', 'potassium', 'ph_level', 'recommended_crops', 'created_at']);

        return response()->json(['success' => true, 'data' => $history]);
    }
}

