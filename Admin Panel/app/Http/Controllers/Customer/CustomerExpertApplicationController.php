<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SubmitExpertApplicationRequest;
use App\Services\Expert\ExpertApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CustomerExpertApplicationController
 *
 * Allows authenticated customers to:
 *   - View the application form
 *   - Submit an application to become an expert
 *   - Track their application status
 */
class CustomerExpertApplicationController extends Controller
{
    public function __construct(
        private readonly ExpertApplicationService $applicationService
    ) {}

    /**
     * GET /customer/expert-application
     * Show application form (or redirect if already applied / already an expert).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Already an expert
        if ($user->role === 'expert') {
            return redirect()->route('customer.dashboard')
                ->with('info', 'You are already registered as an expert.');
        }

        // Has an active application — redirect to status
        $application = $this->applicationService->getLatestApplicationForUser($user->id);

        if ($application && ! $application->isRejected()) {
            return redirect()->route('customer.expert-application.status')
                ->with('info', 'Your application is already under review.');
        }

        // Show form (may pre-fill from last rejected application)
        return view('customer.expert-application.create', compact('application'));
    }

    /**
     * POST /customer/expert-application
     * Store a new application.
     */
    public function store(SubmitExpertApplicationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Policy check: can user apply?
        $this->authorize('apply', \App\Models\ExpertApplication::class);

        try {
            $this->applicationService->submit($user, $request->validated() + [
                'certifications_file' => $request->file('certifications_file'),
                'id_document_file'    => $request->file('id_document_file'),
            ]);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('customer.expert-application.status')
            ->with('success', 'Your application has been submitted and is pending review. We will notify you by email.');
    }

    /**
     * GET /customer/expert-application/status
     * Show application status tracker.
     */
    public function status(Request $request): View|RedirectResponse
    {
        $user        = $request->user();
        $application = $this->applicationService->getLatestApplicationForUser($user->id);

        if (! $application) {
            return redirect()->route('customer.expert-application.create')
                ->with('info', 'You have not submitted an application yet.');
        }

        return view('customer.expert-application.status', compact('application'));
    }
}
