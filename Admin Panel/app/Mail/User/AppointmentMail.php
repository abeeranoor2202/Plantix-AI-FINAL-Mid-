<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailables\Content;

class AppointmentMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Appointment $appointment,
        public readonly ?string $note = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return match ($this->appointment->status) {
            'pending_expert_approval' => "Appointment Request Sent — Booking #{$this->appointment->id}",
            'confirmed'               => "Appointment Confirmed — Booking #{$this->appointment->id}",
            'rejected'                => "Appointment Not Accepted — Booking #{$this->appointment->id}",
            'cancelled'               => "Appointment Cancelled — Booking #{$this->appointment->id}",
            'completed'               => "Consultation Complete — How was it? #{$this->appointment->id}",
            'reschedule_requested'    => "Reschedule Requested — Booking #{$this->appointment->id}",
            'rescheduled'             => "Appointment Rescheduled — Booking #{$this->appointment->id}",
            'payment_failed'          => "Payment Failed for Appointment #{$this->appointment->id}",
            default                   => "Appointment Update — #{$this->appointment->id}",
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.appointment',
            with: [
                'appointment'   => $this->appointment,
                'note'          => $this->note,
                'recipientEmail'=> $this->appointment->user?->email ?? '',
            ]
        );
    }
}
