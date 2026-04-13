<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the thread owner when their thread is marked resolved by admin.
 */
class ThreadResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumThread $thread,
        private readonly ForumReply  $resolvedReply,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $threadUrl = url('/forum/' . $this->thread->slug);

        return (new MailMessage)
            ->subject('Your forum thread has been resolved')
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line('Great news — your forum thread has been marked as resolved:')
            ->line('"' . $this->thread->title . '"')
            ->action('View Resolved Thread', $threadUrl)
            ->line('An official answer has been selected for your question.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'forum.thread_resolved',
            'title'      => 'Your forum thread has been resolved',
            'body'       => '"' . $this->thread->title . '"',
            'action_url' => '/forum/' . $this->thread->slug,
            'thread_id'  => $this->thread->id,
            'reply_id'   => $this->resolvedReply->id,
        ];
    }
}
