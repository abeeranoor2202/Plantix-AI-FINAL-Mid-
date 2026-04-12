<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminAppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    // ── Listing ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Appointment::with(['user', 'expert.user', 'expert.profile'])->latest();

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
        $appointment = Appointment::with(['user', 'expert.user', 'expert.profile', 'statusHistory', 'logs', 'reschedules'])
            ->findOrFail($id);
        $experts = Expert::with('user')->available()->get();

        return view('admin.appointments.show', compact('appointment', 'experts'));
    }

    // ── Edit functionality ────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $appointment = Appointment::with(['user', 'expert.user', 'expert.profile'])
            ->findOrFail($id);
        $experts = Expert::with('user')->available()->get();

        return view('admin.appointments.edit', compact('appointment', 'experts'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'expert_id'     => 'nullable|exists:experts,id',
            'scheduled_at'  => 'nullable|date_format:Y-m-d H:i',
            'fee'           => 'nullable|numeric|min:0',
            'topic'         => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:1000',
            'meeting_link'  => ($appointment->type === 'online' ? 'required' : 'nullable') . '|url|max:500',
            'location'      => ($appointment->type === 'physical' ? 'required' : 'nullable') . '|string|max:500',
        ]);

        $appointment->update($request->only([
            'expert_id', 'scheduled_at', 'fee', 'topic', 'notes', 'meeting_link', 'location'
        ]));

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully.');
    }

    // ── Delete functionality ──────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        // Prevent deletion of completed appointments
        if ($appointment->status === Appointment::STATUS_COMPLETED) {
            return back()->withErrors(['error' => 'Cannot delete completed appointments. Please refund and cancel instead.']);
        }

        $appointment->delete();

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    // ── Status transitions ────────────────────────────────────────────────────

    /**
     * Admin force-confirms an appointment (bypasses state machine's normal path).
     */
    public function confirm(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'expert_id' => 'nullable|exists:experts,id',
            'meeting_link' => ($appointment->type === 'online' ? 'required' : 'nullable') . '|url|max:500',
            'notes'     => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->confirm(
                $appointment,
                $request->expert_id,
                $request->notes,
                true,
                Auth::id(),
                $request->input('meeting_link')
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
        $userId = Auth::id();

        $this->service->cancel($appointment, $request->reason, true, $userId);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);
        $userId = Auth::id();

        try {
            $this->service->complete($appointment, $userId);
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
                Auth::id()
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
                Auth::id(),
                $request->reason
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Expert reassigned.');
    }
}

