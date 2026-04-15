<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\AppointmentReschedule;
use App\Models\AppointmentStatusHistory;
use App\Models\Expert;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\Appointment\AppointmentBookingCreatedNotification;
use App\Notifications\Appointment\AppointmentCancelledNotification;
use App\Notifications\Appointment\AppointmentConfirmedMailNotification;
use App\Notifications\Appointment\AppointmentPaymentSuccessNotification;
use App\Notifications\Appointment\AppointmentRejectedNotification;
use App\Notifications\Appointment\ExpertNewBookingNotification;
use App\Notifications\Appointment\AdminNewBookingNotification;
use App\Notifications\Appointment\AdminPaymentFailureNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Stripe\PaymentIntent;

/**
 * AppointmentService — Core appointment lifecycle orchestrator.
 *
 * All state transitions happen here.  Controllers must be thin.
 * Every transition is:
 *   1. Validated against the state machine
 *   2. Wrapped in a DB transaction
 *   3. Audit-logged in appointment_logs
 *   4. Status-historied in appointment_status_histories
 *   5. Notified via queued mail
 *
 * Race-condition safety:
 *   - Slot booking uses SELECT FOR UPDATE via AvailabilityService
 *   - Payment Intent creation is idempotent via Stripe idempotency keys
 *   - Webhook events are idempotent (checked before state transition)
 */
class AppointmentService
{
    public function __construct(
        private readonly StripeService       $stripe,
        private readonly AvailabilityService $availability,
        private readonly ScheduleService     $schedule,
        private readonly MarketplacePayoutService $payouts,
    ) {}

    public function book(User $user, array $data): array
    {
        return $this->initiateBooking($user, $data);
    }

    // =========================================================================
    // STEP 1 — Customer creates a draft appointment and gets a Stripe PaymentIntent
    // =========================================================================

    /**
     * Create a draft appointment and return a Stripe client_secret for frontend payment.
     *
     * Flow:
     *   1. Validate slot is available (with pessimistic lock inside transaction)
     *   2. Create appointment in 'draft' status
     *   3. Lock the slot
     *   4. Create Stripe PaymentIntent (idempotent)
     *   5. Set status to 'pending_payment'
     *   6. Notify admin of new booking
     *   7. Return appointment + client_secret to caller
     *
     * @throws ValidationException on double-booking
     * @throws \DomainException on expert unavailable / past slot
     * @throws \Stripe\Exception\ApiErrorException on Stripe failure
     */
    public function initiateBooking(User $user, array $data): array
    {
        $expert = Expert::findOrFail($data['expert_id']);
        $this->availability->assertExpertAvailable($expert);

        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $type        = $data['type'] ?? 'online';
        $duration    = (int) ($data['duration_minutes'] ?? $expert->consultation_duration_minutes ?? 60);
        $this->schedule->assertBookingAllowed($expert, $scheduledAt, $type, $duration);
        $location = $type === 'physical' ? $this->schedule->resolveLocation($expert) : null;

        return DB::transaction(function () use ($user, $expert, $data, $scheduledAt, $type, $location) {
            // Lock the slot — throws DomainException on conflict
            $slot = null;
            if (! empty($data['slot_id'])) {
                // Temporarily create in draft so we can link the slot
                $appointment = Appointment::create([
                    'user_id'          => $user->id,
                    'expert_id'        => $expert->id,
                    'type'             => $type,
                    'status'           => Appointment::STATUS_DRAFT,
                    'fee'              => $expert->consultation_fee ?? $expert->hourly_rate ?? 0,
                    'duration_minutes' => $data['duration_minutes'] ?? 60,
                    'notes'            => $data['notes'] ?? null,
                    'topic'            => $data['topic'] ?? null,
                    'location'         => $location,
                ]);

                $slot = $this->availability->lockSlot((int) $data['slot_id'], $appointment);
            } else {
                // Fallback: no slot system — use scheduled_at + overlap check
                $expertId    = $expert->id;

                $conflict = Appointment::where('expert_id', $expertId)
                    ->where('scheduled_at', $scheduledAt)
                    ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_REJECTED, Appointment::STATUS_PAYMENT_FAILED])
                    ->lockForUpdate()
                    ->first();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'scheduled_at' => 'This time slot is already booked. Please choose another time.',
                    ]);
                }

                $appointment = Appointment::create([
                    'user_id'          => $user->id,
                    'expert_id'        => $expert->id,
                    'type'             => $type,
                    'scheduled_at'     => $scheduledAt,
                    'status'           => Appointment::STATUS_DRAFT,
                    'fee'              => $expert->consultation_fee ?? $expert->hourly_rate ?? 0,
                    'duration_minutes' => $data['duration_minutes'] ?? 60,
                    'notes'            => $data['notes'] ?? null,
                    'topic'            => $data['topic'] ?? null,
                    'location'         => $location,
                ]);
            }

            // Create Stripe Checkout Session (and keep the underlying PaymentIntent for compatibility)
            $checkout = $this->stripe->createAppointmentCheckoutSession($appointment, [
                'appointment_id' => (string) $appointment->id,
            ]);

            $pi = $checkout['paymentIntent'];

            $appointment->update([
                'stripe_payment_intent_id' => $pi?->id,
                'stripe_payment_status'    => $pi?->status ?? 'requires_payment_method',
                'status'                   => Appointment::STATUS_PENDING_PAYMENT,
                'payment_status'           => 'pending',
            ]);

            Payment::updateOrCreate(
                [
                    'appointment_id' => $appointment->id,
                    'gateway'        => 'stripe',
                ],
                [
                    'user_id'                  => $user->id,
                    'gateway_transaction_id'   => $pi?->id,
                    'stripe_session_id'        => $checkout['session']->id,
                    'stripe_payment_intent_id' => $pi?->id,
                    'payment_type'             => 'appointment',
                    'amount'                   => $appointment->fee,
                    'currency'                 => strtolower(config('plantix.currency_code', 'usd')),
                    'status'                   => 'pending',
                    'metadata'                 => [
                        'appointment_id' => $appointment->id,
                        'expert_id'      => $expert->id,
                        'checkout_url'   => $checkout['checkout_url'] ?? null,
                    ],
                ]
            );

            $this->recordStatusHistory($appointment, null, Appointment::STATUS_DRAFT, Appointment::STATUS_PENDING_PAYMENT, $user->id, 'Payment intent created.');
            AppointmentLog::record($appointment, 'payment_intent_created', $user->id, Appointment::STATUS_DRAFT, Appointment::STATUS_PENDING_PAYMENT, "PI: {$pi->id}");

            // Notify admin
            $this->notifyAdmin('new_booking', $appointment);

            return [
                'appointment'    => $appointment->fresh(['expert.user']),
                'client_secret'  => $pi?->client_secret,
                'payment_intent' => $pi?->id,
                'checkout_url'   => $checkout['checkout_url'] ?? null,
            ];
        });
    }

    // =========================================================================
    // STEP 2 — Stripe webhook confirms payment → status = pending_expert_approval
    // =========================================================================

    /**
     * Called ONLY by the verified Stripe webhook handler.
     * Idempotent: safe to call multiple times with the same PaymentIntent.
     */
    public function confirmPayment(string $paymentIntentId, string $stripeStatus): void
    {
        $appointment = Appointment::where('stripe_payment_intent_id', $paymentIntentId)
            ->lockForUpdate()
            ->first();

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->orWhere('gateway_transaction_id', $paymentIntentId)
            ->latest()
            ->first();

        if (! $appointment && $payment?->appointment_id) {
            $appointment = Appointment::where('id', $payment->appointment_id)->lockForUpdate()->first();
        }

        if (! $appointment) {
            Log::warning("StripeWebhook: No appointment found for PI {$paymentIntentId}");
            return;
        }

        // Idempotency: skip if already past pending_payment
        if (! in_array($appointment->status, [
            Appointment::STATUS_PENDING_PAYMENT,
            Appointment::STATUS_DRAFT,
            Appointment::STATUS_PAYMENT_FAILED,
        ])) {
            Log::info("StripeWebhook: Appointment #{$appointment->id} already past pending_payment; ignoring.");
            return;
        }

        DB::transaction(function () use ($appointment, $stripeStatus, $paymentIntentId) {
            $from = $appointment->status;
            $appointment->update([
                'stripe_payment_status' => $stripeStatus,
                'payment_status'        => 'paid',
                'status'                => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            ]);

            Payment::where('appointment_id', $appointment->id)
                ->where('gateway', 'stripe')
                ->latest()
                ->first()?->update([
                    'status'                   => 'completed',
                    'paid_at'                  => now(),
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'gateway_transaction_id'   => $paymentIntentId,
                ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_PENDING_EXPERT_APPROVAL, null, 'Stripe payment confirmed.');
            AppointmentLog::record($appointment, 'payment_confirmed', null, $from, Appointment::STATUS_PENDING_EXPERT_APPROVAL, 'Webhook: payment_intent.succeeded');

            // Notify customer + expert
            $this->notifyCustomer('payment_success', $appointment);
            $this->notifyExpert('new_booking', $appointment);

            try {
                $this->payouts->settleAppointment($appointment->fresh(['expert.user']));
            } catch (\Throwable $e) {
                Log::error('Appointment payout settlement failed', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Called when payment_intent.payment_failed webhook arrives.
     */
    public function handlePaymentFailed(string $paymentIntentId): void
    {
        $appointment = Appointment::where('stripe_payment_intent_id', $paymentIntentId)
            ->lockForUpdate()
            ->first();

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->orWhere('gateway_transaction_id', $paymentIntentId)
            ->latest()
            ->first();

        if (! $appointment && $payment?->appointment_id) {
            $appointment = Appointment::where('id', $payment->appointment_id)->lockForUpdate()->first();
        }

        if (! $appointment || $appointment->status !== Appointment::STATUS_PENDING_PAYMENT) {
            return;
        }

        DB::transaction(function () use ($appointment, $paymentIntentId) {
            $from = $appointment->status;
            $appointment->update([
                'stripe_payment_status' => 'failed',
                'payment_status'        => 'failed',
                'status'                => Appointment::STATUS_PAYMENT_FAILED,
            ]);

            Payment::where('appointment_id', $appointment->id)
                ->where('gateway', 'stripe')
                ->latest()
                ->first()?->update([
                    'status'                 => 'failed',
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'gateway_transaction_id' => $paymentIntentId,
                ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_PAYMENT_FAILED, null, 'Stripe payment failed.');
            AppointmentLog::record($appointment, 'payment_failed', null, $from, Appointment::STATUS_PAYMENT_FAILED);

            // Release the slot so others can book
            $this->availability->releaseSlot($appointment);

            // Notify admin
            $this->notifyAdmin('payment_failure', $appointment);
        });
    }

    // =========================================================================
    // STEP 3 — Expert accepts/rejects
    // =========================================================================

    /**
     * Admin or service confirms an appointment after payment.
     * Also used by admin to force-confirm.
     */
    public function confirm(Appointment $appointment, ?int $expertId = null, ?string $adminNotes = null, bool $isAdmin = false, ?int $adminUserId = null, ?string $meetingLink = null): Appointment
    {
        if ($appointment->isOnline() && empty($meetingLink) && empty($appointment->meeting_link)) {
            throw new \DomainException('Meeting link is required before confirming an online appointment.');
        }

        $appointment->assertCanTransitionTo(Appointment::STATUS_CONFIRMED, $isAdmin);

        return DB::transaction(function () use ($appointment, $expertId, $adminNotes, $isAdmin, $adminUserId, $meetingLink) {
            $from = $appointment->status;
            $appointment->update([
                'status'      => Appointment::STATUS_CONFIRMED,
                'expert_id'   => $expertId ?? $appointment->expert_id,
                'admin_id'    => $adminUserId ?? $appointment->admin_id,
                'admin_notes' => $adminNotes ?? $appointment->admin_notes,
                'accepted_at' => now(),
                'meeting_link' => $meetingLink ?? $appointment->meeting_link,
            ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_CONFIRMED, $adminUserId, $adminNotes);
            AppointmentLog::record($appointment, $isAdmin ? 'admin_confirmed' : 'confirmed', $adminUserId, $from, Appointment::STATUS_CONFIRMED, $adminNotes);

            $this->notifyCustomer('confirmed', $appointment);

            return $appointment->fresh(['expert.user', 'user']);
        });
    }

    /**
     * Reject an appointment (expert or admin).
     */
    public function reject(Appointment $appointment, string $reason, ?int $byUserId = null, bool $isAdmin = false): Appointment
    {
        $appointment->assertCanTransitionTo(Appointment::STATUS_REJECTED, $isAdmin);

        return DB::transaction(function () use ($appointment, $reason, $byUserId) {
            $from = $appointment->status;
            $appointment->update([
                'status'                => Appointment::STATUS_REJECTED,
                'reject_reason'         => $reason,
                'expert_response_notes' => $reason,
                'rejected_at'           => now(),
            ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_REJECTED, $byUserId, $reason);
            AppointmentLog::record($appointment, 'rejected', $byUserId, $from, Appointment::STATUS_REJECTED, $reason);

            // Release slot, issue refund if paid
            $this->availability->releaseSlot($appointment);
            $this->attemptAutoRefund($appointment, $byUserId);

            $this->notifyCustomer('rejected', $appointment);

            return $appointment->fresh();
        });
    }

    // =========================================================================
    // Cancellation
    // =========================================================================

    /**
     * Cancel an appointment. Admin can cancel any status.
     */
    public function cancel(Appointment $appointment, string $reason, bool $isAdmin = false, ?int $byUserId = null): Appointment
    {
        if (! $isAdmin) {
            if (! $appointment->canBeCancelledByCustomer()) {
                throw new \DomainException("Appointment #{$appointment->id} cannot be cancelled at this stage.");
            }
        } else {
            // Admin can always cancel
        }

        return DB::transaction(function () use ($appointment, $reason, $isAdmin, $byUserId) {
            $from = $appointment->status;
            $appointment->update([
                'status'              => Appointment::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at'        => now(),
                'admin_id'            => $isAdmin ? ($byUserId ?? $appointment->admin_id) : $appointment->admin_id,
            ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_CANCELLED, $byUserId, $reason);
            AppointmentLog::record($appointment, $isAdmin ? 'admin_cancelled' : 'customer_cancelled', $byUserId, $from, Appointment::STATUS_CANCELLED, $reason);

            // Release the slot
            $this->availability->releaseSlot($appointment);

            // Auto-refund if paid
            $this->attemptAutoRefund($appointment, $byUserId);

            $this->notifyCustomer('cancelled', $appointment);

            return $appointment->fresh();
        });
    }

    // =========================================================================
    // Completion
    // =========================================================================

    public function complete(Appointment $appointment, ?int $byUserId = null): Appointment
    {
        $appointment->assertCanTransitionTo(Appointment::STATUS_COMPLETED);

        return DB::transaction(function () use ($appointment, $byUserId) {
            $from = $appointment->status;
            $appointment->update([
                'status'       => Appointment::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_COMPLETED, $byUserId);
            AppointmentLog::record($appointment, 'completed', $byUserId, $from, Appointment::STATUS_COMPLETED);

            return $appointment->fresh();
        });
    }

    /**
     * Admin-driven generic status update that still respects the appointment state machine.
     */
    public function updateStatus(Appointment $appointment, string $targetStatus, ?int $adminUserId = null, ?string $notes = null): Appointment
    {
        if (
            $targetStatus === Appointment::STATUS_CONFIRMED
            && $appointment->isOnline()
            && empty($appointment->meeting_link)
        ) {
            throw new \DomainException('Meeting link is required before confirming an online appointment.');
        }

        $appointment->assertCanTransitionTo($targetStatus, true);

        return DB::transaction(function () use ($appointment, $targetStatus, $adminUserId, $notes) {
            $from = $appointment->status;
            $payload = [
                'status'    => $targetStatus,
                'admin_id'  => $adminUserId ?? $appointment->admin_id,
            ];

            if ($notes !== null && $notes !== '') {
                $payload['admin_notes'] = $notes;
            }

            if ($targetStatus === Appointment::STATUS_CONFIRMED) {
                $payload['accepted_at'] = now();
            } elseif ($targetStatus === Appointment::STATUS_COMPLETED) {
                $payload['completed_at'] = now();
            } elseif ($targetStatus === Appointment::STATUS_CANCELLED) {
                $payload['cancelled_at'] = now();
                $payload['cancellation_reason'] = $notes ?? $appointment->cancellation_reason;
            } elseif ($targetStatus === Appointment::STATUS_REJECTED) {
                $payload['rejected_at'] = now();
                $payload['reject_reason'] = $notes ?? $appointment->reject_reason;
                $payload['expert_response_notes'] = $notes ?? $appointment->expert_response_notes;
            } elseif ($targetStatus === Appointment::STATUS_RESCHEDULE_REQUESTED) {
                $payload['reschedule_requested_at'] = now();
            }

            $appointment->update($payload);

            $this->recordStatusHistory($appointment, null, $from, $targetStatus, $adminUserId, $notes);
            AppointmentLog::record($appointment, 'admin_status_updated', $adminUserId, $from, $targetStatus, $notes);

            if ($targetStatus === Appointment::STATUS_CONFIRMED) {
                $this->notifyCustomer('confirmed', $appointment);
            } elseif ($targetStatus === Appointment::STATUS_REJECTED) {
                $this->availability->releaseSlot($appointment);
                $this->attemptAutoRefund($appointment, $adminUserId);
                $this->notifyCustomer('rejected', $appointment);
            } elseif ($targetStatus === Appointment::STATUS_CANCELLED) {
                $this->availability->releaseSlot($appointment);
                $this->attemptAutoRefund($appointment, $adminUserId);
                $this->notifyCustomer('cancelled', $appointment);
            }

            return $appointment->fresh(['expert.user', 'user']);
        });
    }

    // =========================================================================
    // Reschedule
    // =========================================================================

    /**
     * Customer requests a reschedule (separate from expert-proposed reschedule).
     * Sets status to reschedule_requested and notifies expert.
     */
    public function reschedule(Appointment $appointment, array $data, ?int $byUserId = null): Appointment
    {
        $appointment->assertCanTransitionTo(Appointment::STATUS_RESCHEDULE_REQUESTED);

        return DB::transaction(function () use ($appointment, $data, $byUserId) {
            $from = $appointment->status;

            $reschedule = AppointmentReschedule::create([
                'appointment_id'        => $appointment->id,
                'requested_by'          => $byUserId,
                'original_scheduled_at' => $appointment->scheduled_at,
                'proposed_scheduled_at' => $data['scheduled_at'],
                'reason'                => $data['notes'] ?? null,
                'status'                => 'pending',
            ]);

            $appointment->update([
                'status'                  => Appointment::STATUS_RESCHEDULE_REQUESTED,
                'reschedule_requested_at' => now(),
            ]);

            $this->recordStatusHistory($appointment, null, $from, Appointment::STATUS_RESCHEDULE_REQUESTED, $byUserId, $data['notes'] ?? null);
            AppointmentLog::record($appointment, 'reschedule_requested', $byUserId, $from, Appointment::STATUS_RESCHEDULE_REQUESTED);

            return $appointment->fresh();
        });
    }

    // =========================================================================
    // Admin — Stripe refund
    // =========================================================================

    /**
     * Admin issues a full or partial Stripe refund.
     * Notifies customer + logs in audit trail.
     *
     * @throws \DomainException|\Stripe\Exception\ApiErrorException
     */
    public function adminRefund(Appointment $appointment, ?float $amount = null, ?string $note = null, ?int $adminId = null): Appointment
    {
        $refund = $amount !== null
            ? $this->stripe->refundPartial($appointment, $amount, $note)
            : $this->stripe->refundFull($appointment, $note);

        AppointmentLog::record($appointment, 'refund_issued', $adminId, $appointment->status, null, $note, ['refund_id' => $refund->id, 'amount' => $amount ?? $appointment->fee]);

        $appointment->update(['admin_id' => $adminId ?? $appointment->admin_id]);

        // Notify admin + customer
        $this->notifyAdmin('refund_issued', $appointment);
        $this->notifyCustomer('cancelled', $appointment->fresh());

        return $appointment->fresh();
    }

    // =========================================================================
    // Admin — reassign expert
    // =========================================================================

    public function reassignExpert(Appointment $appointment, int $newExpertId, ?int $adminId = null, ?string $note = null): Appointment
    {
        $expert = Expert::findOrFail($newExpertId);
        $this->availability->assertExpertAvailable($expert);

        return DB::transaction(function () use ($appointment, $expert, $adminId, $note) {
            $oldExpertId = $appointment->expert_id;
            $appointment->update([
                'expert_id' => $expert->id,
                'admin_id'  => $adminId ?? $appointment->admin_id,
            ]);

            AppointmentLog::record($appointment, 'expert_reassigned', $adminId, null, null, $note, ['old_expert_id' => $oldExpertId, 'new_expert_id' => $expert->id]);

            return $appointment->fresh(['expert.user', 'user']);
        });
    }

    // =========================================================================
    // Notification helpers (queued via ShouldQueue on each notification class)
    // =========================================================================

    private function notifyCustomer(string $event, Appointment $appointment): void
    {
        $user = $appointment->user;
        if (! $user) {
            return;
        }

        try {
            $notification = match ($event) {
                'booking_created' => new AppointmentBookingCreatedNotification($appointment),
                'payment_success' => new AppointmentPaymentSuccessNotification($appointment),
                'confirmed'       => new AppointmentConfirmedMailNotification($appointment),
                'rejected'        => new AppointmentRejectedNotification($appointment),
                'cancelled'       => new AppointmentCancelledNotification($appointment),
                default           => null,
            };

            if ($notification) {
                $user->notify($notification);
            }
        } catch (\Throwable $e) {
            Log::error("AppointmentService::notifyCustomer [{$event}] failed: " . $e->getMessage());
        }
    }

    private function notifyExpert(string $event, Appointment $appointment): void
    {
        $expertUser = optional($appointment->expert)->user;
        if (! $expertUser) {
            return;
        }

        try {
            $notification = match ($event) {
                'new_booking' => new ExpertNewBookingNotification($appointment),
                'cancelled'   => new AppointmentCancelledNotification($appointment),
                default       => null,
            };

            if ($notification) {
                $expertUser->notify($notification);
            }
        } catch (\Throwable $e) {
            Log::error("AppointmentService::notifyExpert [{$event}] failed: " . $e->getMessage());
        }
    }

    private function notifyAdmin(string $event, Appointment $appointment): void
    {
        try {
            // Only notify super admins (role_id = null means unrestricted super admin).
            // Staff sub-admins (role_id set) should not receive every system notification.
            $admins = \App\Models\User::where('role', 'admin')->whereNull('role_id')->get();
            if ($admins->isEmpty()) {
                return;
            }

            $notification = match ($event) {
                'new_booking'    => new AdminNewBookingNotification($appointment),
                'payment_failure'=> new AdminPaymentFailureNotification($appointment),
                'refund_issued'  => new AdminPaymentFailureNotification($appointment), // reuse or create specific
                default          => null,
            };

            if ($notification) {
                Notification::send($admins, $notification);
            }
        } catch (\Throwable $e) {
            Log::error("AppointmentService::notifyAdmin [{$event}] failed: " . $e->getMessage());
        }
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    private function recordStatusHistory(
        Appointment $appointment,
        mixed       $changedBy,
        string      $from,
        string      $to,
        ?int        $userId = null,
        ?string     $notes  = null
    ): void {
        \App\Models\AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'changed_by'     => $userId,
            'from_status'    => $from,
            'to_status'      => $to,
            'notes'          => $notes,
            'changed_at'     => now(),
        ]);
    }

    /**
     * Attempt an automatic full refund if the appointment was paid.
     * Logs but does NOT throw on Stripe failure (admin can retry manually).
     */
    private function attemptAutoRefund(Appointment $appointment, ?int $byUserId): void
    {
        if (! $appointment->isPaid() || $appointment->is_refunded) {
            return;
        }

        try {
            $this->stripe->refundFull($appointment, 'Auto-refund on cancellation/rejection.');
            AppointmentLog::record($appointment, 'auto_refund_issued', $byUserId, null, null, 'Automatic refund on cancel/reject.');
        } catch (\Throwable $e) {
            Log::error("AppointmentService::attemptAutoRefund failed for appointment #{$appointment->id}: " . $e->getMessage());
            // Admin must issue refund manually — this is non-fatal
        }
    }
}

