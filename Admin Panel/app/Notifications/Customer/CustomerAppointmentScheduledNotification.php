<?php

namespace App\Notifications\Customer;

use App\Mail\User\CustomerAppointmentScheduledMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * CustomerAppointmentScheduledNotification
 * 
 * Alerts customer after appointment is successfully scheduled/confirmed
 */
class CustomerAppointmentScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment)
    {
        $this->onQueue('emails');
    }

    /**
     * Email only (as per requirement)
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): CustomerAppointmentScheduledMail
    {
        return new CustomerAppointmentScheduledMail($this->appointment);
    }
}
