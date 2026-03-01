<?php

namespace App\Events\Appointment;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired whenever an appointment's status changes. */
class AppointmentStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string     $previousStatus,
        public readonly string     $newStatus,
        public readonly ?string    $note = null,
    ) {}
}
