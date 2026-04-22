<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\AppointmentRescheduleResponseRequest;
use App\Http\Requests\Frontend\CustomerAppointmentReviewRequest;
use App\Http\Requests\Frontend\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\Expert;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\Expert\RatingService;
use App\Services\Shared\AvailabilityService;
use App\Services\Shared\AppointmentStatusService;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CustomerAppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $service,
        private readonly AvailabilityService $availability,
        private readonly RatingService $ratingService,
        private readonly AppointmentStatusService $appointmentStatus,
    ) {}

    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expert_id' => ['required', 'integer', 'exists:experts,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $expert = Expert::query()->available()->findOrFail((int) $data['expert_id']);
        $slots = $this->availability->getAvailableSlots($expert, (string) $data['date']);

        return response()->json([
            'success' => true,
            'date' => (string) $data['date'],
            'has_availability_template' => $expert->availability()->active()->exists(),
            'slots' => $slots->map(fn ($slot) => [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
            ])->values(),
        ]);
    }

    public function index(Request $request): View
    {
        $user = auth('web')->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'in:online,physical'],
            'expert_id' => ['nullable', 'integer', 'exists:experts,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $appointmentsQuery = $user->appointments()->with(['expert.user', 'expert.profile'])->latest();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $appointmentsQuery->where(function ($query) use ($term): void {
                $query->where('topic', 'like', '%' . $term . '%')
                    ->orWhereHas('expert.user', fn ($expertUserQuery) => $expertUserQuery
                        ->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
                    );
            });
        }

        if (! empty($filters['status'])) {
            $appointmentsQuery->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $appointmentsQuery->where('type', $filters['type']);
        }

        if (! empty($filters['expert_id'])) {
            $appointmentsQuery->where('expert_id', (int) $filters['expert_id']);
        }

        if (! empty($filters['date_from'])) {
            $appointmentsQuery->whereDate('scheduled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $appointmentsQuery->whereDate('scheduled_at', '<=', $filters['date_to']);
        }

        $appointments = $appointmentsQuery->paginate(10)->withQueryString();
        $experts = Expert::with('user:id,name')->orderBy('id')->get(['id', 'user_id']);

        return view('customer.appointments', compact('appointments', 'filters', 'experts'));
    }

    public function create(): View
    {
        $experts = Expert::with(['user', 'profile'])->available()->get();
        return view('customer.appointment-book', compact('experts'));
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $user = auth('web')->user();
        $this->service->book($user, $request->validated());

        return redirect()->route('appointments')
                         ->with('success', 'Appointment booked! You will receive a confirmation email.');
    }

    public function show(int $id): View
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->with(['expert.user', 'expert.profile', 'reschedules' => fn ($q) => $q->where('status', 'pending')])
            ->findOrFail($id);

        return view('customer.appointment-details', compact('appointment'));
    }

    public function cancel(int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()->findOrFail($id);

        if (! $appointment->canBeCancelledByCustomer()) {
            return back()->withErrors([
                'error' => 'This appointment can no longer be cancelled at its current stage.',
            ]);
        }

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
     * Keep appointment payment pending; status mutation is webhook-driven only.
     * POST /appointment/{id}/pay
     */
    public function processPayment(Request $request, int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->where('status', Appointment::STATUS_PENDING_PAYMENT)
            ->findOrFail($id);

        // Intentionally no status mutation here.
        return redirect()->route('appointment.details', $id)
            ->with('info', 'Payment is pending confirmation. Your appointment will update after Stripe webhook verification.');
    }

    /**
     * Customer accepts or rejects an expert's reschedule proposal.
     * Section 6 – Reschedule Logic: POST /appointment/{id}/reschedule-response
     * Input: action[accept|reject]
     */
    public function rescheduleResponse(AppointmentRescheduleResponseRequest $request, int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()
            ->where('status', Appointment::STATUS_RESCHEDULE_REQUESTED)
            ->findOrFail($id);

        $reschedule = AppointmentReschedule::where('appointment_id', $appointment->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        if ($request->input('action') === 'accept') {
            $appointment = $this->appointmentStatus->acceptReschedule($appointment, $user->id);
        } else {
            $appointment = $this->appointmentStatus->rejectReschedule($appointment, $user->id);
        }

        // Notify the expert about the customer's decision
        if ($appointment->expert?->user) {
            try {
                $appointment->expert->user->notify(
                    new AppointmentRescheduledNotification(
                        $appointment->fresh(),
                        $reschedule->fresh(),
                        $request->input('action') === 'accept' ? 'accepted' : 'rejected'
                    )
                );
            } catch (\Throwable $e) {
                Log::warning('Reschedule response notification to expert failed: ' . $e->getMessage());
            }
        }

        $message = $request->input('action') === 'accept'
            ? 'Reschedule accepted. Your appointment has been updated.'
            : 'Reschedule rejected. Your appointment remains at the original time.';

        return redirect()->route('appointment.details', $id)->with('success', $message);
    }

    public function review(CustomerAppointmentReviewRequest $request, int $id): RedirectResponse
    {
        $user        = auth('web')->user();
        $appointment = $user->appointments()->findOrFail($id);

        if (! $appointment->canBeReviewed()) {
            return back()->withErrors([
                'customer_rating' => 'You can only review a completed appointment.',
            ]);
        }

        try {
            $this->ratingService->submitRating(
                $appointment,
                (int) $request->input('customer_rating'),
                $request->input('customer_review')
            );
        } catch (\Throwable $e) {
            Log::error('Appointment review submission failed', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'customer_rating' => 'We could not save your review right now. Please try again.',
            ]);
        }

        return redirect()->route('appointment.details', $id)
            ->with('success', 'Your review has been saved successfully.');
    }
}

