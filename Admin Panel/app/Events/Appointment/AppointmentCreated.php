<?php

namespace App\Events\Appointment;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when a new appointment booking is created by a customer. */
class AppointmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Appointment $appointment) {}
}
