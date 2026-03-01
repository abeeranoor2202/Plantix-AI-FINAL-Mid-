<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForumCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            ['name' => 'Crop Diseases & Pest Control',  'description' => 'Identify and treat diseases, fungi, insects, and weeds affecting your crops.', 'sort_order' => 1],
            ['name' => 'Soil Health & Fertilization',   'description' => 'Discuss soil testing, pH management, composting, and fertiliser programs.', 'sort_order' => 2],
            ['name' => 'Irrigation & Water Management', 'description' => 'Share tips on drip irrigation, canal management, rainwater harvesting.', 'sort_order' => 3],
            ['name' => 'Seeds & Crop Varieties',        'description' => 'Compare varieties, share germination tips, discuss seed treatments.', 'sort_order' => 4],
            ['name' => 'Organic Farming',               'description' => 'Biofertilisers, natural pest control, certifications, and composting.', 'sort_order' => 5],
            ['name' => 'Market & Pricing',              'description' => 'Current crop prices, mandi rates, export news, and market trends.', 'sort_order' => 6],
            ['name' => 'Government Schemes & Loans',    'description' => 'Kisan card, PMKSY, agriculture credit, and subsidy programs.', 'sort_order' => 7],
            ['name' => 'General Discussion',            'description' => 'Anything related to farming that doesn\'t fit other categories.', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            DB::table('forum_categories')->insert(array_merge($cat, [
                'slug'       => Str::slug($cat['name']),
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
