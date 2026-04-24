<?php

namespace App\Services\Expert;

use App\Events\Expert\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\AppointmentReschedule;
use App\Models\AppointmentStatusHistory;
use App\Models\Expert;
use App\Models\User;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\Shared\AppointmentStatusService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ExpertAppointmentService
 *
 * Manages the full appointment lifecycle for expert users.
 * All status transitions are DB-transactional and audit-logged.
 */
class ExpertAppointmentService
{
    public function __construct(
        private readonly AppointmentStatusService $appointmentStatus,
    ) {}

    // ── Queries ───────────────────────────────────────────────────────────────

    public function listForExpert(Expert $expert, array $filters = []): LengthAwarePaginator
    {
        $query = Appointment::with(['user', 'latestReschedule'])
            ->where('expert_id', $expert->id);

        if (! empty($filters['search'])) {
            $term = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('topic', 'like', $term)
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('scheduled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('scheduled_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('scheduled_at', 'asc')->paginate(15);
    }

    public function getStats(Expert $expert): array
    {
        $base = Appointment::where('expert_id', $expert->id);

        return [
            'total'       => (clone $base)->count(),
            'pending'     => (clone $base)->whereIn('status', [Appointment::STATUS_PENDING_EXPERT_APPROVAL, Appointment::STATUS_RESCHEDULE_REQUESTED])->count(),
            'upcoming'    => (clone $base)->whereIn('status', [Appointment::STATUS_CONFIRMED])->upcoming()->count(),
            'completed'   => (clone $base)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'rejected'    => (clone $base)->where('status', Appointment::STATUS_REJECTED)->count(),
        ];
    }

    // ── Lifecycle transitions ─────────────────────────────────────────────────

    public function accept(Appointment $appointment, Expert $expert, ?string $meetingLink = null): Appointment
    {
        $this->assertBelongsToExpert($appointment, $expert);
        $this->assertCanTransition($appointment, Appointment::STATUS_CONFIRMED);

        if ($appointment->isOnline() && empty($meetingLink)) {
            throw new \DomainException('Meeting link is required to accept an online appointment.');
        }

        $result = $this->transition(
            $appointment,
            Appointment::STATUS_CONFIRMED,
            $expert->user_id,
            ['accepted_at' => now(), 'meeting_link' => $meetingLink]
        );

        // Notify the customer that their appointment has been confirmed
        $customer = $result->user;
        if ($customer) {
            try {
                $customer->notify(new \App\Notifications\Appointment\AppointmentConfirmedMailNotification($result));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("ExpertAppointmentService::accept — customer notification failed: " . $e->getMessage());
            }
        }

        return $result;
    }

    public function reject(Appointment $appointment, Expert $expert, string $reason): Appointment
    {
        $this->assertBelongsToExpert($appointment, $expert);
        $this->assertCanTransition($appointment, Appointment::STATUS_REJECTED);

        $result = $this->transition(
            $appointment,
            Appointment::STATUS_REJECTED,
            $expert->user_id,
            ['rejected_at' => now(), 'reject_reason' => $reason, 'expert_response_notes' => $reason]
        );

        // Release the slot so it becomes available again
        app(\App\Services\Shared\AvailabilityService::class)->releaseSlot($result);

        // Auto-refund if the customer already paid
        if ($result->isPaid() && ! $result->is_refunded) {
            try {
                app(\App\Services\Shared\StripeService::class)->refundFull($result, 'Auto-refund: expert rejected appointment.');
                AppointmentLog::record($result, 'auto_refund_issued', $expert->user_id, null, null, 'Automatic refund on expert rejection.');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("ExpertAppointmentService::reject — auto-refund failed for appointment #{$result->id}: " . $e->getMessage());
            }
        }

        // Notify the customer of the rejection
        $customer = $result->user;
        if ($customer) {
            try {
                $customer->notify(new \App\Notifications\Appointment\AppointmentRejectedNotification($result));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("ExpertAppointmentService::reject — customer notification failed: " . $e->getMessage());
            }
        }

        return $result;
    }

    public function complete(Appointment $appointment, Expert $expert, ?string $notes = null): Appointment
    {
        $this->assertBelongsToExpert($appointment, $expert);
        $this->assertCanTransition($appointment, Appointment::STATUS_COMPLETED);

        return $this->transition(
            $appointment,
            Appointment::STATUS_COMPLETED,
            $expert->user_id,
            ['completed_at' => now(), 'admin_notes' => $notes]
        );
    }

    public function requestReschedule(
        Appointment $appointment,
        Expert $expert,
        \DateTimeInterface $newDateTime,
        ?string $reason = null
    ): AppointmentReschedule {
        $this->assertBelongsToExpert($appointment, $expert);

        $reschedule = $this->appointmentStatus->proposeReschedule(
            $appointment,
            $expert->user_id,
            $newDateTime,
            $reason
        );

        // Section 14 – Reschedule proposed → Customer → Email + In-app
        $appointment->loadMissing('user');
        if ($appointment->user) {
            try {
                $appointment->user->notify(
                    new AppointmentRescheduledNotification($appointment, $reschedule, 'proposed')
                );
            } catch (\Throwable $e) {
                Log::warning('Reschedule proposal notification failed: ' . $e->getMessage());
            }
        }

        return $reschedule;
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function transition(
        Appointment $appointment,
        string $toStatus,
        int $byUserId,
        array $extra = []
    ): Appointment {
        return DB::transaction(function () use ($appointment, $toStatus, $byUserId, $extra) {
            $fromStatus = $appointment->status;

            $appointment->update(array_merge(['status' => $toStatus], $extra));

            $this->logStatusChange($appointment, $byUserId, $fromStatus, $toStatus);

            $user = User::find($byUserId);
            if ($user) {
                event(new AppointmentStatusChanged($appointment->fresh(), $user, $toStatus));
            }

            return $appointment->fresh();
        });
    }

    private function logStatusChange(
        Appointment $appointment,
        int $byUserId,
        string $from,
        string $to,
        ?string $notes = null
    ): void {
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'changed_by'     => $byUserId,
            'from_status'    => $from,
            'to_status'      => $to,
            'notes'          => $notes,
            'changed_at'     => now(),
        ]);
    }

    private function assertBelongsToExpert(Appointment $appointment, Expert $expert): void
    {
        if ((int) $appointment->expert_id !== (int) $expert->id) {
            throw new \DomainException('This appointment does not belong to you.');
        }
    }

    private function assertCanTransition(Appointment $appointment, string $targetStatus): void
    {
        $allowed = match ($targetStatus) {
            Appointment::STATUS_CONFIRMED => $appointment->canBeAccepted(),
            Appointment::STATUS_REJECTED  => $appointment->canBeRejected(),
            Appointment::STATUS_COMPLETED => $appointment->canBeCompleted(),
            default                       => false,
        };

        if (! $allowed) {
            throw new \DomainException(
                "Cannot transition appointment #{$appointment->id} from '{$appointment->status}' to '{$targetStatus}'."
            );
        }
    }
}
