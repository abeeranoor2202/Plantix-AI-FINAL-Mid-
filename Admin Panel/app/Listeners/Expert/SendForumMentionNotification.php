<?php

namespace App\Listeners\Expert;

use App\Events\Expert\ExpertMentionedInForum;
use App\Notifications\Expert\ExpertForumMentionNotification;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendForumMentionNotification
 *
 * Queued listener. Notifies the expert via email + database when
 * a farmer replies to their forum answer or mentions them.
 */
class SendForumMentionNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly NotificationCenterService $notificationService
    ) {}

    public function handle(ExpertMentionedInForum $event): void
    {
        $expert      = $event->expert->load('user');
        $thread      = $event->thread;
        $reply       = $event->reply->load('user');
        $mentionedBy = $reply->user?->name ?? 'A farmer';

        // 1) Laravel notification (email + database)
        $expert->user->notify(
            new ExpertForumMentionNotification($thread, $reply, $mentionedBy)
        );

        $this->notificationService->notifyExpert(
            $expert,
            'forum.reply_on_answer',
            "{$mentionedBy} replied in: {$thread->title}",
            $reply->body,
            [
                'thread_id' => $thread->id,
                'reply_id' => $reply->id,
                'action_url' => route('expert.forum.show', $thread->id),
            ],
            $reply->user_id,
            route('expert.forum.show', $thread->id)
        );
    }
}
