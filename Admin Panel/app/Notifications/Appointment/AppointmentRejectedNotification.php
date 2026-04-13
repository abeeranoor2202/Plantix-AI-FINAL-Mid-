<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to customer when expert rejects the appointment. Refund auto-issued if paid. */
class AppointmentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt   = $this->appointment;
        $expert = optional(optional($appt->expert)->user)->name ?? 'the expert';
        $reason = $appt->reject_reason ?? 'No reason provided.';

        $mail = (new MailMessage)
            ->subject("Appointment Rejected — #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Unfortunately, **{$expert}** has rejected your appointment request.")
            ->line("**Reason:** {$reason}");

        if ($appt->is_refunded) {
            $currency = strtoupper(config('plantix.currency_code', 'PKR'));
            $amount   = number_format((float) $appt->refund_amount ?? $appt->fee, 2);
            $mail->line("A refund of **{$currency} {$amount}** has been issued and will appear in 5–10 business days.");
        }

        return $mail
            ->action('Book Another Appointment', url('/appointment/book'))
            ->line('We apologize for the inconvenience.');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'appointment_rejected', 'appointment_id' => $this->appointment->id];
    }
}
