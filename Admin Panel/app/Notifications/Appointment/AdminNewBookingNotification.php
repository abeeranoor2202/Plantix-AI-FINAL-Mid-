<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to all admin accounts when a new appointment booking is created. */
class AdminNewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt     = $this->appointment;
        $customer = optional($appt->user)->name ?? 'Unknown';
        $expert   = optional(optional($appt->expert)->user)->name ?? 'Unassigned';
        $currency = strtoupper(config('plantix.currency_code', 'PKR'));
        $amount   = number_format((float) $appt->fee, 2);

        return (new MailMessage)
            ->subject("[Admin] New Appointment Booking — #{$appt->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new appointment has been created.")
            ->line("**Customer:** {$customer}")
            ->line("**Expert:** {$expert}")
            ->line("**Amount:** {$currency} {$amount}")
            ->line("**Status:** {$appt->status}")
            ->action('View in Admin', url('/admin/appointments/' . $appt->id));
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'admin_new_booking', 'appointment_id' => $this->appointment->id];
    }
}
