<?php

namespace App\Mail\Expert;

use App\Mail\PlantixBaseMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailables\Content;

class ExpertAppointmentMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Appointment $appointment,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return match ($this->appointment->status) {
            'pending_expert_approval' => "📬 New Appointment Request — Booking #{$this->appointment->id}",
            'confirmed'               => "Appointment Confirmed — Booking #{$this->appointment->id}",
            'cancelled'               => "Appointment Cancelled by Customer — #{$this->appointment->id}",
            'completed'               => "Session Completed — #{$this->appointment->id}",
            'reschedule_requested'    => "Reschedule Requested — Booking #{$this->appointment->id}",
            default                   => "Appointment Update — #{$this->appointment->id}",
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expert.appointment',
            with: [
                'appointment'   => $this->appointment,
                'recipientEmail'=> $this->appointment->expert?->user?->email ?? '',
            ]
        );
    }
}
