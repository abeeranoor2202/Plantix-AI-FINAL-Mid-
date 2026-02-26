<?php

namespace Database\Seeders;

use App\Models\SeasonalData;
use Illuminate\Database\Seeder;

class SeasonalDataSeeder extends Seeder
{
    /**
     * Seed seasonal_data reference table with Pakistan-specific
     * Rabi / Kharif / Zaid crop calendars.
     */
    public function run(): void
    {
        $crops = [

            // ─── Rabi (Oct–Apr, cool/dry season) ──────────────────────────────
            [
                'crop_name'        => 'Wheat',
                'season'           => 'Rabi',
                'sowing_month'     => 'October',
                'harvest_month'    => 'April',
                'water_needs'      => 'medium',
                'avg_yield_kg_acre'=> 1500,
                'notes'            => 'Most important staple crop of Pakistan. Requires 4–5 irrigations.',
            ],
            [
                'crop_name'        => 'Chickpea',
                'season'           => 'Rabi',
                'sowing_month'     => 'October',
                'harvest_month'    => 'March',
                'water_needs'      => 'low',
                'avg_yield_kg_acre'=> 600,
                'notes'            => 'Drought-tolerant legume. Fixes atmospheric nitrogen.',
            ],
            [
                'crop_name'        => 'Lentil',
                'season'           => 'Rabi',
                'sowing_month'     => 'November',
                'harvest_month'    => 'April',
                'water_needs'      => 'low',
                'avg_yield_kg_acre'=> 500,
                'notes'            => 'Grown in Punjab and KPK. Prefers well-drained loamy soils.',
            ],
            [
                'crop_name'        => 'Potato',
                'season'           => 'Rabi',
                'sowing_month'     => 'October',
                'harvest_month'    => 'January',
                'water_needs'      => 'high',
                'avg_yield_kg_acre'=> 8000,
                'notes'            => 'High-value vegetable crop. Cool temps preferred for tuber development.',
            ],
            [
                'crop_name'        => 'Onion',
                'season'           => 'Rabi',
                'sowing_month'     => 'November',
                'harvest_month'    => 'April',
                'water_needs'      => 'medium',
                'avg_yield_kg_acre'=> 5000,
                'notes'            => 'Major cash crop in Sindh. Prefers sandy loam soil.',
            ],
            [
                'crop_name'        => 'Sunflower',
                'season'           => 'Rabi',
                'sowing_month'     => 'January',
                'harvest_month'    => 'April',
                'water_needs'      => 'low',
                'avg_yield_kg_acre'=> 700,
                'notes'            => 'Oil crop. Short duration. Suitable as relay crop after cotton.',
            ],

            // ─── Kharif (Jun–Oct, hot/wet season) ─────────────────────────────
            [
                'crop_name'        => 'Rice',
                'season'           => 'Kharif',
                'sowing_month'     => 'June',
                'harvest_month'    => 'October',
                'water_needs'      => 'very_high',
                'avg_yield_kg_acre'=> 1800,
                'notes'            => 'Major export commodity. Requires standing water for 60–90 days.',
            ],
            [
                'crop_name'        => 'Maize',
                'season'           => 'Kharif',
                'sowing_month'     => 'June',
                'harvest_month'    => 'September',
                'water_needs'      => 'high',
                'avg_yield_kg_acre'=> 2000,
                'notes'            => 'Second most important cereal. Used for fodder, food, and feed.',
            ],
            [
                'crop_name'        => 'Cotton',
                'season'           => 'Kharif',
                'sowing_month'     => 'April',
                'harvest_month'    => 'November',
                'water_needs'      => 'high',
                'avg_yield_kg_acre'=> 1200,
                'notes'            => 'White gold of Pakistan. Major export earner. Sandy loam preferred.',
            ],
            [
                'crop_name'        => 'Sugarcane',
                'season'           => 'Kharif',
                'sowing_month'     => 'March',
                'harvest_month'    => 'December',
                'water_needs'      => 'very_high',
                'avg_yield_kg_acre'=> 40000,
                'notes'            => 'Long-duration crop (9–12 months). Needs 8–10 irrigations.',
            ],
            [
                'crop_name'        => 'Tomato',
                'season'           => 'Kharif',
                'sowing_month'     => 'July',
                'harvest_month'    => 'October',
                'water_needs'      => 'high',
                'avg_yield_kg_acre'=> 10000,
                'notes'            => 'High-value vegetable. Disease-prone; monitor weekly.',
            ],
            [
                'crop_name'        => 'Groundnut',
                'season'           => 'Kharif',
                'sowing_month'     => 'June',
                'harvest_month'    => 'September',
                'water_needs'      => 'medium',
                'avg_yield_kg_acre'=> 900,
                'notes'            => 'Oil and protein crop. Nodule bacteria fix nitrogen.',
            ],
            [
                'crop_name'        => 'Sorghum',
                'season'           => 'Kharif',
                'sowing_month'     => 'July',
                'harvest_month'    => 'October',
                'water_needs'      => 'low',
                'avg_yield_kg_acre'=> 1500,
                'notes'            => 'Drought-tolerant cereal and fodder crop for arid areas.',
            ],

            // ─── Zaid (Feb–May, spring / short season) ────────────────────────
            [
                'crop_name'        => 'Mango',
                'season'           => 'Zaid',
                'sowing_month'     => 'February',
                'harvest_month'    => 'July',
                'water_needs'      => 'medium',
                'avg_yield_kg_acre'=> 5000,
                'notes'            => 'King of fruits. Major export crop from Punjab and Sindh.',
            ],
            [
                'crop_name'        => 'Banana',
                'season'           => 'Zaid',
                'sowing_month'     => 'February',
                'harvest_month'    => 'October',
                'water_needs'      => 'very_high',
                'avg_yield_kg_acre'=> 15000,
                'notes'            => 'Year-round crop in warmer regions (Sindh). 8+ months to harvest.',
            ],
        ];

        foreach ($crops as $data) {
            SeasonalData::updateOrCreate(
                ['crop_name' => $data['crop_name'], 'season' => $data['season']],
                $data
            );
        }

        $this->command->info('SeasonalDataSeeder: ' . count($crops) . ' records seeded.');
    }
}
