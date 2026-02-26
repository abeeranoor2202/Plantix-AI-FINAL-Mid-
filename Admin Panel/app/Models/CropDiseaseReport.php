<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropDiseaseReport extends Model
{
    use SoftDeletes;

    protected $table = 'crop_disease_reports';

    protected $fillable = [
        'user_id', 'crop_name', 'image_path', 'detected_disease',
        'confidence_score', 'all_predictions', 'model_used', 'status', 'user_description',
    ];

    protected $casts = [
        'all_predictions' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suggestion(): HasOne
    {
        return $this->hasOne(DiseaseSuggestion::class, 'report_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function getConfidencePercentAttribute(): string
    {
        return ($this->confidence_score ?? 0) . '%';
    }
}
