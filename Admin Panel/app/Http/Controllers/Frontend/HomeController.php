<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Major Pakistani agricultural cities shown in the weather marquee.
     * Override via WEATHER_CITIES in .env (comma-separated, e.g. "Lahore,PK|Karachi,PK")
     */
    private const DEFAULT_CITIES = [
        'Lahore,PK',
        'Karachi,PK',
        'Islamabad,PK',
        'Faisalabad,PK',
        'Multan,PK',
        'Peshawar,PK',
        'Quetta,PK',
        'Sialkot,PK',
        'Gujranwala,PK',
        'Hyderabad,PK',
    ];

    public function index(): View
    {
        $weatherList = $this->fetchAllCities();

        return view('customer.index', compact('weatherList'));
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function fetchAllCities(): array
    {
        $apiKey = config('plantix.openweather_api_key');

        if (blank($apiKey)) {
            return [];
        }

        // Allow override via env: WEATHER_CITIES="Lahore,PK|Karachi,PK|..."
        $envCities = env('WEATHER_CITIES');
        $cities = $envCities
            ? array_filter(array_map('trim', explode('|', $envCities)))
            : self::DEFAULT_CITIES;

        return Cache::remember('home_weather_all_cities', 1800, function () use ($apiKey, $cities) {
            $results = [];

            // Fire all requests in parallel using Http::pool
            $responses = Http::pool(function ($pool) use ($cities, $apiKey) {
                foreach ($cities as $city) {
                    $pool->as($city)->timeout(6)->get('https://api.openweathermap.org/data/2.5/weather', [
                        'q'     => $city,
                        'units' => 'metric',
                        'appid' => $apiKey,
                    ]);
                }
            });

            foreach ($cities as $city) {
                try {
                    $response = $responses[$city];
                    if (! ($response instanceof \Illuminate\Http\Client\Response) || ! $response->successful()) {
                        continue;
                    }
                    $data = $response->json();
                    $results[] = [
                        'city'       => $data['name'] ?? explode(',', $city)[0],
                        'country'    => $data['sys']['country'] ?? 'PK',
                        'temp'       => round($data['main']['temp'] ?? 0),
                        'feels_like' => round($data['main']['feels_like'] ?? 0),
                        'humidity'   => $data['main']['humidity'] ?? 0,
                        'wind_speed' => round(($data['wind']['speed'] ?? 0) * 3.6),
                        'condition'  => $data['weather'][0]['description'] ?? '',
                        'icon_code'  => $data['weather'][0]['icon'] ?? '01d',
                        'icon_url'   => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '.png',
                    ];
                } catch (\Throwable) {
                    continue;
                }
            }

            return $results;
        });
    }
}
