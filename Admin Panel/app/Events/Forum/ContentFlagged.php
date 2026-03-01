<?php

namespace App\Events\Forum;

use App\Models\ForumFlag;
use App\Models\ForumThread;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentFlagged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ForumFlag   $flag,
        public readonly ForumThread $thread,
    ) {}
}
