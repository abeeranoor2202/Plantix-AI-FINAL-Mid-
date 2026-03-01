<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 24-hour reminder — sent to both customer and expert.
 * Dispatched by the AppointmentReminderJob scheduled command.
 */
class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string      $recipientType = 'customer', // 'customer' | 'expert'
    ) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt    = $this->appointment;
        $dateStr = $appt->scheduled_at?->format('l, F j, Y g:i A') ?? 'TBD';

        if ($this->recipientType === 'expert') {
            $customer = optional($appt->user)->name ?? 'A farmer';
            return (new MailMessage)
                ->subject("Reminder: Appointment Tomorrow — #{$appt->id}")
                ->greeting("Hello {$notifiable->name},")
                ->line("Reminder: you have an appointment with **{$customer}** tomorrow.")
                ->line("**Date & Time:** {$dateStr}")
                ->when($appt->meeting_link, fn ($m) => $m->line("**Meeting Link:** {$appt->meeting_link}"))
                ->action('View Appointment', url('/expert/appointments/' . $appt->id));
        }

        $expert = optional(optional($appt->expert)->user)->name ?? 'Your Expert';
        return (new MailMessage)
            ->subject("Reminder: Your Appointment is Tomorrow — #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appointment with **{$expert}** is tomorrow!")
            ->line("**Date & Time:** {$dateStr}")
            ->when($appt->meeting_link, fn ($m) => $m->line("**Meeting Link:** [{$appt->meeting_link}]({$appt->meeting_link})"))
            ->action('View Appointment', url('/appointment/' . $appt->id))
            ->line('You can cancel up to 2 hours before the appointment.');
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
