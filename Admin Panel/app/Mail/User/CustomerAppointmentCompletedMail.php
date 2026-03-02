<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailables\Content;

/**
 * CustomerAppointmentCompletedMail
 * 
 * Notifies customer when appointment is completed, with option for review
 */
class CustomerAppointmentCompletedMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Appointment $appointment,
        public readonly ?string $expertNotes = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        $expert = $this->appointment->expert?->user?->name ?? 'Expert';
        return "✅ Your Appointment with {$expert} is Complete";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.appointment-completed',
            with: [
                'appointment'  => $this->appointment,
                'expert'       => $this->appointment->expert?->user,
                'customer'     => $this->appointment->user,
                'expertNotes'  => $this->expertNotes,
            ]
        );
    }
}
