<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailables\Content;

/**
 * CustomerAppointmentScheduledMail
 * 
 * Notifies customer when an appointment is scheduled
 */
class CustomerAppointmentScheduledMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Appointment $appointment,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        $expert = $this->appointment->expert?->user?->name ?? 'Expert';
        return "📅 Appointment Scheduled with {$expert}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.appointment-scheduled',
            with: [
                'appointment' => $this->appointment,
                'expert'      => $this->appointment->expert?->user,
                'customer'    => $this->appointment->user,
            ]
        );
    }
}
