<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionLog extends Model
{
    protected $fillable = [
        'user_id',
        'crop_recommendation_id',
        'nitrogen',
        'phosphorus',
        'potassium',
        'temperature',
        'humidity',
        'ph_level',
        'rainfall_mm',
        'predicted_crop',
        'confidence_score',
        'confidence_percent',
        'request_id',
        'record_id',
        'model_version',
        'model_name',
        'status',
        'error_details',
        'predicted_at',
    ];

    protected $casts = [
        'nitrogen' => 'decimal:2',
        'phosphorus' => 'decimal:2',
        'potassium' => 'decimal:2',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'ph_level' => 'decimal:2',
        'rainfall_mm' => 'decimal:2',
        'confidence_score' => 'decimal:6',
        'confidence_percent' => 'integer',
        'error_details' => 'array',
        'predicted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cropRecommendation(): BelongsTo
    {
        return $this->belongsTo(CropRecommendation::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCrop($query, $cropName)
    {
        return $query->where('predicted_crop', strtolower($cropName));
    }

    public function scopeHighConfidence($query, $threshold = 80)
    {
        return $query->where('confidence_percent', '>=', $threshold);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderByDesc('predicted_at');
    }
}
