<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'session_id', 'role', 'content', 'metadata', 'model_used', 'tokens_used',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }
}
