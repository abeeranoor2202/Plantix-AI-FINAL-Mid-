<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatSession extends Model
{
    protected $fillable = [
        'user_id', 'session_key', 'context_type', 'context_data',
        'message_count', 'last_active_at',
    ];

    protected $casts = [
        'context_data'   => 'array',
        'last_active_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'session_id')->orderBy('created_at');
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(AiChatEscalation::class, 'session_id')->latest();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AiChatAudit::class, 'session_id')->latest('created_at');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function touch($attribute = null): bool
    {
        $this->update([
            'last_active_at' => now(),
            'message_count'  => $this->message_count + 1,
        ]);
        return true;
    }
}
