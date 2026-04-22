<?php

namespace App\Events\Forum;

use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForumQuestionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ForumThread $thread,
        public readonly User $author,
    ) {}
}

