<?php

namespace App\Listeners\Expert;

use App\Events\Forum\OfficialAnswerMarked;
use App\Models\Expert;
use App\Services\Expert\ExpertNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendForumOfficialMarkedNotification implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(
        private readonly ExpertNotificationService $notifications
    ) {}

    public function handle(OfficialAnswerMarked $event): void
    {
        $reply = $event->reply;
        $thread = $event->thread;

        $expert = null;
        if ($reply->expert_id) {
            $expert = Expert::query()->find($reply->expert_id);
        }

        if (! $expert) {
            return;
        }

        $this->notifications->notify(
            $expert,
            ExpertNotificationService::TYPE_FORUM_HELPFUL,
            'Your reply was marked helpful',
            'Your reply in "' . $thread->title . '" is now marked as official helpful guidance.',
            [
                'thread_id' => $thread->id,
                'reply_id' => $reply->id,
                'action_url' => route('expert.forum.show', $thread->id),
            ],
            $thread->user_id,
            route('expert.forum.show', $thread->id)
        );
    }
}
