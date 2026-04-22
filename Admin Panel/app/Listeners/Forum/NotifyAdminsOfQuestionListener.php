<?php

namespace App\Listeners\Forum;

use App\Events\Forum\ForumQuestionCreated;
use App\Models\User;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfQuestionListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(
        private readonly NotificationCenterService $notificationCenter,
    ) {}

    public function handle(ForumQuestionCreated $event): void
    {
        $thread = $event->thread->fresh(['category']);
        if (! $thread) {
            return;
        }

        $admins = User::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->get();

        foreach ($admins as $admin) {
            $this->notificationCenter->notify(
                $event->author,
                $admin,
                'forum.thread.created',
                'A new forum question was posted and distributed.',
                route('admin.forum.threads.show', $thread->id),
                [
                    'thread_id' => $thread->id,
                    'category' => optional($thread->category)->name,
                    'tags' => $thread->tags ?? [],
                ],
                'New forum question',
                "forum.thread.created.admin:{$thread->id}:{$admin->id}"
            );
        }
    }
}

