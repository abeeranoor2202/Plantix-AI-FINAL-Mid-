<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to the expert's user account when a new paid booking arrives. */
class ExpertNewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt     = $this->appointment;
        $customer = optional($appt->user)->name ?? 'A farmer';
        $dateStr  = $appt->scheduled_at?->format('l, F j, Y g:i A') ?? 'TBD';
        $currency = strtoupper(config('plantix.currency_code', 'PKR'));
        $amount   = number_format((float) $appt->fee, 2);

        return (new MailMessage)
            ->subject("New Appointment Request — #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("**{$customer}** has booked an appointment with you.")
            ->line("**Date & Time:** {$dateStr}")
            ->line("**Fee Paid:** {$currency} {$amount}")
            ->when($appt->topic, fn ($m) => $m->line("**Topic:** {$appt->topic}"))
            ->when($appt->notes, fn ($m) => $m->line("**Notes:** {$appt->notes}"))
            ->action('Accept or Reject', url('/expert/appointments/' . $appt->id))
            ->line('Please respond within 24 hours.');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'expert_new_booking', 'appointment_id' => $this->appointment->id];
    }
}
