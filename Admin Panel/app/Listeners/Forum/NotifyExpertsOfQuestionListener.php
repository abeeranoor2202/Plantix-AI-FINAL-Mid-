<?php

namespace App\Listeners\Forum;

use App\Events\Forum\ForumQuestionCreated;
use App\Mail\Expert\ForumQuestionMail;
use App\Models\ExpertNotificationLog;
use App\Models\ForumLog;
use App\Services\Forum\ForumDistributionService;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyExpertsOfQuestionListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(
        private readonly ForumDistributionService $distributionService,
        private readonly NotificationCenterService $notificationCenter,
    ) {}

    public function handle(ForumQuestionCreated $event): void
    {
        $thread = $event->thread->fresh(['category', 'user']);
        if (! $thread) {
            return;
        }

        $experts = $this->distributionService->resolveRelevantExperts($thread);
        $tags = implode(', ', (array) ($thread->tags ?? []));

        foreach ($experts as $expert) {
            $expertUser = $expert->user;
            if (! $expertUser || (int) $expertUser->id === (int) $event->author->id) {
                continue;
            }

            $alreadyLogged = ExpertNotificationLog::query()
                ->where('expert_id', $expert->id)
                ->where('type', 'forum.thread_assigned')
                ->where('related_id', $thread->id)
                ->exists();

            if ($alreadyLogged) {
                continue;
            }

            $actionUrl = route('forum.thread', $thread->slug);
            $message = "New question related to your expertise: \"{$thread->title}\"";

            $this->notificationCenter->notify(
                $event->author,
                $expertUser,
                'forum.thread_assigned',
                'New question related to your expertise',
                $actionUrl,
                ['thread_id' => $thread->id, 'tags' => $thread->tags ?? []],
                'Forum question assigned',
                "forum.thread_assigned:{$thread->id}:{$expert->id}"
            );

            $this->notificationCenter->notifyExpert(
                $expert,
                'forum.thread_assigned',
                'New question related to your expertise',
                $message,
                ['thread_id' => $thread->id, 'tags' => $thread->tags ?? [], 'action_url' => $actionUrl],
                $thread->id,
                $actionUrl
            );

            try {
                $preferences = (array) ($expertUser->notification_preferences ?? []);
                $allowEmail = ! array_key_exists('forum_email', $preferences) || (bool) $preferences['forum_email'];
                if ($allowEmail && ! empty($expertUser->email)) {
                    Mail::to($expertUser->email)->queue(new ForumQuestionMail($thread, $expert));
                }
            } catch (\Throwable $e) {
                Log::warning('forum expert email notify failed', ['error' => $e->getMessage(), 'expert_id' => $expert->id]);
            }
        }

        ForumLog::record(
            $event->author->id,
            ForumLog::ACTION_THREAD_DISTRIBUTE,
            $thread->id,
            null,
            [
                'distributed_experts_count' => $experts->count(),
                'tags' => $thread->tags ?? [],
                'category' => optional($thread->category)->name,
                'tags_summary' => $tags,
            ]
        );
    }
}

