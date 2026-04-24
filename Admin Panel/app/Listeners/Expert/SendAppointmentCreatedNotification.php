<?php

namespace App\Listeners\Expert;

use App\Events\Appointment\AppointmentCreated;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAppointmentCreatedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly NotificationCenterService $notifications
    ) {}

    public function handle(AppointmentCreated $event): void
    {
        // Re-fetch fresh from DB to guarantee expert_id is the latest persisted value.
        // The serialized model in the event may be stale if the appointment was
        // updated between dispatch and queue processing.
        $appointment = \App\Models\Appointment::with(['expert', 'user'])
            ->find($event->appointment->id);

        if (! $appointment) {
            return;
        }

        $expert = $appointment->expert;

        if (! $expert) {
            return;
        }

        $farmerName = $appointment->user?->name ?? 'a farmer';

        $this->notifications->notifyExpert(
            $expert,
            'appointment.new_request',
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
