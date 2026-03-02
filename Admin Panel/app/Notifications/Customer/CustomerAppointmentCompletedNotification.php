<?php

namespace App\Notifications\Customer;

use App\Mail\User\CustomerAppointmentCompletedMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * CustomerAppointmentCompletedNotification
 * 
 * Alerts customer after appointment is completed, invites them to leave a review
 */
class CustomerAppointmentCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly ?string $expertNotes = null,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Email only (as per requirement)
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): CustomerAppointmentCompletedMail
    {
        return new CustomerAppointmentCompletedMail(
            $this->appointment,
            $this->expertNotes,
        );
    }
}
