<?php

namespace App\Notifications;

use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Section 14 – Notification Trigger Map:
 *  Forum reply added  → Thread owner → In-app
 *  Expert reply added → Thread owner → Email + In-app
 *
 * Sent to the forum thread owner when any reply is posted.
 * If the reply is from an expert, email is also included.
 */
class ForumReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumReply   $reply,
        private readonly ForumThread  $thread,
    ) {}

    public function via(object $notifiable): array
    {
        // Expert replies → email + in-app; regular replies → in-app only
        return $this->reply->is_expert_reply
            ? ['database', 'mail']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $author     = $this->reply->user?->name ?? 'An Expert';
        $threadUrl  = url('/forum/' . $this->thread->id);

        return (new MailMessage)
            ->subject("An expert replied to your forum thread")
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line("{$author} has posted an expert answer to your thread:")
            ->line("**\"{$this->thread->title}\"**")
            ->action('View Thread', $threadUrl)
            ->line('Log into Plantix AI to view the full response.');
    }

    public function toArray(object $notifiable): array
    {
        $author = $this->reply->user?->name ?? 'Someone';

        return [
            'type'        => 'forum_reply',
            'title'       => $this->reply->is_expert_reply
                ? "Expert replied to your thread"
                : "{$author} replied to your thread",
            'body'        => "\"{$this->thread->title}\"",
            'action_url'  => '/forum/' . $this->thread->id,
            'thread_id'   => $this->thread->id,
            'reply_id'    => $this->reply->id,
            'is_expert'   => $this->reply->is_expert_reply,
        ];
    }
}
