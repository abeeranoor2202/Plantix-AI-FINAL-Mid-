<?php

namespace App\Notifications\Expert;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ExpertNewAppointmentRequestNotification
 *
 * Sent to the EXPERT when a farmer submits a new appointment request.
 */
class ExpertNewAppointmentRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly \App\Models\Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $farmerName = $this->appointment->user?->name ?? 'A Farmer';
        $dateTime   = $this->appointment->scheduled_at?->format('D, d M Y H:i');

        return (new MailMessage)
            ->subject('[Plantix AI] New Appointment Request')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$farmerName} has requested an appointment with you.")
            ->line("Topic: " . ($this->appointment->topic ?? 'General Consultation'))
            ->line("Requested Date/Time: {$dateTime}")
            ->action('Review Appointment', url('/expert/appointments/' . $this->appointment->id))
            ->line('Please respond within 24 hours.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'appointment.new',
            'appointment_id' => $this->appointment->id,
            'farmer_name'    => $this->appointment->user?->name,
            'scheduled_at'   => $this->appointment->scheduled_at?->toISOString(),
            'message'        => 'New appointment request received.',
        ];
    }
}
