<?php

namespace App\Listeners\Expert;

use App\Events\Appointment\AppointmentCreated;
use App\Services\Expert\ExpertNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAppointmentCreatedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly ExpertNotificationService $notifications
    ) {}

    public function handle(AppointmentCreated $event): void
    {
        $appointment = $event->appointment->loadMissing(['expert', 'user']);
        $expert = $appointment->expert;

        if (! $expert) {
            return;
        }

        $farmerName = $appointment->user?->name ?? 'a farmer';

        $this->notifications->notify(
            $expert,
            ExpertNotificationService::TYPE_APPOINTMENT_NEW,
            'New appointment request',
            'You have a new appointment request from ' . $farmerName . '.',
            [
                'appointment_id' => $appointment->id,
                'action_url' => route('expert.appointments.show', $appointment->id),
            ],
            $appointment->user_id,
            route('expert.appointments.show', $appointment->id)
        );
    }
}
