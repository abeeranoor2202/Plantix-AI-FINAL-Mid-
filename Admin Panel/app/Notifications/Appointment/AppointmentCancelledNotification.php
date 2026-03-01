<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to customer (and expert when relevant) when appointment is cancelled. */
class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt   = $this->appointment;
        $reason = $appt->cancellation_reason ?? 'No reason provided.';

        $mail = (new MailMessage)
            ->subject("Appointment Cancelled — #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appointment (#{$appt->id}) has been cancelled.")
            ->line("**Reason:** {$reason}");

        if ($appt->is_refunded) {
            $currency = strtoupper(config('plantix.currency_code', 'PKR'));
            $amount   = number_format((float) ($appt->refund_amount ?? $appt->fee), 2);
            $mail->line("A refund of **{$currency} {$amount}** has been issued and will appear in 5–10 business days.");
        }

        return $mail
            ->action('Book a New Appointment', url('/appointment/book'))
            ->line('We apologize for any inconvenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_cancelled',
            'appointment_id' => $this->appointment->id,
            'reason'         => $this->appointment->cancellation_reason,
        ];
    }
}
