<?php

namespace App\Notifications\Expert;

use App\Models\Expert;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ExpertForumMentionNotification
 *
 * Queued notification sent to an expert when they are mentioned in the forum
 * or when someone replies to their forum answer.
 */
class ExpertForumMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumThread $thread,
        private readonly ForumReply  $reply,
        private readonly string      $mentionedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Plantix AI] You have a new forum notification')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->mentionedBy} replied to the forum thread: \"{$this->thread->title}\"")
            ->action('View Thread', url("/expert/forum/{$this->thread->id}"))
            ->line('Keep the conversation going!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'forum.mention',
            'thread_id'   => $this->thread->id,
            'thread_title'=> $this->thread->title,
            'reply_id'    => $this->reply->id,
            'mentioned_by'=> $this->mentionedBy,
            'message'     => "{$this->mentionedBy} replied in \"{$this->thread->title}\"",
        ];
    }
}
