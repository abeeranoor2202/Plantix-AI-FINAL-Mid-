<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiseaseReportRequest;
use App\Jobs\ProcessDiseaseDetection;
use App\Models\CropDiseaseReport;
use App\Services\Customer\DiseaseDetectionService;
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

        return view('customer.disease-identification', compact('reports'));
    }

    /**
     * Process an uploaded crop image for disease detection.
     *
     * Creates a pending report immediately and dispatches a queued job for
     * heavy ML inference, so the user gets an instant response.
     */
    public function detect(DiseaseReportRequest $request)
    {
        $user = Auth::user();

        // 1. Store image + create pending report (fast, no ML call)
        $report = $this->service->createPendingReport(
            $user,
            $request->file('image'),
            [
                'crop_name'        => $request->input('crop_name'),
                'user_description' => $request->input('description'),
            ]
        );

        // 2. Dispatch async job for inference
        ProcessDiseaseDetection::dispatch($report);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'               => $report->id,
                    'detected_disease' => null,
                    'confidence_score' => null,
                    'confidence_pct'   => null,
                    'all_predictions'  => null,
                    'image_url'        => $report->image_url,
                    'status'           => $report->status, // 'pending'
                    'suggestion'       => null,
                ],
                'message' => 'Your image has been submitted for analysis. Results will be ready shortly.',
            ]);
        }

        return redirect()->back()->with('info', 'Your image has been submitted for analysis. Please check back shortly for results.');
    }

    /**
     * Poll the status of a pending disease detection report (AJAX).
     * Called repeatedly by the frontend after submitting an image
     * until status transitions from 'pending' to 'processed', 'invalid_image',
     * 'manual_review', or 'failed'.
     */
    public function pollStatus(int $id)
    {
        $report = CropDiseaseReport::where('user_id', Auth::id())
            ->findOrFail($id);

        $data = [
            'id'               => $report->id,
            'status'           => $report->status,
            'detected_disease' => $report->detected_disease,
            'confidence_score' => $report->confidence_score,
            'confidence_pct'   => $report->confidence_score !== null
                                    ? round((float) $report->confidence_score * 100, 1)
                                    : null,
            'all_predictions'  => null,
            'suggestion'       => null,
            // Confidence gate fields
            'is_valid_image'   => $report->status !== 'invalid_image',
            'invalid_message'  => $report->status === 'invalid_image'
                ? 'This image does not appear to be a plant leaf. Please upload a clear image of a plant for disease identification.'
                : null,
        ];

        if ($report->status === 'processed') {
            $report->load('suggestion');
            $data['all_predictions'] = $report->all_predictions;
            $data['suggestion']      = $report->suggestion;
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * View a specific disease report.
     */
    public function show(int $id)
    {
        $report = CropDiseaseReport::where('user_id', Auth::id())
            ->with('suggestion')
            ->findOrFail($id);

        return view('customer.disease-identification', compact('report'));
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


