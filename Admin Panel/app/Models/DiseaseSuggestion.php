<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseSuggestion extends Model
{
    protected $fillable = [
        'report_id', 'disease_name', 'description', 'organic_treatment',
        'chemical_treatment', 'preventive_measures', 'recommended_products',
        'expert_verified', 'verified_by',
    ];

    protected $casts = [
        'recommended_products' => 'array',
        'expert_verified'      => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function report(): BelongsTo
    {
        return $this->belongsTo(CropDiseaseReport::class, 'report_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
