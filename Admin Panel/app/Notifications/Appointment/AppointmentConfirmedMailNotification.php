<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to customer when expert/admin confirms the appointment. */
class AppointmentConfirmedMailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt    = $this->appointment;
        $dateStr = $appt->scheduled_at?->format('l, F j, Y g:i A') ?? 'TBD';
        $expert  = optional(optional($appt->expert)->user)->name ?? 'Your Expert';

        $mail = (new MailMessage)
            ->subject("Appointment Confirmed — {$appt->scheduled_at?->format('M j')}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appointment with **{$expert}** is **confirmed**.")
            ->line("**Date & Time:** {$dateStr}");

        if ($appt->meeting_link) {
            $mail->line("**Meeting Link:** [{$appt->meeting_link}]({$appt->meeting_link})");
        }

        return $mail
            ->action('View Appointment', url('/appointment/' . $appt->id))
            ->line('Please be ready at the scheduled time. You can cancel up to 24 hours before.');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'appointment_confirmed', 'appointment_id' => $this->appointment->id];
    }
}
