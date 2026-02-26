<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FertilizerRecommendation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'soil_test_id', 'crop_type', 'growth_stage',
        'nitrogen', 'phosphorus', 'potassium', 'ph_level', 'temperature', 'humidity',
        'fertilizer_plan', 'application_instructions', 'estimated_cost_pkr', 'model_version',
    ];

    protected $casts = [
        'fertilizer_plan'     => 'array',
        'nitrogen'            => 'decimal:2',
        'phosphorus'          => 'decimal:2',
        'potassium'           => 'decimal:2',
        'ph_level'            => 'decimal:2',
        'temperature'         => 'decimal:2',
        'humidity'            => 'decimal:2',
        'estimated_cost_pkr'  => 'decimal:2',
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
}
