<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Pakistani agricultural cities / districts
        $zones = [
            [
                'zone_name'   => 'Lahore',
                'description' => 'Provincial capital of Punjab – major agri-input distribution hub',
                'status'      => 'active',
                'points'      => [
                    [31.5497, 74.3436], [31.5700, 74.3600], [31.5600, 74.3800],
                    [31.5300, 74.3700], [31.5200, 74.3400], [31.5300, 74.3200],
                ],
            ],
            [
                'zone_name'   => 'Faisalabad',
                'description' => 'Textile & agricultural hub of Punjab',
                'status'      => 'active',
                'points'      => [
                    [31.4180, 73.0790], [31.4500, 73.1000], [31.4300, 73.1200],
                    [31.4000, 73.1100], [31.3900, 73.0900], [31.4000, 73.0700],
                ],
            ],
            [
                'zone_name'   => 'Multan',
                'description' => 'Mango & citrus belt of South Punjab',
                'status'      => 'active',
                'points'      => [
                    [30.1575, 71.5249], [30.1800, 71.5500], [30.1700, 71.5700],
                    [30.1400, 71.5600], [30.1300, 71.5300], [30.1400, 71.5100],
                ],
            ],
            [
                'zone_name'   => 'Bahawalpur',
                'description' => 'Cotton & wheat growing district',
                'status'      => 'active',
                'points'      => [
                    [29.3956, 71.6836], [29.4200, 71.7100], [29.4100, 71.7300],
                    [29.3800, 71.7200], [29.3700, 71.6900], [29.3800, 71.6700],
                ],
            ],
            [
                'zone_name'   => 'Gujranwala',
                'description' => 'Rice and wheat cultivation belt',
                'status'      => 'active',
                'points'      => [
                    [32.1877, 74.1945], [32.2100, 74.2200], [32.2000, 74.2400],
                    [32.1700, 74.2300], [32.1600, 74.2000], [32.1700, 74.1800],
                ],
            ],
            [
                'zone_name'   => 'Sahiwal',
                'description' => 'Dairy and sugarcane growing region',
                'status'      => 'active',
                'points'      => [
                    [30.6706, 73.1064], [30.6900, 73.1300], [30.6800, 73.1500],
                    [30.6500, 73.1400], [30.6400, 73.1100], [30.6600, 73.0900],
                ],
            ],
            [
                'zone_name'   => 'Peshawar',
                'description' => 'KPK provincial capital – tobacco & maize region',
                'status'      => 'active',
                'points'      => [
                    [34.0150, 71.5805], [34.0400, 71.6100], [34.0300, 71.6300],
                    [34.0000, 71.6200], [33.9900, 71.5900], [34.0000, 71.5700],
                ],
            ],
            [
                'zone_name'   => 'Karachi',
                'description' => 'Port city – agri-input import & distribution',
                'status'      => 'active',
                'points'      => [
                    [24.8607, 67.0011], [24.8900, 67.0400], [24.8700, 67.0700],
                    [24.8400, 67.0600], [24.8200, 67.0200], [24.8400, 66.9900],
                ],
            ],
            [
                'zone_name'   => 'Sukkur',
                'description' => 'Sindh irrigated zone – rice and wheat',
                'status'      => 'active',
                'points'      => [
                    [27.7052, 68.8574], [27.7300, 68.8800], [27.7200, 68.9000],
                    [27.6900, 68.8900], [27.6800, 68.8600], [27.6900, 68.8400],
                ],
            ],
            [
                'zone_name'   => 'Quetta',
                'description' => 'Apple & stone-fruit belt of Balochistan',
                'status'      => 'active',
                'points'      => [
                    [30.1798, 66.9750], [30.2100, 67.0100], [30.2000, 67.0400],
                    [30.1700, 67.0300], [30.1500, 67.0000], [30.1600, 66.9700],
                ],
            ],
        ];

        foreach ($zones as $z) {
            $zoneId = DB::table('zones')->insertGetId([
                'zone_name'   => $z['zone_name'],
                'description' => $z['description'],
                'status'      => $z['status'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            foreach ($z['points'] as $i => $pt) {
                DB::table('zone_points')->insert([
                    'zone_id'    => $zoneId,
                    'latitude'   => $pt[0],
                    'longitude'  => $pt[1],
                    'sort_order' => $i,
                ]);
            }
        }

        $this->command->info('ZonesSeeder: ' . DB::table('zones')->count() . ' zones with polygon points inserted.');
    }
}
