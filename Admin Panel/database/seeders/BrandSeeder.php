<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
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
            'TerraSol'    => 'Soil science company specializing in micro-nutrients and humic acids.',
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
