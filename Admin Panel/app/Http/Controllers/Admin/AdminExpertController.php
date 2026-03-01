<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\ExpertApplication;
use App\Services\Expert\ExpertApprovalService;
use App\Services\Expert\ExpertApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AdminExpertController
 *
 * Full lifecycle management: list, review applications, all 6 transitions, CSV export, logs.
 */
class AdminExpertController extends Controller
{
    public function __construct(
        private readonly ExpertApprovalService    $approvalService,
        private readonly ExpertApplicationService $applicationService,
    ) {}

    // ── Expert list ──────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Expert::with(['user', 'profile'])->withCount('appointments');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($type = $request->input('type')) {
            $query->whereHas('profile', fn ($q) => $q->where('account_type', $type));
        }

        $experts = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'        => Expert::count(),
            'pending'      => Expert::pending()->count(),
            'under_review' => Expert::underReview()->count(),
            'approved'     => Expert::approved()->count(),
            'rejected'     => Expert::rejected()->count(),
            'suspended'    => Expert::suspended()->count(),
            'inactive'     => Expert::inactive()->count(),
        ];

        return view('admin.experts.index', compact('experts', 'stats'));
    }

    public function show(int $id): View
    {
        $expert = Expert::with(['user', 'profile', 'specializations', 'logs.actor'])
            ->withCount('appointments')
            ->findOrFail($id);

        return view('admin.experts.show', compact('expert'));
    }

    // ── Lifecycle transitions ────────────────────────────────────────────────

    public function markUnderReview(Request $request, int $id): RedirectResponse
    {
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->markUnderReview($expert, $request->user()->id, $request->input('notes', ''), $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} moved to Under Review.");
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->approve($expert, $request->user()->id, $request->input('notes', ''), $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} approved.");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->reject($expert, $request->input('reason'), $request->user()->id, $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} rejected.");
    }

    public function suspend(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->suspend($expert, $request->input('reason'), $request->user()->id, $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} suspended.");
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->restore($expert, $request->user()->id, $request->input('notes', ''), $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} restored to active.");
    }

    public function deactivate(Request $request, int $id): RedirectResponse
    {
        $expert = Expert::findOrFail($id);
        try {
            $this->approvalService->deactivate($expert, $request->user()->id, $request->input('notes', ''), $request);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Expert #{$expert->id} deactivated.");
    }

    public function toggleAvailability(int $id): RedirectResponse
    {
        $expert = Expert::findOrFail($id);
        $expert->update(['is_available' => ! $expert->is_available]);
        $state = $expert->is_available ? 'available' : 'unavailable';
        return back()->with('success', "Expert #{$expert->id} is now {$state}.");
    }

    // ── Audit log ────────────────────────────────────────────────────────────

    public function logs(int $id): View
    {
        $expert = Expert::with('user')->findOrFail($id);
        $logs   = $expert->logs()->with('actor')->paginate(30);
        return view('admin.experts.logs', compact('expert', 'logs'));
    }

    // ── Applications queue ───────────────────────────────────────────────────

    public function applications(Request $request): View
    {
        $query = ExpertApplication::with(['user', 'reviewer'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            $query->needsReview();
        }

        $applications = $query->paginate(25)->withQueryString();

        $stats = [
            'pending'      => ExpertApplication::pending()->count(),
            'under_review' => ExpertApplication::underReview()->count(),
            'approved'     => ExpertApplication::approved()->count(),
            'rejected'     => ExpertApplication::rejected()->count(),
        ];

        return view('admin.experts.applications', compact('applications', 'stats'));
    }

    public function applicationUnderReview(int $id, Request $request): RedirectResponse
    {
        $application = ExpertApplication::findOrFail($id);
        $this->applicationService->markUnderReview($application, $request->user());
        return back()->with('success', "Application #{$id} marked as under review.");
    }

    public function applicationApprove(int $id, Request $request): RedirectResponse
    {
        $application = ExpertApplication::findOrFail($id);
        $this->authorize('approve', $application);
        $expert = $this->applicationService->approve($application, $request->user(), $request->input('notes'));
        return redirect()->route('admin.experts.show', $expert->id)
            ->with('success', "Application approved. Expert #{$expert->id} created.");
    }

    public function applicationReject(int $id, Request $request): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $application = ExpertApplication::findOrFail($id);
        $this->authorize('reject', $application);
        $this->applicationService->reject($application, $request->user(), $request->input('reason'));
        return back()->with('success', "Application #{$id} rejected.");
    }

    // ── CSV Export ───────────────────────────────────────────────────────────

    public function export(Request $request): StreamedResponse
    {
        $status  = $request->input('status');
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="experts_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($status) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Specialty', 'Status', 'Rating', 'Total Appointments', 'Total Completed', 'Is Available', 'Verified At', 'Suspended At', 'Created At']);
            Expert::with('user')
                ->when($status, fn ($q) => $q->where('status', $status))
                ->orderBy('id')
                ->chunk(500, function ($experts) use ($handle) {
                    foreach ($experts as $expert) {
                        fputcsv($handle, [
                            $expert->id,
                            $expert->user?->name,
                            $expert->user?->email,
                            $expert->specialty,
                            $expert->status,
                            $expert->rating_avg,
                            $expert->total_appointments,
                            $expert->total_completed,
                            $expert->is_available ? 'Yes' : 'No',
                            $expert->verified_at?->format('Y-m-d'),
                            $expert->suspended_at?->format('Y-m-d'),
                            $expert->created_at->format('Y-m-d'),
                        ]);
                    }
                });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
