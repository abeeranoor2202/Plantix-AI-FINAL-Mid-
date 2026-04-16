<?php

namespace App\Services\Shared;

use App\Events\Expert\AppointmentStatusChanged as ExpertAppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\AppointmentReschedule;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class AppointmentStatusService
{
    public function proposeReschedule(
        Appointment $appointment,
        int $byUserId,
        DateTimeInterface $proposedTime,
        ?string $notes = null
    ): AppointmentReschedule {
        $this->assertTransition($appointment, Appointment::STATUS_RESCHEDULE_REQUESTED);

        return DB::transaction(function () use ($appointment, $byUserId, $proposedTime, $notes): AppointmentReschedule {
            $pending = AppointmentReschedule::where('appointment_id', $appointment->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->exists();

            if ($pending) {
                throw new \DomainException('A reschedule request is already pending for this appointment.');
            }

            $fromStatus = $appointment->status;

            $reschedule = AppointmentReschedule::create([
                'appointment_id'        => $appointment->id,
                'requested_by'          => $byUserId,
                'original_scheduled_at' => $appointment->scheduled_at,
                'proposed_scheduled_at' => $proposedTime,
                'reason'                => $notes,
                'status'                => 'pending',
            ]);

            $appointment->update([
                'status'                  => Appointment::STATUS_RESCHEDULE_REQUESTED,
                'reschedule_requested_at' => now(),
            ]);

            $this->recordStatusHistory($appointment, $byUserId, $fromStatus, Appointment::STATUS_RESCHEDULE_REQUESTED, $notes);
            AppointmentLog::record(
                $appointment,
                'reschedule_requested',
                $byUserId,
                $fromStatus,
                Appointment::STATUS_RESCHEDULE_REQUESTED,
                $notes,
                ['proposed_scheduled_at' => $proposedTime->format('Y-m-d H:i:s')]
            );

            $this->dispatchExpertStatusEvent($appointment, $byUserId, Appointment::STATUS_RESCHEDULE_REQUESTED);

            return $reschedule->fresh();
        });
    }

    public function acceptReschedule(Appointment $appointment, int $byUserId): Appointment
    {
        $this->assertTransition($appointment, Appointment::STATUS_RESCHEDULED);

        return DB::transaction(function () use ($appointment, $byUserId): Appointment {
            $reschedule = AppointmentReschedule::where('appointment_id', $appointment->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $reschedule) {
                throw new \DomainException('No pending reschedule request found for this appointment.');
            }

            $fromStatus = $appointment->status;

            $appointment->update([
                'scheduled_at' => $reschedule->proposed_scheduled_at,
                'status'       => Appointment::STATUS_RESCHEDULED,
            ]);

            $reschedule->update([
                'status'       => 'accepted',
                'responded_at' => now(),
            ]);

            $this->recordStatusHistory($appointment, $byUserId, $fromStatus, Appointment::STATUS_RESCHEDULED, $reschedule->reason);
            AppointmentLog::record(
                $appointment,
                'reschedule_accepted',
                $byUserId,
                $fromStatus,
                Appointment::STATUS_RESCHEDULED,
                $reschedule->reason
            );

            $this->dispatchExpertStatusEvent($appointment, $byUserId, Appointment::STATUS_RESCHEDULED);

            return $appointment->fresh();
        });
    }

    public function rejectReschedule(Appointment $appointment, int $byUserId, ?string $notes = null): Appointment
    {
        $this->assertTransition($appointment, Appointment::STATUS_CONFIRMED);

        return DB::transaction(function () use ($appointment, $byUserId, $notes): Appointment {
            $reschedule = AppointmentReschedule::where('appointment_id', $appointment->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $reschedule) {
                throw new \DomainException('No pending reschedule request found for this appointment.');
            }

            $fromStatus = $appointment->status;

            $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
            ]);

            $reschedule->update([
                'status'       => 'rejected',
                'responded_at' => now(),
            ]);

            $finalNotes = $notes ?? $reschedule->reason;
            $this->recordStatusHistory($appointment, $byUserId, $fromStatus, Appointment::STATUS_CONFIRMED, $finalNotes);
            AppointmentLog::record(
                $appointment,
                'reschedule_rejected',
                $byUserId,
                $fromStatus,
                Appointment::STATUS_CONFIRMED,
                $finalNotes
            );

            $this->dispatchExpertStatusEvent($appointment, $byUserId, Appointment::STATUS_CONFIRMED);

            return $appointment->fresh();
        });
    }

    private function assertTransition(Appointment $appointment, string $toStatus): void
    {
        $allowed = [
            Appointment::STATUS_CONFIRMED => [Appointment::STATUS_RESCHEDULE_REQUESTED],
            Appointment::STATUS_RESCHEDULE_REQUESTED => [Appointment::STATUS_RESCHEDULED, Appointment::STATUS_CONFIRMED],
        ];

        $transitions = $allowed[$appointment->status] ?? [];

        if (! in_array($toStatus, $transitions, true)) {
            throw new \DomainException(
                "Invalid reschedule transition: {$appointment->status} -> {$toStatus} for appointment #{$appointment->id}."
            );
        }
    }

    private function recordStatusHistory(
        Appointment $appointment,
        int $byUserId,
        string $fromStatus,
        string $toStatus,
        ?string $notes = null
    ): void {
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'changed_by'     => $byUserId,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'notes'          => $notes,
            'changed_at'     => now(),
        ]);
    }

    private function dispatchExpertStatusEvent(Appointment $appointment, int $byUserId, string $newStatus): void
    {
        $user = User::find($byUserId);

        if ($user) {
            event(new ExpertAppointmentStatusChanged($appointment->fresh(), $user, $newStatus));
        }
    }
}