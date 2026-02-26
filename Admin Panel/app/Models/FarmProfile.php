<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'farm_name', 'location', 'farm_size_acres',
        'soil_type', 'water_source', 'climate_zone', 'previous_crops', 'notes',
    ];

    protected $casts = [
        'previous_crops'  => 'array',
        'farm_size_acres' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function soilTests(): HasMany
    {
        return $this->hasMany(SoilTest::class);
    }

    public function cropPlans(): HasMany
    {
        return $this->hasMany(CropPlan::class);
    }
}
