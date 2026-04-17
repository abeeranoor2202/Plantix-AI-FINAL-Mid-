<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'message_id',
        'event_type',
        'actor_user_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(AiChatMessage::class, 'message_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
