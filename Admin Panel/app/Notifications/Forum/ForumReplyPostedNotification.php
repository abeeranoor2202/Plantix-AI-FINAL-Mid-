<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the thread owner when any reply is posted.
 * All forum notifications are queued (ShouldQueue).
 * They use the mail channel only (SMTP, no Firebase).
 */
class ForumReplyPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumReply  $reply,
        private readonly ForumThread $thread,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $author    = $this->reply->user?->name ?? 'A user';
        $threadUrl = url('/forum/' . $this->thread->slug);
        $isExpert  = $this->reply->is_expert_reply;

        return (new MailMessage)
            ->subject($isExpert
                ? "Expert replied to your forum thread"
                : "{$author} replied to your forum thread")
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line($isExpert
                ? "An expert has posted a reply to your thread:"
                : "{$author} has posted a reply to your thread:")
            ->line('"' . $this->thread->title . '"')
            ->action('View Thread', $threadUrl)
            ->line('Log in to Plantix to read the full reply.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'forum.reply_posted',
            'title'      => $this->reply->is_expert_reply
                ? 'Expert replied to your thread'
                : ($this->reply->user?->name ?? 'Someone') . ' replied to your thread',
            'body'       => '"' . $this->thread->title . '"',
            'action_url' => '/forum/' . $this->thread->slug,
            'thread_id'  => $this->thread->id,
            'reply_id'   => $this->reply->id,
        ];
    }
}
