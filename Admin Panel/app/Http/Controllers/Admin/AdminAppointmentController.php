<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    // ── Listing ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Appointment::with(['user', 'expert.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('expert_id')) {
            $query->where('expert_id', $request->expert_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term))
                  ->orWhereHas('expert.user', fn ($u) => $u->where('name', 'like', $term));
            });
        }

        $appointments = $query->paginate(20)->withQueryString();
        $experts      = Expert::with('user')->available()->get();
        $statuses     = array_keys(Appointment::TRANSITIONS);

        return view('admin.appointments.index', compact('appointments', 'experts', 'statuses'));
    }

    public function show(int $id): View
    {
        $appointment = Appointment::with(['user', 'expert.user', 'statusHistory', 'logs', 'reschedules'])
            ->findOrFail($id);
        $experts = Expert::with('user')->available()->get();

        return view('admin.appointments.show', compact('appointment', 'experts'));
    }

    // ── Status transitions ────────────────────────────────────────────────────

    /**
     * Admin force-confirms an appointment (bypasses state machine's normal path).
     */
    public function confirm(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'expert_id' => 'nullable|exists:experts,id',
            'notes'     => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::findOrFail($id);

        try {
            $this->service->confirm(
                $appointment,
                $request->expert_id,
                $request->notes,
                true,
                $request->user()->id
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $appointment = Appointment::findOrFail($id);

        $this->service->cancel($appointment, $request->reason, true, $request->user()->id);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        try {
            $this->service->complete($appointment, $request->user()->id);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Appointment marked as completed.');
    }

    // ── Stripe refund ─────────────────────────────────────────────────────────

    /**
     * Issue a full or partial Stripe refund from the admin panel.
     */
    public function refund(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'refund_type' => 'required|in:full,partial',
            'amount'      => 'required_if:refund_type,partial|nullable|numeric|min:0.01|max:' . ($appointment->fee ?? 0),
            'reason'      => 'required|string|max:500',
        ]);

        try {
            $this->service->adminRefund(
                $appointment,
                $request->refund_type === 'full' ? null : (float) $request->amount,
                $request->reason,
                $request->user()->id
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->withErrors(['error' => 'Stripe refund failed: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Refund issued successfully.');
    }

    // ── Reassign expert ───────────────────────────────────────────────────────

    public function reassign(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'expert_id' => 'required|exists:experts,id',
            'reason'    => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);

        try {
            $this->service->reassignExpert(
                $appointment,
                (int) $request->expert_id,
                $request->user()->id,
                $request->reason
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Expert reassigned.');
    }
}

