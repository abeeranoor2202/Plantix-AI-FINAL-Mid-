<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'city',
        'country',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'latitude'   => 'decimal:8',
        'longitude'  => 'decimal:8',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Set this location as the user's primary, unset any other primary.
     */
    public function makePrimary(): void
    {
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }
}
