<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use App\Models\WeatherLog;
use App\Services\Customer\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    public function __construct(private WeatherService $weatherService) {}

    /**
     * Show weather widget / page data (AJAX or embedded).
     */
    public function current(Request $request)
    {
        $city = $request->input('city');
        $lat  = $request->input('lat');
        $lon  = $request->input('lon');

        if ($lat && $lon) {
            $weather = $this->weatherService->getWeatherByCoords((float)$lat, (float)$lon);
        } elseif ($city) {
            $weather = $this->weatherService->getWeatherForCity($city);
        } elseif (Auth::check()) {
            $weather = $this->weatherService->getWeatherForUser(Auth::user());
        } else {
            $weather = $this->weatherService->getWeatherForCity('Lahore');
        }

        // Check for agriculture alerts
        $alerts = $this->weatherService->checkAgricultureAlert($weather, Auth::user() ?? null);

        return response()->json([
            'success' => true,
            'data'    => $weather,
            'alerts'  => $alerts,
        ]);
    }

    /**
     * Save a user location preference.
     */
    public function saveLocation(Request $request)
    {
        $validated = $request->validate([
            'city'      => 'required|string|max:100',
            'label'     => 'nullable|string|max:50',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        // If making this primary, unset previous primary
        if ($request->boolean('is_primary')) {
            UserLocation::where('user_id', $user->id)->update(['is_primary' => false]);
        }

        $location = UserLocation::updateOrCreate(
            ['user_id' => $user->id, 'city' => $validated['city']],
            [
                'label'      => $validated['label'] ?? 'default',
                'latitude'   => $validated['latitude'] ?? null,
                'longitude'  => $validated['longitude'] ?? null,
                'is_primary' => $request->boolean('is_primary'),
            ]
        );

        return response()->json(['success' => true, 'data' => $location]);
    }

    /**
     * Get weather history logs for a city (chart data).
     */
    public function history(Request $request)
    {
        $city = $request->input('city', 'Lahore');

        $logs = WeatherLog::forCity($city)
            ->latest('fetched_at')
            ->take(24)
            ->get(['city', 'temperature_c', 'humidity', 'condition', 'fetched_at']);

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Pakistan major cities quick list.
     */
    public function cities()
    {
        $cities = [
            'Lahore', 'Karachi', 'Islamabad', 'Faisalabad', 'Rawalpindi',
            'Multan', 'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala',
            'Hyderabad', 'Sargodha', 'Bahawalpur', 'Sukkur', 'Mardan',
            'Kasur', 'Rahim Yar Khan', 'Sahiwal', 'Okara', 'Larkana',
        ];

        return response()->json(['success' => true, 'data' => $cities]);
    }
}

