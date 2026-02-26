<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalData extends Model
{
    protected $table = 'seasonal_data';

    protected $fillable = [
        'season', 'region', 'crop_name', 'sowing_months', 'harvesting_months',
        'water_requirement_mm', 'soil_type_compatibility', 'min_temp_celsius',
        'max_temp_celsius', 'avg_yield_tons_per_acre', 'notes', 'is_active',
    ];

    protected $casts = [
        'water_requirement_mm'      => 'decimal:2',
        'avg_yield_tons_per_acre'   => 'decimal:3',
        'is_active'                 => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSeason($query, string $season)
    {
        return $query->where('season', $season)->where('is_active', true);
    }
}
