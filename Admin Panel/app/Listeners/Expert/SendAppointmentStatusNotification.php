<?php

namespace App\Listeners\Expert;

use App\Events\Expert\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Notifications\Expert\ExpertAppointmentNotification;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendAppointmentStatusNotification
 *
 * Queued listener. On every appointment status change, notifies the farmer
 * and records the expert-facing notification feed entry.
 */
class SendAppointmentStatusNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly NotificationCenterService $notificationService
    ) {}

    public function handle(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment->load(['user', 'expert.user']);
        $newStatus   = $event->newStatus;

        // 1) Notify the farmer
        $farmer = $appointment->user;
        if ($farmer) {
            $farmer->notify(new ExpertAppointmentNotification($appointment, $newStatus));
        }

        $expert = $appointment->expert;
        if ($expert) {
            $titleMap = [
                Appointment::STATUS_CONFIRMED   => 'Appointment accepted',
                Appointment::STATUS_REJECTED    => 'Appointment rejection sent',
                Appointment::STATUS_RESCHEDULE_REQUESTED => 'Reschedule request sent',
                Appointment::STATUS_RESCHEDULED => 'Appointment rescheduled',
                Appointment::STATUS_COMPLETED   => 'Appointment completed',
            ];

            $this->notificationService->notifyExpert(
                $expert,
                'appointment.status_updated',
                $titleMap[$newStatus] ?? "Appointment status: {$newStatus}",
                "Appointment #{$appointment->id} with {$farmer?->name} is now {$newStatus}.",
                [
                    'appointment_id' => $appointment->id,
                    'status' => $newStatus,
                    'action_url' => route('expert.appointments.show', $appointment->id),
                ],
                $appointment->user_id,
                route('expert.appointments.show', $appointment->id)
            );
        }
    }
}
