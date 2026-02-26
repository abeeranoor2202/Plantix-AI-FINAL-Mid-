<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'farm_profile_id', 'season', 'year', 'primary_crop',
        'crop_schedule', 'water_plan', 'expected_yield_tons', 'estimated_revenue',
        'soil_suitability_notes', 'recommendations', 'status',
    ];

    protected $casts = [
        'crop_schedule'       => 'array',
        'water_plan'          => 'array',
        'expected_yield_tons' => 'decimal:3',
        'estimated_revenue'   => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farmProfile(): BelongsTo
    {
        return $this->belongsTo(FarmProfile::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSeason($query, string $season, int $year)
    {
        return $query->where('season', $season)->where('year', $year);
    }
}
