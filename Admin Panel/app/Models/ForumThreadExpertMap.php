<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumThreadExpertMap extends Model
{
    protected $table = 'forum_thread_expert_map';

    protected $fillable = [
        'forum_thread_id',
        'expert_id',
        'match_reason',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class, 'expert_id');
    }
}

