<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherAlertLog extends Model
{
    protected $fillable = [
        'user_id',
        'alert_type',
        'severity',
        'message',
        'city',
        'temperature_c',
        'notification_sent',
        'notified_at',
    ];

    protected $casts = [
        'notification_sent' => 'boolean',
        'notified_at'       => 'datetime',
        'temperature_c'     => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeUnnotified($query)
    {
        return $query->where('notification_sent', false);
    }

    public function scopeSeverity($query, string $level)
    {
        return $query->where('severity', $level);
    }
}
