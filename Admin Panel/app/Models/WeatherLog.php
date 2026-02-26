<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherLog extends Model
{
    protected $fillable = [
        'city', 'latitude', 'longitude',
        'temperature_c', 'feels_like_c', 'humidity', 'wind_speed_kmh',
        'wind_direction', 'rainfall_mm', 'uv_index', 'condition', 'icon_code',
        'hourly_forecast', 'daily_forecast', 'raw_response',
        'has_alert', 'alert_message', 'fetched_at',
    ];

    protected $casts = [
        'hourly_forecast'  => 'array',
        'daily_forecast'   => 'array',
        'raw_response'     => 'array',
        'has_alert'        => 'boolean',
        'fetched_at'       => 'datetime',
        'temperature_c'    => 'decimal:2',
        'feels_like_c'     => 'decimal:2',
        'humidity'         => 'decimal:2',
        'wind_speed_kmh'   => 'decimal:2',
        'rainfall_mm'      => 'decimal:2',
        'uv_index'         => 'decimal:1',
        'latitude'         => 'decimal:7',
        'longitude'        => 'decimal:7',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeRecent($query, int $minutes = 30)
    {
        return $query->where('fetched_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeForCity($query, string $city)
    {
        return $query->where('city', $city)->orderByDesc('fetched_at');
    }
}
