<?php

namespace App\Mail\Expert;

use App\Mail\PlantixBaseMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailables\Content;

class AppointmentReminderMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Appointment $appointment,
        public readonly int         $hoursAway,        // 24 or 1
        public readonly string      $recipientRole,    // expert | user
        public readonly string      $recipientName,
        public readonly string      $recipientEmail,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return "⏰ Reminder: Consultation in {$this->hoursAway} hour(s) — Booking #{$this->appointment->id}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expert.reminder',
            with: [
                'appointment'   => $this->appointment,
                'hoursAway'     => $this->hoursAway,
                'recipientRole' => $this->recipientRole,
                'recipientName' => $this->recipientName,
                'recipientEmail'=> $this->recipientEmail,
            ]
        );
    }
}
