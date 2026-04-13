<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * NotificationLog — immutable audit trail for every outgoing email.
 *
 * @property string $dedup_key  set to "{notification_type}:{notifiable_type}:{notifiable_id}" to prevent duplicate emails
 */
class NotificationLog extends Model
{
    protected $fillable = [
        'user_id', 'recipient_email', 'recipient_name', 'recipient_role',
        'notification_type', 'mailable_class', 'subject',
        'payload',
        'notifiable_type', 'notifiable_id',
        'status', 'error_message', 'attempt_count',
        'sent_at', 'failed_at', 'dedup_key',
    ];

    protected $casts = [
        'sent_at'       => 'datetime',
        'failed_at'     => 'datetime',
        'attempt_count' => 'integer',
        'payload'       => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
