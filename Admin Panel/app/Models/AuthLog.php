<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuthLog — immutable authentication event record.
 *
 * Events: login_success | login_failed | logout | password_changed |
 *         password_reset | email_verified | account_locked | session_invalidated
 */
class AuthLog extends Model
{
    const UPDATED_AT = null; // Append-only

    const EVENT_LOGIN_SUCCESS       = 'login_success';
    const EVENT_LOGIN_FAILED        = 'login_failed';
    const EVENT_LOGOUT              = 'logout';
    const EVENT_PASSWORD_CHANGED    = 'password_changed';
    const EVENT_PASSWORD_RESET      = 'password_reset';
    const EVENT_EMAIL_VERIFIED      = 'email_verified';
    const EVENT_ACCOUNT_LOCKED      = 'account_locked';
    const EVENT_SESSION_INVALIDATED = 'session_invalidated';

    protected $table = 'auth_logs';

    protected $fillable = [
        'user_id',
        'email',
        'event',
        'ip_address',
        'user_agent',
        'context',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeEvent($query, string $event): mixed
    {
        return $query->where('event', $event);
    }

    public function scopeForUser($query, int $userId): mixed
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForIp($query, string $ip): mixed
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeRecent($query, int $minutes = 15): mixed
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
