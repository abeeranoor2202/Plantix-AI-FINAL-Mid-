<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoilTest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'farm_profile_id', 'nitrogen', 'phosphorus', 'potassium',
        'ph_level', 'organic_matter', 'humidity', 'rainfall_mm', 'temperature',
        'lab_report', 'tested_at',
    ];

    protected $casts = [
        'nitrogen'       => 'decimal:2',
        'phosphorus'     => 'decimal:2',
        'potassium'      => 'decimal:2',
        'ph_level'       => 'decimal:2',
        'organic_matter' => 'decimal:2',
        'humidity'       => 'decimal:2',
        'rainfall_mm'    => 'decimal:2',
        'temperature'    => 'decimal:2',
        'tested_at'      => 'date',
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

    public function cropRecommendations(): HasMany
    {
        return $this->hasMany(CropRecommendation::class);
    }

    public function fertilizerRecommendations(): HasMany
    {
        return $this->hasMany(FertilizerRecommendation::class);
    }
}
