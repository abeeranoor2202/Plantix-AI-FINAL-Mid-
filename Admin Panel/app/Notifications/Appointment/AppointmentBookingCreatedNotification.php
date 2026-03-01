<?php

namespace App\Notifications\Appointment;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to customer immediately after booking is created (before payment). */
class AppointmentBookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $appt   = $this->appointment;
        $expert = optional(optional($appt->expert)->user)->name ?? 'an expert';

        return (new MailMessage)
            ->subject('Booking Received — Complete Payment to Confirm')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your booking request with **{$expert}** has been received.")
            ->line('**Complete your payment to confirm the appointment.** Until payment is received, your slot is not reserved.')
            ->action('Complete Payment', url('/appointment/' . $appt->id))
            ->line('If you have questions, contact our support team.');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'booking_created', 'appointment_id' => $this->appointment->id];
    }
}
