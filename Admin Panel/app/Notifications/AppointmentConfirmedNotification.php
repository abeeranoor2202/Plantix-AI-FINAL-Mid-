<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to customer when appointment is confirmed by expert or admin.
 *
 * FIX: Previous version referenced non-existent fields
 *   $appt->appointment_date / $appt->appointment_time
 * Corrected to use $appt->scheduled_at (the actual model field).
 */
class AppointmentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appt    = $this->appointment;
        $dateStr = $appt->scheduled_at?->format('l, F j, Y') ?? 'TBD';
        $time    = $appt->scheduled_at?->format('g:i A') ?? 'TBD';
        $expert  = optional(optional($appt->expert)->user)->name ?? 'Your Expert';

        return (new MailMessage())
            ->subject("Appointment Confirmed — {$dateStr}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appointment has been **confirmed**!")
            ->line("**Date:** {$dateStr}")
            ->line("**Time:** {$time}")
            ->line("**Expert:** {$expert}")
            ->when($appt->meeting_link, fn ($m) => $m->line("**Meeting Link:** {$appt->meeting_link}"))
            ->action('View Appointment', url('/appointment/' . $appt->id))
            ->line('Please be available at the scheduled time. You may cancel up to 24 hours before.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_confirmed',
            'appointment_id' => $this->appointment->id,
            'message'        => "Your appointment on {$this->appointment->scheduled_at?->format('M j, Y g:i A')} is confirmed.",
            'scheduled_at'   => $this->appointment->scheduled_at?->toISOString(),
        ];
    }
}

