<?php

namespace App\Services;

use App\Models\WeatherLog;
use App\Models\WeatherAlertLog;
use App\Models\User;
use App\Models\UserLocation;
use App\Notifications\WeatherAlertNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WeatherService
 *
 * Fetches real-time weather and forecasts via OpenWeatherMap API.
 * Caches responses (30 min) to avoid excessive API calls.
 * Generates agriculture-focused alerts.
 */
class WeatherService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5';
    private int    $cacheTtlMinutes = 30;

    public function __construct()
    {
        $this->apiKey = config('plantix.openweather_api_key', '');
    }

    /**
     * Get current weather + 5-day forecast for a city.
     *
     * @param  string $city
     * @return array
     */
    public function getWeatherForCity(string $city): array
    {
        $cacheKey = 'weather_city_' . strtolower(str_replace(' ', '_', $city));

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTtlMinutes), function () use ($city) {
            return $this->fetchAndStore($city, null, null);
        });
    }

    /**
     * Get weather by coordinates.
     *
     * @param  float $lat
     * @param  float $lon
     * @return array
     */
    public function getWeatherByCoords(float $lat, float $lon): array
    {
        $cacheKey = 'weather_coords_' . round($lat, 2) . '_' . round($lon, 2);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTtlMinutes), function () use ($lat, $lon) {
            return $this->fetchAndStore(null, $lat, $lon);
        });
    }

    /**
     * Get weather for a user's primary location.
     */
    public function getWeatherForUser(User $user): array
    {
        $location = $user->primaryLocation ?? $user->locations()->first();

        if ($location?->city) {
            return $this->getWeatherForCity($location->city);
        }
        if ($location?->latitude) {
            return $this->getWeatherByCoords((float)$location->latitude, (float)$location->longitude);
        }

        // Default to Lahore, Pakistan
        return $this->getWeatherForCity('Lahore');
    }

    /**
     * Generate agriculture-specific alert message if conditions are dangerous.
     */
    public function checkAgricultureAlert(array $weather, ?User $user = null): ?array
    {
        $alerts = [];

        $temp     = $weather['temperature_c'] ?? 25;
        $humidity = $weather['humidity'] ?? 60;
        $wind     = $weather['wind_speed_kmh'] ?? 0;
        $rain     = $weather['rainfall_mm'] ?? 0;

        if ($temp > 42) {
            $alerts[] = ['type' => 'heat_stress', 'severity' => 'high',
                'message' => "Extreme heat ({$temp}°C) — Irrigate crops early morning. Protect seedlings with shade nets."];
        }
        if ($temp < 4) {
            $alerts[] = ['type' => 'frost_alert', 'severity' => 'extreme',
                'message' => "Frost risk ({$temp}°C) — Cover sensitive crops. Light irrigation can protect from frost damage."];
        }
        if ($humidity > 85 && $temp > 25) {
            $alerts[] = ['type' => 'disease_risk', 'severity' => 'moderate',
                'message' => "High humidity ({$humidity}%) with warm temperature — Ideal for fungal diseases. Scout crops and apply preventive fungicide."];
        }
        if ($wind > 60) {
            $alerts[] = ['type' => 'wind_alert', 'severity' => 'high',
                'message' => "Strong winds ({$wind} km/h) — Secure support structures. Avoid spray applications."];
        }
        if ($rain > 50) {
            $alerts[] = ['type' => 'heavy_rain', 'severity' => 'moderate',
                'message' => "Heavy rainfall ({$rain} mm) — Check drainage. Delay fertilizer application by 48 hours."];
        }

        if (empty($alerts)) {
            return null;
        }

        // Log and notify if user provided
        foreach ($alerts as $alert) {
            $log = WeatherAlertLog::create([
                'user_id'    => $user?->id,
                'city'       => $weather['city'] ?? null,
                'alert_type' => $alert['type'],
                'severity'   => $alert['severity'],
                'message'    => $alert['message'],
            ]);

            if ($user) {
                try {
                    $user->notify(new WeatherAlertNotification($alert));
                    $log->update(['notification_sent' => true]);
                } catch (\Throwable $e) {
                    Log::error('Weather alert notification failed: ' . $e->getMessage());
                }
            }
        }

        return $alerts;
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function fetchAndStore(?string $city, ?float $lat, ?float $lon): array
    {
        $current  = $this->fetchCurrentWeather($city, $lat, $lon);
        $forecast = $this->fetchForecast($city, $lat, $lon);

        if (!$current) {
            return $this->getMockWeather($city ?? 'Unknown');
        }

        $data = $this->parseOWMResponse($current, $forecast);

        // Persist to DB
        WeatherLog::create($data);

        return $data;
    }

    private function fetchCurrentWeather(?string $city, ?float $lat, ?float $lon): ?array
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $params = ['appid' => $this->apiKey, 'units' => 'metric'];
            if ($city) {
                $params['q'] = $city . ',PK';
            } else {
                $params['lat'] = $lat;
                $params['lon'] = $lon;
            }

            $response = Http::timeout(8)->get("{$this->baseUrl}/weather", $params);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('OpenWeatherMap current weather failed: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchForecast(?string $city, ?float $lat, ?float $lon): ?array
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $params = ['appid' => $this->apiKey, 'units' => 'metric', 'cnt' => 40];
            if ($city) {
                $params['q'] = $city . ',PK';
            } else {
                $params['lat'] = $lat;
                $params['lon'] = $lon;
            }

            $response = Http::timeout(8)->get("{$this->baseUrl}/forecast", $params);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('OpenWeatherMap forecast failed: ' . $e->getMessage());
            return null;
        }
    }

    private function parseOWMResponse(array $current, ?array $forecast): array
    {
        $main    = $current['main'] ?? [];
        $wind    = $current['wind'] ?? [];
        $weather = $current['weather'][0] ?? [];
        $rain    = $current['rain']['1h'] ?? 0;

        $hourly = [];
        $daily  = [];

        if ($forecast && isset($forecast['list'])) {
            // Build hourly (next 24h)
            foreach (array_slice($forecast['list'], 0, 8) as $item) {
                $hourly[] = [
                    'time'      => date('H:i', $item['dt']),
                    'temp_c'    => $item['main']['temp'] ?? null,
                    'condition' => $item['weather'][0]['description'] ?? '',
                    'icon'      => $item['weather'][0]['icon'] ?? '',
                ];
            }

            // Build daily (next 5 days, one entry per day)
            $daysSeen = [];
            foreach ($forecast['list'] as $item) {
                $day = date('Y-m-d', $item['dt']);
                if (!isset($daysSeen[$day]) && count($daily) < 5) {
                    $daysSeen[$day] = true;
                    $daily[] = [
                        'date'      => $day,
                        'min_c'     => $item['main']['temp_min'] ?? null,
                        'max_c'     => $item['main']['temp_max'] ?? null,
                        'condition' => $item['weather'][0]['description'] ?? '',
                        'icon'      => $item['weather'][0]['icon'] ?? '',
                    ];
                }
            }
        }

        return [
            'city'              => $current['name'] ?? null,
            'latitude'          => $current['coord']['lat'] ?? null,
            'longitude'         => $current['coord']['lon'] ?? null,
            'temperature_c'     => $main['temp'] ?? null,
            'feels_like_c'      => $main['feels_like'] ?? null,
            'humidity'          => $main['humidity'] ?? null,
            'wind_speed_kmh'    => isset($wind['speed']) ? round($wind['speed'] * 3.6, 1) : null,
            'wind_direction'    => $this->degreeToCompass($wind['deg'] ?? null),
            'rainfall_mm'       => $rain,
            'condition'         => $weather['description'] ?? null,
            'icon_code'         => $weather['icon'] ?? null,
            'hourly_forecast'   => $hourly,
            'daily_forecast'    => $daily,
            'raw_response'      => $current,
            'has_alert'         => false,
            'fetched_at'        => now(),
        ];
    }

    private function getMockWeather(string $city): array
    {
        return [
            'city'              => $city,
            'latitude'          => 31.5204,
            'longitude'         => 74.3587,
            'temperature_c'     => 32.5,
            'feels_like_c'      => 35.0,
            'humidity'          => 55,
            'wind_speed_kmh'    => 15,
            'wind_direction'    => 'NW',
            'rainfall_mm'       => 0,
            'condition'         => 'Partly Cloudy',
            'icon_code'         => '02d',
            'uv_index'          => 7.2,
            'hourly_forecast'   => [],
            'daily_forecast'    => [],
            'raw_response'      => [],
            'has_alert'         => false,
            'alert_message'     => null,
            'fetched_at'        => now(),
        ];
    }

    private function degreeToCompass(?int $deg): ?string
    {
        if ($deg === null) {
            return null;
        }
        $dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        return $dirs[round($deg / 45) % 8];
    }
}
