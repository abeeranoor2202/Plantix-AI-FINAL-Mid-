<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiseaseReportRequest;
use App\Models\CropDiseaseReport;
use App\Services\DiseaseDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiseaseIdentificationController extends Controller
{
    public function __construct(private DiseaseDetectionService $service) {}

    /**
     * Show disease identification page.
     */
    public function index(Request $request)
    {
        $reports = collect();

        if (Auth::check()) {
            $reports = CropDiseaseReport::where('user_id', Auth::id())
                ->with('suggestion')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('pages.disease-identification', compact('reports'));
    }

    /**
     * Process an uploaded crop image for disease detection.
     */
    public function detect(DiseaseReportRequest $request)
    {
        $user = Auth::user();

        $report = $this->service->detect($user, $request->file('image'), [
            'crop_name'        => $request->input('crop_name'),
            'user_description' => $request->input('description'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'               => $report->id,
                    'detected_disease' => $report->detected_disease,
                    'confidence_score' => $report->confidence_score,
                    'confidence_pct'   => $report->confidence_percent,
                    'all_predictions'  => $report->all_predictions,
                    'image_url'        => $report->image_url,
                    'status'           => $report->status,
                    'suggestion'       => $report->suggestion ? [
                        'disease_name'        => $report->suggestion->disease_name,
                        'description'         => $report->suggestion->description,
                        'organic_treatment'   => $report->suggestion->organic_treatment,
                        'chemical_treatment'  => $report->suggestion->chemical_treatment,
                        'preventive_measures' => $report->suggestion->preventive_measures,
                    ] : null,
                ],
            ]);
        }

        return redirect()->back()->with([
            'report'  => $report,
            'success' => 'Disease analysis completed!',
        ]);
    }

    /**
     * View a specific disease report.
     */
    public function show(int $id)
    {
        $report = CropDiseaseReport::where('user_id', Auth::id())
            ->with('suggestion')
            ->findOrFail($id);

        return view('pages.disease-identification', compact('report'));
    }

    /**
     * Get detection history (AJAX).
     */
    public function history(Request $request)
    {
        $reports = CropDiseaseReport::where('user_id', Auth::id())
            ->with('suggestion:id,report_id,disease_name')
            ->latest()
            ->take(20)
            ->get(['id', 'crop_name', 'detected_disease', 'confidence_score', 'status', 'image_path', 'created_at']);

        return response()->json(['success' => true, 'data' => $reports]);
    }
}
