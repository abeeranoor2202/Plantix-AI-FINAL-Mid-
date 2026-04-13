<?php

namespace App\Notifications;

use App\Mail\CustomNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * CustomNotification
 *
 * Sent to users when admin sends a custom notification.
 * Email-only custom notification.
 * Queued for async processing.
 */
class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly ?string $actionUrl = null,
        private readonly bool $sendEmail = false,
    ) {
        $this->queue = 'notifications';
        $this->tries = 3;
    }

    /**
     * Determine which channels the notification should be sent through.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): CustomNotificationMail
    {
        return (new CustomNotificationMail(
            $notifiable,
            $this->title,
            $this->body,
            $this->actionUrl
        ))->onQueue('notifications');
    }

    /**
     * Get the array representation of the notification (database storage).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'custom',
            'title'      => $this->title,
            'body'       => $this->body,
            'action_url' => $this->actionUrl,
            'admin_id'   => auth('admin')->id() ?? null,
        ];
    }
}
