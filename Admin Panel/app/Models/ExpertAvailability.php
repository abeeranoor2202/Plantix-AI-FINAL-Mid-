<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpertAvailability — Recurring weekly schedule template.
 *
 * Each row = one time block on a specific day of the week.
 * day_of_week: 0 = Sunday … 6 = Saturday (matches Carbon::dayOfWeek).
 *
 * Used by AvailabilityService::generateWeeklySlots() to project
 * concrete appointment_slots for a date range.
 */
class ExpertAvailability extends Model
{
    protected $table = 'expert_availability';

    protected $fillable = [
        'expert_id',
        'day',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'label',
        'is_active',
    ];

    protected $casts = [
        'slot_duration' => 'integer',
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ExpertAvailability $availability) {
            $dayMap = [
                'sunday' => 0,
                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
            ];

            if (! empty($availability->day) && ! $availability->isDirty('day_of_week')) {
                $normalizedDay = strtolower((string) $availability->day);
                if (array_key_exists($normalizedDay, $dayMap)) {
                    $availability->day = $normalizedDay;
                    $availability->day_of_week = $dayMap[$normalizedDay];
                }
            } elseif (! is_null($availability->day_of_week) && ! $availability->isDirty('day')) {
                $days = array_flip($dayMap);
                $availability->day = $days[(int) $availability->day_of_week] ?? $availability->day;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDayNameAttribute(): string
    {
        if (! empty($this->day)) {
            return ucfirst((string) $this->day);
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week] ?? 'Unknown';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }
}
