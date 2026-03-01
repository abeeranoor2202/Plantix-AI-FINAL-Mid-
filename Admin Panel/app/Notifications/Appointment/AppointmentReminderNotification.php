<?php

namespace App\Notifications\Appointment;

use App\Mail\Expert\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * 24-hour reminder — sent to both customer and expert.
 * Dispatched by the AppointmentReminderJob scheduled command.
 * Delivers via our branded AppointmentReminderMail template + database channel.
 */
class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string      $recipientType = 'customer', // 'customer' | 'expert'
    ) {
        $this->onQueue('emails');
    }

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): AppointmentReminderMail
    {
        return new AppointmentReminderMail(
            appointment:    $this->appointment,
            hoursAway:      24,
            recipientRole:  $this->recipientType === 'expert' ? 'expert' : 'user',
            recipientName:  $notifiable->name ?? '',
            recipientEmail: $notifiable->email ?? '',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'recipient_type' => $this->recipientType,
        ];
    }
}
