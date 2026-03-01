<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeasonalDataSeeder extends Seeder
{
    public function run(): void
    {
        $now  = Carbon::now();
        $rows = [
            // ── RABI (Oct/Nov → Mar/Apr) ───────────────────────────────────────────
            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Wheat',     'sowing_months'=>'October–November', 'harvesting_months'=>'March–April',    'water_requirement_mm'=>400,  'soil_type_compatibility'=>'Loamy,Clay Loam',          'min_temp_celsius'=>10,'max_temp_celsius'=>25,'avg_yield_tons_per_acre'=>1.60,'notes'=>'Most important Rabi crop of Pakistan.'],
            ['season'=>'Rabi','region'=>'Sindh',       'crop_name'=>'Wheat',     'sowing_months'=>'November',         'harvesting_months'=>'March',          'water_requirement_mm'=>350,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>12,'max_temp_celsius'=>28,'avg_yield_tons_per_acre'=>1.40,'notes'=>'Lower yield due to heat stress at grain fill.'],
            ['season'=>'Rabi','region'=>'KPK',         'crop_name'=>'Wheat',     'sowing_months'=>'October',          'harvesting_months'=>'April–May',      'water_requirement_mm'=>450,  'soil_type_compatibility'=>'Loamy,Clay Loam,Silty',    'min_temp_celsius'=> 8,'max_temp_celsius'=>22,'avg_yield_tons_per_acre'=>1.50,'notes'=>'Cooler temperatures extend grain-fill period.'],
            ['season'=>'Rabi','region'=>'Balochistan', 'crop_name'=>'Wheat',     'sowing_months'=>'October–November', 'harvesting_months'=>'May',            'water_requirement_mm'=>300,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=> 5,'max_temp_celsius'=>20,'avg_yield_tons_per_acre'=>1.20,'notes'=>'Primarily rain-fed plateau cultivation.'],

            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Mustard',   'sowing_months'=>'October',          'harvesting_months'=>'February–March', 'water_requirement_mm'=>250,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>10,'max_temp_celsius'=>25,'avg_yield_tons_per_acre'=>0.50,'notes'=>'Drought-tolerant oil seed crop.'],
            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Chickpea',  'sowing_months'=>'October–November', 'harvesting_months'=>'March–April',    'water_requirement_mm'=>300,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=>10,'max_temp_celsius'=>28,'avg_yield_tons_per_acre'=>0.45,'notes'=>'Nitrogen-fixing legume; improves soil health.'],
            ['season'=>'Rabi','region'=>'Sindh',       'crop_name'=>'Onion',     'sowing_months'=>'November',         'harvesting_months'=>'February–March', 'water_requirement_mm'=>550,  'soil_type_compatibility'=>'Sandy Loam,Loam',          'min_temp_celsius'=>13,'max_temp_celsius'=>30,'avg_yield_tons_per_acre'=>4.00,'notes'=>'Major commercial vegetable crop in Sindh.'],
            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Potato',    'sowing_months'=>'October–November', 'harvesting_months'=>'January–February','water_requirement_mm'=>500, 'soil_type_compatibility'=>'Sandy Loam,Loamy',          'min_temp_celsius'=>10,'max_temp_celsius'=>22,'avg_yield_tons_per_acre'=>5.50,'notes'=>'Prefer well-drained loose soils.'],
            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Tomato',    'sowing_months'=>'November',         'harvesting_months'=>'February–March', 'water_requirement_mm'=>600,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>12,'max_temp_celsius'=>27,'avg_yield_tons_per_acre'=>8.00,'notes'=>'High-value vegetable; requires staking.'],
            ['season'=>'Rabi','region'=>'Punjab',      'crop_name'=>'Sunflower', 'sowing_months'=>'February',         'harvesting_months'=>'May–June',       'water_requirement_mm'=>350,  'soil_type_compatibility'=>'Loamy,Clay Loam',          'min_temp_celsius'=>15,'max_temp_celsius'=>32,'avg_yield_tons_per_acre'=>0.70,'notes'=>'Spring sunflower; short rabi window.'],
            ['season'=>'Rabi','region'=>'KPK',         'crop_name'=>'Maize',     'sowing_months'=>'March',            'harvesting_months'=>'July',           'water_requirement_mm'=>500,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>15,'max_temp_celsius'=>32,'avg_yield_tons_per_acre'=>1.80,'notes'=>'Spring maize common in NWFP valleys.'],

            // ── KHARIF (May/Jun → Oct/Nov) ────────────────────────────────────────
            ['season'=>'Kharif','region'=>'Sindh',       'crop_name'=>'Rice',      'sowing_months'=>'June–July',        'harvesting_months'=>'October–November','water_requirement_mm'=>1200,'soil_type_compatibility'=>'Clay,Clay Loam',           'min_temp_celsius'=>20,'max_temp_celsius'=>35,'avg_yield_tons_per_acre'=>2.50,'notes'=>'Paddy variety IRRI-6 widely grown in Sindh.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Rice',      'sowing_months'=>'June',             'harvesting_months'=>'October',        'water_requirement_mm'=>1100,'soil_type_compatibility'=>'Clay,Clay Loam',            'min_temp_celsius'=>22,'max_temp_celsius'=>35,'avg_yield_tons_per_acre'=>2.20,'notes'=>'Basmati varieties grown in central Punjab.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Cotton',    'sowing_months'=>'May–June',         'harvesting_months'=>'September–November','water_requirement_mm'=>700,'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=>20,'max_temp_celsius'=>38,'avg_yield_tons_per_acre'=>0.70,'notes'=>'"White Gold" — backbone of Pakistan textile.'],
            ['season'=>'Kharif','region'=>'Sindh',       'crop_name'=>'Cotton',    'sowing_months'=>'May',              'harvesting_months'=>'September–October','water_requirement_mm'=>650,'soil_type_compatibility'=>'Sandy Loam,Loamy',          'min_temp_celsius'=>22,'max_temp_celsius'=>40,'avg_yield_tons_per_acre'=>0.65,'notes'=>'Higher heat tolerance required.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Maize',     'sowing_months'=>'June–July',        'harvesting_months'=>'October',        'water_requirement_mm'=>550,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>18,'max_temp_celsius'=>35,'avg_yield_tons_per_acre'=>2.00,'notes'=>'Hybrid varieties can yield up to 3 t/acre.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Sugarcane', 'sowing_months'=>'March–April',      'harvesting_months'=>'December–February','water_requirement_mm'=>1500,'soil_type_compatibility'=>'Loamy,Clay Loam',          'min_temp_celsius'=>18,'max_temp_celsius'=>38,'avg_yield_tons_per_acre'=>30.00,'notes'=>'Long-duration crop; 10–14 month cycle.'],
            ['season'=>'Kharif','region'=>'Sindh',       'crop_name'=>'Sugarcane', 'sowing_months'=>'February–March',  'harvesting_months'=>'November–January','water_requirement_mm'=>1400,'soil_type_compatibility'=>'Loamy,Clay Loam',           'min_temp_celsius'=>20,'max_temp_celsius'=>40,'avg_yield_tons_per_acre'=>28.00,'notes'=>'Ratoon crop gives 2nd and 3rd year yield.'],
            ['season'=>'Kharif','region'=>'Sindh',       'crop_name'=>'Mango',     'sowing_months'=>'March (grafting)', 'harvesting_months'=>'May–August',     'water_requirement_mm'=>800,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=>20,'max_temp_celsius'=>45,'avg_yield_tons_per_acre'=>3.50,'notes'=>'Sindhri, Chaunsa, Desi — prime export varieties.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Mango',     'sowing_months'=>'February (grafting)','harvesting_months'=>'June–August',  'water_requirement_mm'=>700,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=>18,'max_temp_celsius'=>42,'avg_yield_tons_per_acre'=>3.00,'notes'=>'Anwar Ratol dominant in Multan belt.'],
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Sunflower', 'sowing_months'=>'June',             'harvesting_months'=>'September',      'water_requirement_mm'=>400,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>18,'max_temp_celsius'=>35,'avg_yield_tons_per_acre'=>0.65,'notes'=>'Autumn sunflower; second crop in rotation.'],

            // ── ZAID (Mar–May short window) ────────────────────────────────────────
            ['season'=>'Zaid','region'=>'Punjab',      'crop_name'=>'Bitter Gourd','sowing_months'=>'March–April',    'harvesting_months'=>'May–June',       'water_requirement_mm'=>450,  'soil_type_compatibility'=>'Sandy Loam,Loam',          'min_temp_celsius'=>22,'max_temp_celsius'=>38,'avg_yield_tons_per_acre'=>2.50,'notes'=>'Fast cash crop; ready in 45–55 days.'],
            ['season'=>'Zaid','region'=>'Sindh',       'crop_name'=>'Watermelon', 'sowing_months'=>'February–March',  'harvesting_months'=>'April–May',      'water_requirement_mm'=>500,  'soil_type_compatibility'=>'Sandy Loam',               'min_temp_celsius'=>22,'max_temp_celsius'=>40,'avg_yield_tons_per_acre'=>6.00,'notes'=>'Popular cash crop in southern Sindh.'],
            ['season'=>'Zaid','region'=>'Punjab',      'crop_name'=>'Muskmelon',  'sowing_months'=>'March',           'harvesting_months'=>'May–June',       'water_requirement_mm'=>400,  'soil_type_compatibility'=>'Sandy Loam',               'min_temp_celsius'=>20,'max_temp_celsius'=>38,'avg_yield_tons_per_acre'=>4.50,'notes'=>'Riverine sandy soils ideal.'],
            ['season'=>'Zaid','region'=>'Punjab',      'crop_name'=>'Cucumber',   'sowing_months'=>'March',           'harvesting_months'=>'May',            'water_requirement_mm'=>420,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>20,'max_temp_celsius'=>35,'avg_yield_tons_per_acre'=>3.50,'notes'=>'Greenhouse cultivation extends season.'],
            ['season'=>'Zaid','region'=>'KPK',         'crop_name'=>'Pea',        'sowing_months'=>'March–April',     'harvesting_months'=>'May–June',       'water_requirement_mm'=>350,  'soil_type_compatibility'=>'Loamy,Sandy Loam',         'min_temp_celsius'=>12,'max_temp_celsius'=>25,'avg_yield_tons_per_acre'=>1.20,'notes'=>'Short season legume; good summer demand.'],

            // ── PERENNIAL / year-round ──────────────────────────────────────────────
            ['season'=>'Kharif','region'=>'Punjab',      'crop_name'=>'Okra',      'sowing_months'=>'April–May',        'harvesting_months'=>'July–September', 'water_requirement_mm'=>500,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=>22,'max_temp_celsius'=>40,'avg_yield_tons_per_acre'=>2.50,'notes'=>'Heat-loving; multiple harvests per season.'],
            ['season'=>'Rabi','region'=>'Balochistan', 'crop_name'=>'Apple',      'sowing_months'=>'March (planting)', 'harvesting_months'=>'August–October', 'water_requirement_mm'=>600,  'soil_type_compatibility'=>'Loamy,Silty Loam',         'min_temp_celsius'=> 0,'max_temp_celsius'=>25,'avg_yield_tons_per_acre'=>4.00,'notes'=>'Quetta belt — high altitude orchards.'],
            ['season'=>'Rabi','region'=>'KPK',         'crop_name'=>'Peach',      'sowing_months'=>'February (graft)', 'harvesting_months'=>'June–August',    'water_requirement_mm'=>500,  'soil_type_compatibility'=>'Sandy Loam,Loamy',         'min_temp_celsius'=> 5,'max_temp_celsius'=>30,'avg_yield_tons_per_acre'=>2.80,'notes'=>'Swat & Dir valleys; chill-hour requirements.'],
            ['season'=>'Kharif','region'=>'Sindh',       'crop_name'=>'Banana',    'sowing_months'=>'Round-year',       'harvesting_months'=>'Round-year',     'water_requirement_mm'=>1800, 'soil_type_compatibility'=>'Sandy Loam,Loamy,Clay Loam','min_temp_celsius'=>18,'max_temp_celsius'=>40,'avg_yield_tons_per_acre'=>10.00,'notes'=>'Mirpurkhas district primary production zone.'],
        ];

        foreach ($rows as $row) {
            DB::table('seasonal_data')->insert(array_merge($row, [
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
