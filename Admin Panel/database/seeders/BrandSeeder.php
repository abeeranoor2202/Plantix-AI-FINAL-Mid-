<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * BrandSeeder — seeds the brands table.
 *
 * The brands table was initially dropped by migration 2026_02_28_200001,
 * but was recreated by migration 2026_03_01_100001 because the Product model
 * and ShopController still reference brand_id / Brand model.
 */
class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Guard — if table doesn't exist (shouldn't happen after migrations), skip
        if (! DB::getSchemaBuilder()->hasTable('brands')) {
            return;
        }

        $now = Carbon::now();

        $brands = [
            'GreenGrow'   => 'Pakistan\'s leading seed and plant nutrition brand.',
            'PakSeed'     => 'High-yield certified seeds trusted by farmers nationwide.',
            'AgroShield'  => 'Effective crop protection chemicals for all field crops.',
            'KisanPro'    => 'Professional-grade farming tools for Pakistan\'s smallholder farmers.',
            'HydroFarm'   => 'Modern drip and hydroponic irrigation system manufacturer.',
            'NatureCraft' => 'Organic and bio-based inputs for sustainable agriculture.',
            'FieldMaster' => 'Heavy-duty equipment and sprayers for large-scale operations.',
            'CropSure'    => 'Integrated pest and disease management solutions.',
            'BioPak'      => 'Home of certified bio-fertilizers and beneficial microorganisms.',
            'TerraSol'    => 'Soil science company specialising in micro-nutrients and humic acids.',
        ];

        foreach ($brands as $name => $desc) {
            DB::table('brands')->insert([
                'name'       => $name,
                'slug'       => Str::slug($name),
                'logo'       => null,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
