<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Section 14 – Notification Trigger Map:
 *  Reschedule proposed → Customer → Email + In-app
 *
 * Sent to:
 *  - Customer: when expert proposes a new scheduled_at
 *  - Expert:   when customer accepts or rejects the reschedule
 */
class AppointmentRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $event  'proposed' | 'accepted' | 'rejected'
     */
    public function __construct(
        private readonly Appointment         $appointment,
        private readonly AppointmentReschedule $reschedule,
        private readonly string              $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expertName   = $this->appointment->expert?->user?->name ?? 'Your Expert';
        $oldTime      = $this->reschedule->original_scheduled_at?->format('M j, Y g:i A');
        $newTime      = $this->reschedule->proposed_scheduled_at?->format('M j, Y g:i A');
        $bookingUrl   = url('/appointment/' . $this->appointment->id);

        return match ($this->event) {
            'proposed' => (new MailMessage)
                ->subject('Appointment Reschedule Request')
                ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
                ->line("{$expertName} has requested to reschedule your appointment.")
                ->line("**Current time:** {$oldTime}")
                ->line("**Proposed new time:** {$newTime}")
                ->when($this->reschedule->reason, fn ($m) => $m->line("**Reason:** {$this->reschedule->reason}"))
                ->action('Accept or Reject', $bookingUrl)
                ->line('Please respond within 24 hours, or the appointment will remain at its original time.'),

            'accepted' => (new MailMessage)
                ->subject('Reschedule Accepted')
                ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
                ->line("Your appointment reschedule request has been accepted.")
                ->line("**New scheduled time:** {$newTime}")
                ->action('View Appointment', $bookingUrl),

            'rejected' => (new MailMessage)
                ->subject('Reschedule Rejected')
                ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
                ->line("Your reschedule proposal was declined. The appointment remains at the original time.")
                ->line("**Original time:** {$oldTime}")
                ->action('View Appointment', $bookingUrl),

            default => new MailMessage,
        };
    }

    public function toArray(object $notifiable): array
    {
        $expertName = $this->appointment->expert?->user?->name ?? 'Your Expert';
        $newTime    = $this->reschedule->proposed_scheduled_at?->format('M j, Y g:i A');

        $titleMap = [
            'proposed' => "Reschedule request from {$expertName}",
            'accepted' => 'Your reschedule request was accepted',
            'rejected' => 'Your reschedule proposal was declined',
        ];

        return [
            'type'           => 'appointment_reschedule',
            'event'          => $this->event,
            'title'          => $titleMap[$this->event] ?? 'Appointment updated',
            'body'           => $this->event === 'proposed'
                ? "Proposed new time: {$newTime}"
                : "Appointment ID #{$this->appointment->id}",
            'action_url'     => '/appointment/' . $this->appointment->id,
            'appointment_id' => $this->appointment->id,
            'reschedule_id'  => $this->reschedule->id,
        ];
    }
}
