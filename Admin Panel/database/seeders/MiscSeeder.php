<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MiscSeeder — seeds: on_board_slides
 *
 * Removed (tables dropped in cleanup migration):
 *  - currencies      (dropped)
 *  - store_filters   (dropped)
 *  - cms_pages       (dropped)
 */
class MiscSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── On-Boarding Slides ────────────────────────────────────────────────
        $slides = [
            ['title' => 'Welcome to Plantix',           'description' => 'Your one-stop agriculture marketplace. Buy seeds, tools, fertilizers and more.', 'sort_order' => 1],
            ['title' => 'Consult Expert Agronomists',   'description' => 'Book appointments with certified agricultural experts from across Pakistan.',     'sort_order' => 2],
            ['title' => 'AI Disease Detection',         'description' => 'Upload a photo of your crop to identify plant diseases instantly.',               'sort_order' => 3],
            ['title' => 'Smart Crop Planning',          'description' => 'Get personalised crop recommendations based on your soil and climate data.',       'sort_order' => 4],
            ['title' => 'Community Forum',              'description' => 'Join thousands of farmers sharing knowledge and solving problems together.',       'sort_order' => 5],
        ];
        foreach ($slides as $s) {
            DB::table('on_board_slides')->insert(array_merge($s, [
                'image' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }
    }
}
