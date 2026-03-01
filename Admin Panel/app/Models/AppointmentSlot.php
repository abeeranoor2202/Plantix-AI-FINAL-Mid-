<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentSlot extends Model
{
    protected $fillable = [
        'expert_id',
        'date',
        'start_time',
        'end_time',
        'is_booked',
        'appointment_id',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_booked' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_booked', false);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForExpert($query, int $expertId)
    {
        return $query->where('expert_id', $expertId);
    }
}
