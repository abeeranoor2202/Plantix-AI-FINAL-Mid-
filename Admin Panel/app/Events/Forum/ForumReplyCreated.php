<?php

namespace App\Events\Forum;

use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForumReplyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ForumThread $thread,
        public readonly ForumReply  $reply,
    ) {}
}
