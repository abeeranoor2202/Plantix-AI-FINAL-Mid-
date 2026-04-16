<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\Expert;
use App\Services\Shared\AppointmentService;
use App\Services\Shared\AppointmentStatusService;
use App\Services\Shared\AvailabilityService;
use App\Services\Shared\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CustomerAppointmentApiController extends Controller
{
    public function __construct(
        private readonly AppointmentService  $service,
        private readonly AppointmentStatusService $appointmentStatus,
        private readonly AvailabilityService $availability,
        private readonly StripeService       $stripe,
    ) {}

    // ── List appointments ─────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $appointments = $request->user()
            ->appointments()
            ->with('expert.user')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success'      => true,
            'appointments' => $appointments->map(fn ($a) => $this->apptPayload($a)),
            'meta'         => [
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
                'total'        => $appointments->total(),
            ],
        ]);
    }

    // ── Available experts ─────────────────────────────────────────────────────

    public function experts(): JsonResponse
    {
        $experts = Expert::with('user')->available()->get()->map(fn ($e) => [
            'id'               => $e->id,
            'name'             => optional($e->user)->name,
            'speciality'       => $e->specialty,
            'bio'              => $e->bio,
            'consultation_fee' => $e->consultation_fee ?? $e->hourly_rate,
            'rating'           => $e->rating ?? null,
            'avatar'           => optional($e->user)->profile_photo
                                    ? asset('storage/' . $e->user->profile_photo)
                                    : null,
        ]);

        return response()->json(['success' => true, 'data' => $experts]);
    }

    // ── Available slots for an expert on a date ───────────────────────────────

    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expert_id' => 'required|exists:experts,id',
            'date'      => 'required|date|after_or_equal:today',
        ]);

        $expert = Expert::findOrFail($data['expert_id']);
        $slots  = $this->availability->getAvailableSlots($expert, $data['date']);

        return response()->json([
            'success' => true,
            'date'    => $data['date'],
            'slots'   => $slots->map(fn ($s) => [
                'id'         => $s->id,
                'start_time' => $s->start_time,
                'end_time'   => $s->end_time,
            ]),
        ]);
    }

    // ── Initiate booking (creates draft + Stripe PaymentIntent) ──────────────
    //
    // FLOW:
    //   1. Validate input
    //   2. Rate-limit (5 booking attempts per user per minute)
    //   3. service->initiateBooking() → creates appointment + locks slot
    //   4. Returns client_secret for frontend Stripe.js
    //   5. NO booking is confirmed before webhook fires
    //
    public function store(Request $request): JsonResponse
    {
        // ── Rate limiting ─────────────────────────────────────────────────────
        $key = 'book-appt:' . $request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many booking attempts. Try again in {$seconds} seconds.",
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'expert_id'        => 'required|exists:experts,id',
            'slot_id'          => 'nullable|exists:appointment_slots,id',
            'scheduled_at'     => 'required_without:slot_id|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'notes'            => 'nullable|string|max:1000',
            'topic'            => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->service->initiateBooking($request->user(), $data);

            return response()->json([
                'success'        => true,
                'message'        => 'Booking initiated. Complete payment to confirm.',
                'appointment'    => $this->apptPayload($result['appointment']),
                'client_secret'  => $result['client_secret'],
                'payment_intent' => $result['payment_intent'],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Show appointment ──────────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $appt = $request->user()
            ->appointments()
            ->with('expert.user', 'statusHistory')
            ->findOrFail($id);

        return response()->json(['success' => true, 'appointment' => $this->apptPayload($appt)]);
    }

    // ── Read-only payment status check (webhook-only settlement) ─────────────
    //
    // Frontend can poll this endpoint to display payment progress.
    // IMPORTANT: this endpoint MUST NOT mutate appointment/payment state.
    // Only verified Stripe webhooks can confirm payments.
    //
    public function checkPayment(Request $request, int $id): JsonResponse
    {
        $appt = $request->user()->appointments()->findOrFail($id);

        if (empty($appt->stripe_payment_intent_id)) {
            return response()->json(['success' => false, 'message' => 'No payment intent associated.'], 422);
        }

        try {
            $pi = $this->stripe->retrievePaymentIntent($appt->stripe_payment_intent_id);

            return response()->json([
                'success'      => true,
                'pi_status'    => $pi->status,
                'appt_status'  => $appt->status,
                'awaiting_webhook' => $appt->status === Appointment::STATUS_PENDING_PAYMENT,
                'appointment'  => $this->apptPayload($appt->load('expert.user')),
            ]);
        } catch (\Throwable $e) {
            Log::error("checkPayment error for appt #{$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment check failed.'], 500);
        }
    }

    // ── Cancel appointment ────────────────────────────────────────────────────

    public function cancel(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:500']);

        $appt = $request->user()->appointments()->findOrFail($id);

        if (! $appt->canBeCancelledByCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment cannot be cancelled at its current stage.',
            ], 422);
        }

        $this->service->cancel($appt, $data['reason'] ?? 'Cancelled by customer.', false, $request->user()->id);

        return response()->json(['success' => true, 'message' => 'Appointment cancelled.']);
    }

    // ── Reschedule appointment ────────────────────────────────────────────────

    public function reschedule(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes'        => 'nullable|string|max:500',
        ]);

        $appt = $request->user()
            ->appointments()
            ->whereNotIn('status', [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED])
            ->findOrFail($id);

        try {
            $this->service->reschedule($appt, $data, $request->user()->id);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Reschedule request submitted.',
            'appointment' => $this->apptPayload($appt->fresh()->load('expert.user')),
        ]);
    }

    // ── Respond to expert reschedule proposal ─────────────────────────────────

    public function rescheduleResponse(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['action' => 'required|in:accept,reject']);

        $appt = Appointment::where('user_id', $request->user()->id)
            ->where('status', Appointment::STATUS_RESCHEDULE_REQUESTED)
            ->findOrFail($id);

        if ($data['action'] === 'accept') {
            $this->appointmentStatus->acceptReschedule($appt, $request->user()->id);
        } else {
            $this->appointmentStatus->rejectReschedule($appt, $request->user()->id, 'Customer rejected reschedule proposal.');
        }

        $message = $data['action'] === 'accept'
            ? 'Reschedule accepted. Appointment moved to the proposed time.'
            : 'Reschedule rejected. Appointment remains confirmed at the original time.';

        return response()->json(['success' => true, 'message' => $message]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function apptPayload(Appointment $appt): array
    {
        return [
            'id'             => $appt->id,
            'status'         => $appt->status,
            'scheduled_at'   => $appt->scheduled_at?->toISOString(),
            'scheduled_date' => $appt->scheduled_date?->toDateString(),
            'start_time'     => $appt->start_time,
            'end_time'       => $appt->end_time,
            'fee'            => $appt->fee,
            'payment_status' => $appt->payment_status,
            'is_refunded'    => $appt->is_refunded,
            'meeting_link'   => $appt->meeting_link,
            'notes'          => $appt->notes,
            'expert'         => [
                'id'   => optional($appt->expert)->id,
                'name' => optional(optional($appt->expert)->user)->name,
            ],
            'created_at'     => $appt->created_at?->toISOString(),
        ];
    }
}
