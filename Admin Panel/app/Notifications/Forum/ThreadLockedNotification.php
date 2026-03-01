<?php

namespace App\Notifications\Forum;

use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the thread owner when an admin locks their thread.
 */
class ThreadLockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumThread $thread,
        private readonly ?string     $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your forum thread has been locked')
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line('An administrator has locked your forum thread:')
            ->line('"' . $this->thread->title . '"')
            ->line('Locked threads cannot receive new replies.');

        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail->line('Contact support if you believe this is an error.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'forum.thread_locked',
            'title'      => 'Your forum thread has been locked',
            'body'       => '"' . $this->thread->title . '"',
            'action_url' => '/forum/' . $this->thread->slug,
            'thread_id'  => $this->thread->id,
            'reason'     => $this->reason,
        ];
    }
}
