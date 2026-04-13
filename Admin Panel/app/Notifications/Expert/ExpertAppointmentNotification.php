<?php

namespace App\Notifications\Expert;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ExpertAppointmentNotification
 *
 * Queued notification sent via database + email channels when
 * an appointment status changes.  Sent to the farmer (user).
 */
class ExpertAppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string      $eventType, // 'accepted' | 'rejected' | 'rescheduled' | 'completed'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->eventType) {
            'accepted'    => 'Your appointment has been accepted',
            'rejected'    => 'Your appointment has been rejected',
            'rescheduled' => 'Your appointment has been rescheduled',
            'completed'   => 'Your appointment is completed',
            default       => 'Appointment update',
        };

        $expertName = $this->appointment->expert?->user?->name ?? 'Expert';
        $dateTime   = $this->appointment->scheduled_at?->format('D, d M Y H:i');

        return (new MailMessage)
            ->subject("[Plantix AI] {$subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$subject} by {$expertName}.")
            ->line("Scheduled: {$dateTime}")
            ->when($this->eventType === 'accepted' && $this->appointment->meeting_link, fn ($m) =>
                $m->action('Join Meeting', $this->appointment->meeting_link)
            )
            ->when($this->eventType === 'rejected', fn ($m) =>
                $m->line("Reason: " . ($this->appointment->reject_reason ?? 'Not specified.'))
            )
            ->line('Thank you for using Plantix AI Expert Services.')
            ->salutation('— Plantix AI Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'appointment.' . $this->eventType,
            'appointment_id' => $this->appointment->id,
            'expert_name'    => $this->appointment->expert?->user?->name,
            'scheduled_at'   => $this->appointment->scheduled_at?->toISOString(),
            'message'        => "Your appointment has been {$this->eventType}.",
        ];
    }
}
