<?php

namespace App\Listeners\Forum;

use App\Events\Forum\ForumQuestionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class UpdateForumFeedListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function handle(ForumQuestionCreated $event): void
    {
        Cache::forget('forum.pinned_threads');
        Cache::forget('forum.category_counts');
        Cache::forget('forum.latest_questions');
    }
}

