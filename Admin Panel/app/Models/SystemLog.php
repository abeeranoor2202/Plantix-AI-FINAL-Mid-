<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemLog — centralized structured log for cross-cutting concerns.
 *
 * Written by LoggingService. Sensitive data (tokens, passwords) must be
 * masked by the service before insertion.
 */
class SystemLog extends Model
{
    const UPDATED_AT = null;

    // Levels (PSR-3 compatible)
    const LEVEL_DEBUG     = 'debug';
    const LEVEL_INFO      = 'info';
    const LEVEL_NOTICE    = 'notice';
    const LEVEL_WARNING   = 'warning';
    const LEVEL_ERROR     = 'error';
    const LEVEL_CRITICAL  = 'critical';
    const LEVEL_ALERT     = 'alert';
    const LEVEL_EMERGENCY = 'emergency';

    // Channels
    const CHANNEL_AUTH    = 'auth';
    const CHANNEL_PAYMENT = 'payment';
    const CHANNEL_RBAC    = 'rbac';
    const CHANNEL_FILE    = 'file';
    const CHANNEL_QUEUE   = 'queue';
    const CHANNEL_WEBHOOK = 'webhook';
    const CHANNEL_API     = 'api';
    const CHANNEL_APP     = 'app';

    protected $table = 'system_logs';

    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
        'user_id',
        'ip_address',
        'trace_id',
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

    public function scopeChannel($query, string $channel): mixed
    {
        return $query->where('channel', $channel);
    }

    public function scopeLevel($query, string $level): mixed
    {
        return $query->where('level', $level);
    }

    public function scopeErrors($query): mixed
    {
        return $query->whereIn('level', [
            self::LEVEL_ERROR,
            self::LEVEL_CRITICAL,
            self::LEVEL_ALERT,
            self::LEVEL_EMERGENCY,
        ]);
    }

    public function scopeRecent($query, int $hours = 24): mixed
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeForTrace($query, string $traceId): mixed
    {
        return $query->where('trace_id', $traceId);
    }
}
