<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to admin when a Stripe payment fails or a refund is issued. */
class AdminPaymentFailureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string      $eventType = 'payment_failure', // 'payment_failure' | 'refund_issued'
    ) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt     = $this->appointment;
        $customer = optional($appt->user)->name ?? 'Unknown';
        $currency = strtoupper(config('plantix.currency_code', 'PKR'));
        $amount   = number_format((float) $appt->fee, 2);
        $pi       = $appt->stripe_payment_intent_id ?? 'N/A';

        if ($this->eventType === 'refund_issued') {
            $refundAmt = number_format((float) ($appt->refund_amount ?? $appt->fee), 2);
            return (new MailMessage)
                ->subject("[Admin] Refund Issued — Appointment #{$appt->id}")
                ->greeting("Hello {$notifiable->name},")
                ->line("A refund has been issued for appointment #{$appt->id}.")
                ->line("**Customer:** {$customer}")
                ->line("**Refund Amount:** {$currency} {$refundAmt}")
                ->line("**Stripe Refund ID:** {$appt->stripe_refund_id}")
                ->action('View Appointment', url('/admin/appointments/' . $appt->id));
        }

        return (new MailMessage)
            ->subject("[Admin] Payment Failed — Appointment #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A payment has failed for appointment #{$appt->id}.")
            ->line("**Customer:** {$customer}")
            ->line("**Amount:** {$currency} {$amount}")
            ->line("**Stripe PI:** {$pi}")
            ->action('View Appointment', url('/admin/appointments/' . $appt->id))
            ->line('The slot has been released. The customer may retry payment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => "admin_{$this->eventType}",
            'appointment_id' => $this->appointment->id,
        ];
    }
}
