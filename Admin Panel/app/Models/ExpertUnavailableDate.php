<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpertUnavailableDate — Specific dates/time blocks when an expert is unavailable.
 *
 * Overrides recurring weekly schedule for the given date.
 * No block_from/block_until = full day block.
 */
class ExpertUnavailableDate extends Model
{
    protected $fillable = [
        'expert_id',
        'unavailable_date',
        'reason',
        'block_from',
        'block_until',
    ];

    protected $casts = [
        'unavailable_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this block covers the entire day (no specific time range).
     */
    public function isFullDayBlock(): bool
    {
        return is_null($this->block_from) && is_null($this->block_until);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->where('unavailable_date', '>=', now()->toDateString());
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('unavailable_date', $date);
    }
}
