<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropRecommendation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'soil_test_id',
        'nitrogen', 'phosphorus', 'potassium', 'ph_level',
        'humidity', 'rainfall_mm', 'temperature',
        'recommended_crops', 'explanation', 'model_version', 'status',
    ];

    protected $casts = [
        'recommended_crops' => 'array',
        'nitrogen'          => 'decimal:2',
        'phosphorus'        => 'decimal:2',
        'potassium'         => 'decimal:2',
        'ph_level'          => 'decimal:2',
        'humidity'          => 'decimal:2',
        'rainfall_mm'       => 'decimal:2',
        'temperature'       => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function soilTest(): BelongsTo
    {
        return $this->belongsTo(SoilTest::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────
    public function getTopCropAttribute(): ?string
    {
        $crops = $this->recommended_crops;
        return $crops[0]['crop'] ?? ($crops[0]['name'] ?? null);
    }
}
