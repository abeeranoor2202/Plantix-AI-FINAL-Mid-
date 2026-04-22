<?php

namespace App\Listeners\Forum;

use App\Events\Forum\ForumQuestionCreated;
use App\Models\ForumThreadExpertMap;
use App\Services\Forum\ForumDistributionService;
use Illuminate\Contracts\Queue\ShouldQueue;

class MapQuestionExpertsListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(
        private readonly ForumDistributionService $distributionService,
    ) {}

    public function handle(ForumQuestionCreated $event): void
    {
        $thread = $event->thread->fresh(['category']);
        if (! $thread) {
            return;
        }

        $experts = $this->distributionService->resolveRelevantExperts($thread);
        foreach ($experts as $expert) {
            ForumThreadExpertMap::query()->firstOrCreate(
                [
                    'forum_thread_id' => $thread->id,
                    'expert_id' => $expert->id,
                ],
                [
                    'match_reason' => 'tag_specialization_match',
                ]
            );
        }
    }
}

