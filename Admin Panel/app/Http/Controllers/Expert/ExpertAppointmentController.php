<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\AcceptAppointmentRequest;
use App\Http\Requests\Expert\RejectAppointmentRequest;
use App\Http\Requests\Expert\RescheduleAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Validation\Rule;
use App\Services\Expert\ExpertAppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ExpertAppointmentController
 *
 * Thin controller delegating all business logic to ExpertAppointmentService.
 * Handles the full appointment lifecycle: view, accept, reject, reschedule, complete.
 */
class ExpertAppointmentController extends Controller
{
    public function __construct(
        private readonly ExpertAppointmentService $service
    ) {}

    private function currentExpert(): \App\Models\Expert
    {
        return auth('expert')->user()->expert;
    }

    // ── Listing ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $expert       = $this->currentExpert();
        $filters      = $request->only(['search', 'status', 'date_from', 'date_to']);
        $appointments = $this->service->listForExpert($expert, $filters);
        $stats        = $this->service->getStats($expert);

        return view('expert.appointments.index', compact('appointments', 'stats', 'filters'));
    }

    public function show(Appointment $appointment): View
    {
        $expert = $this->currentExpert();
        abort_unless((int) $appointment->expert_id === (int) $expert->id, 403);

        $appointment->load(['user', 'expert.profile', 'statusHistory.changedBy', 'reschedules.requestedBy']);

        return view('expert.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        $expert = $this->currentExpert();
        abort_unless((int) $appointment->expert_id === (int) $expert->id, 403);

        if (in_array($appointment->status, [
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_REJECTED,
        ], true)) {
            return abort(403, 'This appointment can no longer be edited.');
        }

        return view('expert.appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $expert = $this->currentExpert();
        abort_unless((int) $appointment->expert_id === (int) $expert->id, 403);

        if (in_array($appointment->status, [
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_REJECTED,
        ], true)) {
            return back()->with('error', 'This appointment can no longer be edited.');
        }

        $data = $request->validate([
            'scheduled_at'     => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'topic'            => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:2000',
            'meeting_link'     => [
                Rule::requiredIf(fn () => $appointment->isOnline()),
                'nullable',
                'url',
                'max:500',
            ],
            'location'         => [
                Rule::requiredIf(fn () => $appointment->isPhysical()),
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'meeting_link.required'    => 'Enter a valid meeting URL',
            'meeting_link.url'         => 'Enter a valid meeting URL',
            'scheduled_at.after'       => 'Select a future date and time.',
            'duration_minutes.min'     => 'Duration must be greater than 0.',
            'duration_minutes.integer' => 'Duration must be greater than 0.',
        ]);

        $appointment->update([
            'scheduled_at'     => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'],
            'topic'            => $data['topic'] ?? $appointment->topic,
            'notes'            => $data['notes'] ?? $appointment->notes,
            'meeting_link'     => $appointment->isOnline() ? ($data['meeting_link'] ?? $appointment->meeting_link) : null,
            'location'         => $appointment->isPhysical() ? ($data['location'] ?? $appointment->location) : null,
        ]);

        return redirect()->route('expert.appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $expert = $this->currentExpert();
        abort_unless((int) $appointment->expert_id === (int) $expert->id, 403);

        if (! $appointment->canBeAccepted()) {
            return back()->with('error', 'Only pending appointments can be deleted.');
        }

        $appointment->delete();

        return redirect()->route('expert.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function accept(AcceptAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        try {
            $this->service->accept(
                $appointment,
                $this->currentExpert(),
                $request->validated('meeting_link')
            );

            return redirect()->route('expert.appointments.show', $appointment)
                ->with('success', 'Appointment accepted. The farmer has been notified.');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(RejectAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        try {
            $this->service->reject(
                $appointment,
                $this->currentExpert(),
                $request->input('reason')
            );

            return redirect()->route('expert.appointments.index')
                ->with('success', 'Appointment rejected.');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        try {
            $this->service->complete(
                $appointment,
                $this->currentExpert(),
                $request->input('notes')
            );

            return redirect()->route('expert.appointments.show', $appointment)
                ->with('success', 'Appointment marked as completed.');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        try {
            $this->service->requestReschedule(
                $appointment,
                $this->currentExpert(),
                new \DateTime($request->input('proposed_datetime')),
                $request->input('reason')
            );

            return redirect()->route('expert.appointments.show', $appointment)
                ->with('success', 'Reschedule request sent. Waiting for farmer confirmation.');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
