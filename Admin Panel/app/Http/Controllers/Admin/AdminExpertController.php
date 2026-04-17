<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Expert;
use App\Models\ExpertProfile;
use App\Models\ExpertApplication;
use App\Services\Expert\ExpertApprovalService;
use App\Services\Expert\ExpertApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (($activity = $request->input('activity')) && Schema::hasColumn('users', 'last_login_at')) {
            if ($activity === 'active_7d') {
                $query->whereHas('user', fn ($q) => $q->whereNotNull('last_login_at')->where('last_login_at', '>=', now()->subDays(7)));
            } elseif ($activity === 'active_30d') {
                $query->whereHas('user', fn ($q) => $q->whereNotNull('last_login_at')->where('last_login_at', '>=', now()->subDays(30)));
            } elseif ($activity === 'inactive_30d') {
                $query->whereHas('user', fn ($q) => $q
                    ->where(function ($nested) {
                        $nested->whereNull('last_login_at')
                            ->orWhere('last_login_at', '<', now()->subDays(30));
                    })
                );
            }
        }

        $experts = $query->latest()->paginate(25)->withQueryString();

        $statuses = ['pending', 'under_review', 'approved', 'rejected', 'suspended', 'inactive'];

        $stats = [
            'total'        => Expert::count(),
            'pending'      => Expert::pending()->count(),
            'under_review' => Expert::underReview()->count(),
            'approved'     => Expert::approved()->count(),
            'rejected'     => Expert::rejected()->count(),
            'suspended'    => Expert::suspended()->count(),
            'inactive'     => Expert::inactive()->count(),
        ];

        return view('admin.experts.index', compact('experts', 'stats', 'statuses'));
    }

    public function create(): View
    {
        return view('admin.experts.form', [
            'expert' => new Expert([
                'status' => Expert::STATUS_PENDING,
                'is_available' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'      => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'specialty'  => ['nullable', 'string', 'max:255'],
            'bio'        => ['nullable', 'string'],
            'hourly_rate'=> ['nullable', 'numeric', 'min:0'],
            'account_type' => ['required', 'in:individual,agency'],
            'agency_name'   => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'city'       => ['nullable', 'string', 'max:255'],
            'country'    => ['nullable', 'string', 'max:100'],
            'consultation_price' => ['nullable', 'numeric', 'min:0'],
            'website'    => ['nullable', 'url', 'max:255'],
            'linkedin'   => ['nullable', 'url', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'certifications' => ['nullable', 'string'],
            'profile_image'  => ['nullable', 'image', 'max:2048'],
        ]);

        $profileImagePath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('experts', 'public')
            : null;

        DB::transaction(function () use ($validated, $profileImagePath) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role'     => 'expert',
                'active'   => true,
            ]);

            $expert = Expert::create([
                'user_id'                      => $user->id,
                'status'                       => Expert::STATUS_APPROVED,
                'specialty'                    => $validated['specialty'] ?? null,
                'bio'                          => $validated['bio'] ?? null,
                'profile_image'                => $profileImagePath,
                'is_available'                 => true,
                'hourly_rate'                  => $validated['hourly_rate'] ?? 0,
                'consultation_price'           => $validated['consultation_price'] ?? null,
                'consultation_duration_minutes'=> 60,
            ]);

            if ($expert) {
                ExpertProfile::create([
                    'expert_id'        => $expert->id,
                    'agency_name'      => $validated['account_type'] === 'agency' ? ($validated['agency_name'] ?? null) : null,
                    'specialization'   => $validated['specialty'] ?? null,
                    'experience_years' => $validated['experience_years'] ?? 0,
                    'certifications'   => $validated['certifications'] ?? null,
                    'website'          => $validated['website'] ?? null,
                    'linkedin'         => $validated['linkedin'] ?? null,
                    'contact_phone'    => $validated['contact_phone'] ?? null,
                    'city'             => $validated['city'] ?? null,
                    'country'          => $validated['country'] ?? 'Pakistan',
                    'account_type'     => $validated['account_type'],
                    'approval_status'  => Expert::STATUS_APPROVED,
                ]);
            }
        });

        return redirect()->route('admin.experts.index')->with('success', 'Expert created successfully.');
    }

    public function edit(int $id): View
    {
        $expert = Expert::with(['user', 'profile'])->findOrFail($id);
        return view('admin.experts.form', compact('expert'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $expert = Expert::with(['user', 'profile'])->findOrFail($id);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email,' . $expert->user_id],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'specialty'             => ['nullable', 'string', 'max:255'],
            'bio'                   => ['nullable', 'string'],
            'hourly_rate'           => ['nullable', 'numeric', 'min:0'],
            'consultation_price'    => ['nullable', 'numeric', 'min:0'],
            'consultation_duration_minutes' => ['nullable', 'integer', 'min:15'],
            'approval_status'       => ['nullable', 'in:pending,under_review,approved,suspended,rejected,inactive'],
            'is_available'          => ['nullable', 'boolean'],
            'account_type'          => ['nullable', 'in:individual,agency'],
            'agency_name'           => ['nullable', 'string', 'max:255'],
            'experience_years'      => ['nullable', 'integer', 'min:0', 'max:60'],
            'city'                  => ['nullable', 'string', 'max:255'],
            'country'               => ['nullable', 'string', 'max:100'],
            'website'               => ['nullable', 'url', 'max:255'],
            'linkedin'              => ['nullable', 'url', 'max:255'],
            'contact_phone'         => ['nullable', 'string', 'max:30'],
            'certifications'        => ['nullable', 'string'],
            'profile_image'         => ['nullable', 'image', 'max:2048'],
        ]);

        $profileImagePath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('experts', 'public')
            : null;

        DB::transaction(function () use ($expert, $validated, $request, $profileImagePath) {
            $expert->user?->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $expert->user?->phone,
            ]);

            $expert->update([
                'specialty'                    => $validated['specialty'] ?? $expert->specialty,
                'bio'                          => $validated['bio'] ?? $expert->bio,
                'profile_image'                => $profileImagePath ?? $expert->profile_image,
                'hourly_rate'                  => $validated['hourly_rate'] ?? $expert->hourly_rate,
                'consultation_price'           => $validated['consultation_price'] ?? $expert->consultation_price,
                'consultation_duration_minutes' => $validated['consultation_duration_minutes'] ?? $expert->consultation_duration_minutes,
                'status'                       => $validated['approval_status'] ?? $expert->status,
                'is_available'                 => $request->boolean('is_available', $expert->is_available),
            ]);

            if ($expert->profile) {
                $expert->profile->update([
                    'agency_name'      => $validated['account_type'] === 'agency' ? ($validated['agency_name'] ?? null) : null,
                    'specialization'   => $validated['specialty'] ?? $expert->profile->specialization,
                    'experience_years' => $validated['experience_years'] ?? $expert->profile->experience_years,
                    'certifications'   => $validated['certifications'] ?? $expert->profile->certifications,
                    'website'          => $validated['website'] ?? $expert->profile->website,
                    'linkedin'         => $validated['linkedin'] ?? $expert->profile->linkedin,
                    'contact_phone'    => $validated['contact_phone'] ?? $expert->profile->contact_phone,
                    'city'             => $validated['city'] ?? $expert->profile->city,
                    'country'          => $validated['country'] ?? $expert->profile->country,
                    'account_type'     => $validated['account_type'] ?? $expert->profile->account_type,
                    'approval_status'  => $validated['approval_status'] ?? $expert->profile->approval_status,
                    'admin_notes'      => $request->input('admin_notes', $expert->profile->admin_notes),
                ]);
            } else {
                ExpertProfile::create([
                    'expert_id'        => $expert->id,
                    'agency_name'      => $validated['account_type'] === 'agency' ? ($validated['agency_name'] ?? null) : null,
                    'specialization'   => $validated['specialty'] ?? null,
                    'experience_years' => $validated['experience_years'] ?? 0,
                    'certifications'   => $validated['certifications'] ?? null,
                    'website'          => $validated['website'] ?? null,
                    'linkedin'         => $validated['linkedin'] ?? null,
                    'contact_phone'    => $validated['contact_phone'] ?? null,
                    'city'             => $validated['city'] ?? null,
                    'country'          => $validated['country'] ?? 'Pakistan',
                    'account_type'     => $validated['account_type'] ?? 'individual',
                    'approval_status'  => $validated['approval_status'] ?? ($expert->status ?? Expert::STATUS_PENDING),
                    'admin_notes'      => $request->input('admin_notes'),
                ]);
            }
        });

        return back()->with('success', 'Expert updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $expert = Expert::with('user')->findOrFail($id);
        $expert->delete();
        return redirect()->route('admin.experts.index')->with('success', 'Expert archived successfully.');
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
