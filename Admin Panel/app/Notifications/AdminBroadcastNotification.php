<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to admin when broadcasting to a user segment.
 * Section 14 – Notification Engine: Admin Broadcast
 *
 * Mail-only broadcast notification.
 */
class AdminBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly array $data,
        private readonly bool  $sendEmail = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->data['title'])
            ->greeting('Hello, ' . ($notifiable->name ?? 'User') . '!')
            ->line($this->data['body']);

        if (! empty($this->data['action_url'])) {
            $mail->action('View Details', $this->data['action_url']);
        }

        return $mail->line('Thank you for using Plantix AI.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'broadcast',
            'title'      => $this->data['title'],
            'body'       => $this->data['body'],
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}
