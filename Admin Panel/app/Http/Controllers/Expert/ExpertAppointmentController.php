<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\AcceptAppointmentRequest;
use App\Http\Requests\Expert\RejectAppointmentRequest;
use App\Http\Requests\Expert\RescheduleAppointmentRequest;
use App\Models\Appointment;
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
        $filters      = $request->only(['status', 'date_from', 'date_to']);
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
