<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AppointmentLog — Immutable audit trail for every appointment mutation.
 *
 * Never soft-deleted; records are permanent.
 */
class AppointmentLog extends Model
{
    public const UPDATED_AT = null; // immutable — no updated_at

    protected $fillable = [
        'appointment_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'context',
        'notes',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'context'     => 'array',
        'occurred_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static factory ────────────────────────────────────────────────────────

    public static function record(
        Appointment $appointment,
        string      $action,
        ?int        $userId     = null,
        ?string     $fromStatus = null,
        ?string     $toStatus   = null,
        ?string     $notes      = null,
        array       $context    = []
    ): self {
        return static::create([
            'appointment_id' => $appointment->id,
            'user_id'        => $userId,
            'action'         => $action,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'notes'          => $notes,
            'context'        => $context,
            'ip_address'     => request()->ip(),
            'user_agent'     => substr(request()->userAgent() ?? '', 0, 500),
            'occurred_at'    => now(),
        ]);
    }
}
