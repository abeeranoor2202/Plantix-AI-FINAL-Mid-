<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            ['name' => 'Seeds & Planting',           'description' => 'High-quality seeds for all major crops — wheat, rice, vegetables, and fruits.', 'active' => 1, 'sort_order' => 1],
            ['name' => 'Fertilizers & Soil Nutrients','description' => 'Macro and micro nutrient fertilizers to improve crop yield and soil health.',  'active' => 1, 'sort_order' => 2],
            ['name' => 'Pesticides & Herbicides',     'description' => 'Safe and effective pest control solutions for all field and vegetable crops.',   'active' => 1, 'sort_order' => 3],
            ['name' => 'Farming Tools & Equipment',   'description' => 'Durable hand tools, soil meters, and manual equipment for the modern farmer.',   'active' => 1, 'sort_order' => 4],
            ['name' => 'Irrigation & Water Systems',  'description' => 'Drip kits, sprinkler systems, pumps, and pipes for water-efficient farming.',     'active' => 1, 'sort_order' => 5],
            ['name' => 'Greenhouse Supplies',         'description' => 'UV films, shade nets, hydroponic kits, and grow bags for protected cultivation.',  'active' => 1, 'sort_order' => 6],
            ['name' => 'Animal Feed & Livestock',     'description' => 'Nutritionally balanced feeds for poultry, dairy cattle, goats, and fish.',        'active' => 1, 'sort_order' => 7],
            ['name' => 'Organic & Natural Products',  'description' => 'Eco-friendly, chemical-free alternatives for sustainable and organic farming.',    'active' => 1, 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert(array_merge($cat, [
                'image'      => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
