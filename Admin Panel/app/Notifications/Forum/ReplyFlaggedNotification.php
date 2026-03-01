<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the reply author when their reply is flagged for moderation.
 */
class ReplyFlaggedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumReply $reply,
        private readonly User       $reporter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reply = $this->reply;
        $reply->loadMissing('thread');
        $threadUrl = url('/forum/' . ($reply->thread->slug ?? $reply->thread_id));

        return (new MailMessage)
            ->subject('Your reply has been flagged for review')
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line('Your reply in the following thread has been flagged by our community for moderator review:')
            ->line('"' . ($reply->thread->title ?? '') . '"')
            ->action('View Thread', $threadUrl)
            ->line('Our moderation team will review the report. No action is required from you at this time.')
            ->line('If your reply did not violate our community guidelines, it will remain visible.');
    }

    public function toArray(object $notifiable): array
    {
        $this->reply->loadMissing('thread');

        return [
            'type'       => 'forum.reply_flagged',
            'title'      => 'Your reply has been flagged for review',
            'body'       => '"' . ($this->reply->thread?->title ?? '') . '"',
            'action_url' => '/forum/' . ($this->reply->thread?->slug ?? $this->reply->thread_id),
            'reply_id'   => $this->reply->id,
            'thread_id'  => $this->reply->thread_id,
        ];
    }
}
