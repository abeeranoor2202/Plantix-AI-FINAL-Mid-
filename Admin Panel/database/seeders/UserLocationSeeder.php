<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserLocationSeeder extends Seeder
{
    public function run(): void
    {
        $now       = Carbon::now();
        $customers = DB::table('users')->where('role', 'user')->pluck('id')->toArray();

        $cities = [
            ['city' => 'Lahore',        'lat' => 31.5204, 'lng' => 74.3587],
            ['city' => 'Karachi',       'lat' => 24.8607, 'lng' => 67.0011],
            ['city' => 'Islamabad',     'lat' => 33.7294, 'lng' => 73.0931],
            ['city' => 'Faisalabad',    'lat' => 31.4154, 'lng' => 73.0791],
            ['city' => 'Rawalpindi',    'lat' => 33.5651, 'lng' => 73.0169],
            ['city' => 'Multan',        'lat' => 30.1575, 'lng' => 71.5249],
            ['city' => 'Peshawar',      'lat' => 34.0151, 'lng' => 71.5249],
            ['city' => 'Quetta',        'lat' => 30.1798, 'lng' => 66.9750],
            ['city' => 'Gujranwala',    'lat' => 32.1617, 'lng' => 74.1883],
            ['city' => 'Sialkot',       'lat' => 32.4927, 'lng' => 74.5312],
            ['city' => 'Hyderabad',     'lat' => 25.3960, 'lng' => 68.3578],
            ['city' => 'Sukkur',        'lat' => 27.7052, 'lng' => 68.8574],
        ];

        foreach ($customers as $idx => $userId) {
            $city = $cities[$idx % count($cities)];
            DB::table('user_locations')->insert([
                'user_id'    => $userId,
                'city'       => $city['city'],
                'country'    => 'Pakistan',
                'latitude'   => $city['lat'] + (rand(-5, 5) * 0.001),
                'longitude'  => $city['lng'] + (rand(-5, 5) * 0.001),
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
