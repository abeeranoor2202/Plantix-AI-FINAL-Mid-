<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to customer when Stripe confirms payment → appointment moves to pending_expert_approval. */
class AppointmentPaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt       = $this->appointment;
        $dateStr    = $appt->scheduled_at?->format('l, F j, Y g:i A') ?? 'TBD';
        $expert     = optional(optional($appt->expert)->user)->name ?? 'Your Expert';
        $amount     = number_format((float) $appt->fee, 2);
        $currency   = strtoupper(config('plantix.currency_code', 'PKR'));

        return (new MailMessage)
            ->subject("Payment Confirmed — Appointment #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your payment of **{$currency} {$amount}** has been received.")
            ->line("Your appointment with **{$expert}** is now pending expert acceptance.")
            ->line("**Scheduled:** {$dateStr}")
            ->action('View Appointment', url('/appointment/' . $appt->id))
            ->line('You will receive another email once the expert confirms or reschedules.');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'payment_success', 'appointment_id' => $this->appointment->id, 'amount' => $this->appointment->fee];
    }
}
