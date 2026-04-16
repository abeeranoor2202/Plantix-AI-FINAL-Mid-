<?php

namespace App\Listeners\Expert;

use App\Events\Expert\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Notifications\Expert\ExpertAppointmentNotification;
use App\Services\Expert\ExpertNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendAppointmentStatusNotification
 *
 * Queued listener.  On every appointment status change:
 *   1. Notifies the farmer via database + email (ExpertAppointmentNotification)
 *   2. Logs the event in expert_notification_logs for the expert panel nav badge
 */
class SendAppointmentStatusNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly ExpertNotificationService $notificationService
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

        // 2) Log in expert panel (for nav badge)
        $expert = $appointment->expert;
        if ($expert) {
            $titleMap = [
                Appointment::STATUS_CONFIRMED   => 'Appointment accepted',
                Appointment::STATUS_REJECTED    => 'Appointment rejection sent',
                Appointment::STATUS_RESCHEDULE_REQUESTED => 'Reschedule request sent',
                Appointment::STATUS_RESCHEDULED => 'Appointment rescheduled',
                Appointment::STATUS_COMPLETED   => 'Appointment completed',
            ];

            $this->notificationService->notify(
                $expert,
                ExpertNotificationService::TYPE_APPOINTMENT_UPDATE,
                $titleMap[$newStatus] ?? "Appointment status: {$newStatus}",
                "Appointment #{$appointment->id} with {$farmer?->name} is now {$newStatus}.",
                ['appointment_id' => $appointment->id, 'status' => $newStatus]
            );
        }
    }
}
