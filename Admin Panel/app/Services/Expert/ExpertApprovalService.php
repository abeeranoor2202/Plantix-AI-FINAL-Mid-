<?php

namespace App\Services\Expert;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Expert;
use App\Models\ExpertLog;
use App\Models\ExpertProfile;
use App\Notifications\Expert\ExpertStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * ExpertApprovalService
 *
 * Owns all expert lifecycle state transitions.
 * Every state change MUST go through this service to ensure:
 *   1. Transition is valid (state machine)
 *   2. An ExpertLog row is written
 *   3. Expert is notified
 *   4. ExpertProfile.approval_status is kept in sync
 */
class ExpertApprovalService
{
    // ── Transitions ───────────────────────────────────────────────────────────

    /**
     * Move expert to "under_review" (admin opened the review).
     */
    public function markUnderReview(
        Expert $expert,
        ?int   $actorId = null,
        string $notes   = '',
        ?Request $request = null
    ): Expert {
        return $this->applyTransition($expert, Expert::STATUS_UNDER_REVIEW, ExpertLog::ACTION_UNDER_REVIEW, $actorId, $notes, $request);
    }

    /**
     * Approve an expert.
     */
    public function approve(
        Expert $expert,
        ?int   $actorId = null,
        string $notes   = '',
        ?Request $request = null
    ): Expert {
        return $this->applyTransition($expert, Expert::STATUS_APPROVED, ExpertLog::ACTION_APPROVED, $actorId, $notes, $request);
    }

    /**
     * Reject an expert application.
     */
    public function reject(
        Expert  $expert,
        string  $reason,
        ?int    $actorId = null,
        ?Request $request = null
    ): Expert {
        DB::transaction(function () use ($expert, $reason, $actorId, $request) {
            $expert->rejection_reason = $reason;
            $this->applyTransition($expert, Expert::STATUS_REJECTED, ExpertLog::ACTION_REJECTED, $actorId, $reason, $request, true);
        });

        return $expert->fresh();
    }

    /**
     * Suspend an approved expert.
     */
    public function suspend(
        Expert  $expert,
        string  $reason,
        ?int    $actorId = null,
        ?Request $request = null
    ): Expert {
        $updated = $this->applyTransition($expert, Expert::STATUS_SUSPENDED, ExpertLog::ACTION_SUSPENDED, $actorId, $reason, $request);

        $this->cancelActiveAppointmentsForSuspendedExpert($updated, $actorId, $reason);

        return $updated;
    }

    /**
     * Restore a suspended or inactive expert to approved.
     */
    public function restore(
        Expert  $expert,
        ?int    $actorId = null,
        string  $notes   = '',
        ?Request $request = null
    ): Expert {
        return $this->applyTransition($expert, Expert::STATUS_APPROVED, ExpertLog::ACTION_RESTORED, $actorId, $notes, $request);
    }

    /**
     * Deactivate (set inactive) an approved expert.
     */
    public function deactivate(
        Expert  $expert,
        ?int    $actorId = null,
        string  $notes   = '',
        ?Request $request = null
    ): Expert {
        return $this->applyTransition($expert, Expert::STATUS_INACTIVE, ExpertLog::ACTION_DEACTIVATED, $actorId, $notes, $request);
    }

    // ── Core transition engine ────────────────────────────────────────────────

    private function applyTransition(
        Expert   $expert,
        string   $newStatus,
        string   $action,
        ?int     $actorId,
        string   $notes,
        ?Request $request,
        bool     $alreadyInTransaction = false
    ): Expert {
        $fromStatus = $expert->status;

        $run = function () use ($expert, $newStatus, $action, $actorId, $notes, $request, $fromStatus) {
            // Validates transition and sets status + timestamps on model
            $expert->transitionTo($newStatus);
            $expert->save();

            // Keep ExpertProfile in sync (optional extended profile table)
            optional($expert->profile)->update(['approval_status' => $newStatus]);

            // Write immutable audit log
            ExpertLog::create([
                'expert_id'   => $expert->id,
                'actor_id'    => $actorId,
                'action'      => $action,
                'from_status' => $fromStatus,
                'to_status'   => $newStatus,
                'notes'       => $notes ?: null,
                'ip_address'  => $request?->ip(),
                'user_agent'  => $request?->userAgent(),
            ]);

            // Notify expert via email/database notification
            try {
                $expert->user->notify(new ExpertStatusChangedNotification($expert, $fromStatus, $newStatus, $notes));
            } catch (\Throwable $e) {
                // Notification failure must NOT roll back the state transition
                logger()->warning('ExpertStatusChangedNotification failed', [
                    'expert_id' => $expert->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        };

        if ($alreadyInTransaction) {
            $run();
        } else {
            DB::transaction($run);
        }

        return $expert->fresh();
    }

    private function cancelActiveAppointmentsForSuspendedExpert(Expert $expert, ?int $actorId, string $reason): void
    {
        $activeStatuses = [
            Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_RESCHEDULE_REQUESTED,
            Appointment::STATUS_RESCHEDULED,
            Appointment::STATUS_PENDING,
        ];

        DB::transaction(function () use ($expert, $actorId, $reason, $activeStatuses): void {
            $appointments = Appointment::query()
                ->where('expert_id', $expert->id)
                ->whereIn('status', $activeStatuses)
                ->lockForUpdate()
                ->get();

            foreach ($appointments as $appointment) {
                $fromStatus = $appointment->status;

                $appointment->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'cancellation_reason' => trim('Auto-cancelled: expert suspended. ' . $reason),
                    'cancelled_at' => now(),
                    'admin_id' => $actorId,
                ]);

                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'changed_by' => $actorId,
                    'from_status' => $fromStatus,
                    'to_status' => Appointment::STATUS_CANCELLED,
                    'notes' => 'Appointment auto-cancelled due to expert suspension.',
                    'changed_at' => now(),
                ]);
            }
        });
    }
}
