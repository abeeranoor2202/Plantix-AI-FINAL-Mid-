<?php

namespace App\Services\Shared;

use App\Events\Expert\AppointmentStatusChanged as ExpertAppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
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

            $proposedAt = Carbon::parse((string) $reschedule->proposed_scheduled_at);
            if ($proposedAt->isPast()) {
                throw new \DomainException('The proposed reschedule time is in the past.');
            }

            $currentSlot = AppointmentSlot::query()
                ->where('appointment_id', $appointment->id)
                ->lockForUpdate()
                ->first();

            if ($currentSlot) {
                $targetSlot = AppointmentSlot::query()
                    ->where('expert_id', $appointment->expert_id)
                    ->whereDate('date', $proposedAt->toDateString())
                    ->where('start_time', $proposedAt->format('H:i:s'))
                    ->lockForUpdate()
                    ->first();

                if (! $targetSlot) {
                    throw new \DomainException('The proposed reschedule time is not a valid slot.');
                }

                if ($targetSlot->is_booked && (int) $targetSlot->appointment_id !== (int) $appointment->id) {
                    throw new \DomainException('The proposed reschedule time conflicts with another booking.');
                }

                if ((int) $currentSlot->id !== (int) $targetSlot->id) {
                    $currentSlot->update([
                        'is_booked' => false,
                        'appointment_id' => null,
                    ]);
                }

                $targetSlot->update([
                    'is_booked' => true,
                    'appointment_id' => $appointment->id,
                ]);

                $appointment->update([
                    'scheduled_at' => $proposedAt,
                    'scheduled_date' => $targetSlot->date,
                    'start_time' => $targetSlot->start_time,
                    'end_time' => $targetSlot->end_time,
                    'status' => Appointment::STATUS_RESCHEDULED,
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
            }

            $duration = (int) ($appointment->duration_minutes ?? 60);
            $end = $proposedAt->copy()->addMinutes(max(1, $duration));

            $hasConflict = Appointment::query()
                ->where('expert_id', $appointment->expert_id)
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELLED,
                    Appointment::STATUS_REJECTED,
                    Appointment::STATUS_PAYMENT_FAILED,
                ])
                ->whereRaw('scheduled_at < ? AND DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$end, $proposedAt])
                ->exists();

            if ($hasConflict) {
                throw new \DomainException('The proposed reschedule time conflicts with another appointment.');
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