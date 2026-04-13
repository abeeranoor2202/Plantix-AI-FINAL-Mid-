<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the thread owner when an expert or admin marks a reply as the official answer.
 */
class OfficialAnswerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ForumReply  $reply,
        private readonly ForumThread $thread,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expert    = $this->reply->user?->name ?? 'An expert';
        $threadUrl = url('/forum/' . $this->thread->slug);

        return (new MailMessage)
            ->subject('Your forum thread has an official answer')
            ->greeting('Hello, ' . ($notifiable->name ?? 'there') . '!')
            ->line("{$expert} has marked their reply as the Official Answer to your thread:")
            ->line('"' . $this->thread->title . '"')
            ->action('View Official Answer', $threadUrl)
            ->line('Log in to Plantix to read the full expert response.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'forum.official_answer',
            'title'      => 'Your thread has an official answer',
            'body'       => '"' . $this->thread->title . '"',
            'action_url' => '/forum/' . $this->thread->slug,
            'thread_id'  => $this->thread->id,
            'reply_id'   => $this->reply->id,
        ];
    }
}
