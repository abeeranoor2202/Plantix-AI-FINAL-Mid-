<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpertLog — Immutable audit trail.
 *
 * Every status transition, admin action, and system event writes one row.
 * No updates, no deletes. Only created_at timestamp (no updated_at).
 */
class ExpertLog extends Model
{
    const UPDATED_AT = null;  // Append-only — no updated_at column

    protected $fillable = [
        'expert_id',
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'notes',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // Common action constants
    const ACTION_CREATED        = 'created';
    const ACTION_UNDER_REVIEW   = 'under_review';
    const ACTION_APPROVED       = 'approved';
    const ACTION_REJECTED       = 'rejected';
    const ACTION_SUSPENDED      = 'suspended';
    const ACTION_RESTORED       = 'restored';
    const ACTION_DEACTIVATED    = 'deactivated';
    const ACTION_PROFILE_UPDATED = 'profile_updated';
    const ACTION_RATING_UPDATED  = 'rating_updated';
    const ACTION_AVATAR_UPDATED  = 'avatar_updated';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeStatusChanges($query)
    {
        return $query->whereNotNull('from_status');
    }
}
