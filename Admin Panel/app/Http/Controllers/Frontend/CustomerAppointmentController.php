<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\Expert;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CustomerAppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    public function index(): View
    {
        $user         = auth('web')->user();
        $appointments = $user->appointments()->with('expert.user')->latest()->paginate(10);

        return view('customer.appointments', compact('appointments'));
    }

    public function create(): View
    {
        $experts = Expert::with('user')->available()->get();
        return view('customer.appointment-book', compact('experts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'expert_id'    => 'nullable|exists:experts,id',
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string|max:500',
        ]);

        $user = auth('web')->user();
        $this->service->book($user, $request->validated());

        return redirect()->route('appointments')
                         ->with('success', 'Appointment booked! You will receive a confirmation email.');
    }

    public function show(int $id): View
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->with(['expert.user', 'reschedules' => fn ($q) => $q->where('status', 'pending')])
            ->findOrFail($id);

        return view('customer.appointment-details', compact('appointment'));
    }

    public function cancel(int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()->where('status', 'pending')->findOrFail($id);

        $this->service->cancel($appointment, 'Cancelled by customer.');

        return back()->with('success', 'Appointment cancelled.');
    }

    /**
     * Show the payment page for a pending_payment appointment.
     * GET /appointment/{id}/pay
     */
    public function payPage(int $id): \Illuminate\View\View|RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->with('expert.user')
            ->findOrFail($id);

        if ($appointment->status !== Appointment::STATUS_PENDING_PAYMENT) {
            return redirect()->route('appointment.details', $id)
                             ->with('error', 'This appointment does not require payment.');
        }

        return view('customer.appointment-payment', compact('appointment'));
    }

    /**
     * Process the simulated payment for a pending_payment appointment.
     * POST /appointment/{id}/pay
     */
    public function processPayment(Request $request, int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->where('status', Appointment::STATUS_PENDING_PAYMENT)
            ->findOrFail($id);

        $request->validate([
            'card_name'   => 'required|string|max:100',
            'card_number' => ['required', 'string', 'regex:/^\d{4} \d{4} \d{4} \d{4}$/'],
            'card_exp'    => ['required', 'string', 'regex:/^\d{2} \/ \d{2}$/',
                function ($attr, $value, $fail) {
                    [$m, $y] = explode(' / ', $value);
                    $expires = \Carbon\Carbon::createFromDate('20' . $y, (int) $m, 1)->endOfMonth();
                    if ($expires->isPast()) {
                        $fail('The expiry date has passed.');
                    }
                },
            ],
            'card_cvc'    => ['required', 'string', 'regex:/^\d{3,4}$/'],
        ], [
            'card_number.regex' => 'Card number must be 16 digits (e.g. 4242 4242 4242 4242).',
            'card_exp.regex'    => 'Expiry must be in MM / YY format.',
            'card_cvc.regex'    => 'CVC must be 3 or 4 digits.',
        ]);

        try {
            if ($appointment->stripe_payment_intent_id) {
                // Use the real Stripe PI stored on the appointment
                $this->service->confirmPayment($appointment->stripe_payment_intent_id, 'succeeded');
            } else {
                // No real PI — simulate advance for demo / test environments
                $appointment->update([
                    'status'                => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
                    'stripe_payment_status' => 'succeeded',
                    'payment_status'        => 'paid',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Appointment payment processing error', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('appointment.details', $id)
                             ->with('error', 'Payment could not be processed. Please try again.');
        }

        return redirect()->route('appointment.details', $id)
                         ->with('success', 'Payment successful! Your appointment is now pending expert approval.');
    }

    /**
     * Customer accepts or rejects an expert's reschedule proposal.
     * Section 6 – Reschedule Logic: POST /appointment/{id}/reschedule-response
     * Input: action[accept|reject]
     */
    public function rescheduleResponse(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->where('status', Appointment::STATUS_RESCHEDULED)
            ->findOrFail($id);

        $reschedule = AppointmentReschedule::where('appointment_id', $appointment->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        DB::transaction(function () use ($request, $appointment, $reschedule, $user) {
            if ($request->action === 'accept') {
                // Update appointment to the new proposed time
                $appointment->update([
                    'scheduled_at' => $reschedule->proposed_scheduled_at,
                    'status'       => Appointment::STATUS_ACCEPTED,
                ]);
                $reschedule->update(['status' => 'accepted']);
            } else {
                // Reject: revert appointment to confirmed/accepted, keep original time
                $appointment->update(['status' => Appointment::STATUS_ACCEPTED]);
                $reschedule->update(['status' => 'rejected']);
            }
        });

        // Notify the expert about the customer's decision
        if ($appointment->expert?->user) {
            try {
                $appointment->expert->user->notify(
                    new AppointmentRescheduledNotification(
                        $appointment->fresh(),
                        $reschedule->fresh(),
                        $request->action === 'accept' ? 'accepted' : 'rejected'
                    )
                );
            } catch (\Throwable $e) {
                Log::warning('Reschedule response notification to expert failed: ' . $e->getMessage());
            }
        }

        $message = $request->action === 'accept'
            ? 'Reschedule accepted. Your appointment has been updated.'
            : 'Reschedule rejected. Your appointment remains at the original time.';

        return redirect()->route('appointment.details', $id)->with('success', $message);
    }
}

