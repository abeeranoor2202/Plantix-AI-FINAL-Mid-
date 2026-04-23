<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ProductsSeeder — 29 agriculture products, each with a confirmed image file.
 *
 * Image path format: plantix_images/<filename>
 * All files physically exist in public/plantix_images/.
 *
 * Removed products that had no image (Irrigation, Greenhouse, Animal Feed,
 * all Organic, and 3 Farming Tools: pH Meter, Seed Drill, Knapsack Sprayer).
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $vendors = DB::table('vendors')->orderBy('id')->pluck('id')->toArray();
        $cats    = DB::table('categories')->pluck('id', 'name');

        $v1 = $vendors[0] ?? 1; // GreenHarvest Seeds
        $v2 = $vendors[1] ?? 2; // PakiAgroPro Fertilizers
        $v3 = $vendors[2] ?? 3; // FarmTech Tools
        $v4 = $vendors[3] ?? 4; // KisanMart
        $v5 = $vendors[4] ?? 5; // AgroShield Pesticides

        $cSeeds = $cats['Seeds & Planting']             ?? 1;
        $cFert  = $cats['Fertilizers & Soil Nutrients'] ?? 2;
        $cPest  = $cats['Pesticides & Herbicides']      ?? 3;
        $cTools = $cats['Farming Tools & Equipment']    ?? 4;

        $products = [

            // ── Seeds (8) — all have images ──────────────────────────────────
            [
                'name'           => 'Wheat Seed Galaxy-2013 (40kg)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 2450,
                'discount_price' => 2190,
                'stock_quantity' => 500,
                'is_featured'    => 1,
                'image'          => 'plantix_images/wheat_Seed.jpg',
                'description'    => 'Certified wheat seed for irrigated Punjab fields with stable tillering and high grain recovery.',
            ],
            [
                'name'           => 'Hybrid Tomato F1 Seed (10g)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 850,
                'discount_price' => 750,
                'stock_quantity' => 300,
                'is_featured'    => 0,
                'image'          => 'plantix_images/hybrid_tomato_seeds.png',
                'description'    => 'Determinate hybrid tomato with disease resistance. Produces firm, medium-sized fruits ideal for fresh market.',
            ],
            [
                'name'           => 'Basmati Rice Seed 1121 (5kg)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 1850,
                'discount_price' => null,
                'stock_quantity' => 250,
                'is_featured'    => 1,
                'image'          => 'plantix_images/rice_Seed.png',
                'description'    => 'Aromatic long-grain Basmati 1121 seed for Kharif transplanting in irrigated paddy belts.',
            ],
            [
                'name'           => 'Jowar Hybrid Seed (5kg)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 2950,
                'discount_price' => 2700,
                'stock_quantity' => 180,
                'is_featured'    => 0,
                'image'          => 'plantix_images/jawar hybrid.png',
                'description'    => 'Hybrid jowar seed with strong stand and good fodder plus grain performance in warm districts.',
            ],
            [
                'name'           => 'Cotton BT-121 Seed (500g)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 4200,
                'discount_price' => null,
                'stock_quantity' => 120,
                'is_featured'    => 1,
                'image'          => 'plantix_images/cotton_bt121_seed.png',
                'description'    => 'Bt-transgenic cotton seed with built-in bollworm resistance. Ideal for Punjab cotton belt.',
            ],
            [
                'name'           => 'Sunflower Hybrid Seed (1kg)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 2800,
                'discount_price' => 2500,
                'stock_quantity' => 90,
                'is_featured'    => 0,
                'image'          => 'plantix_images/sunflower_hybrid_seed.png',
                'description'    => 'High-oil hybrid sunflower for oilseed production. Short duration of 100 days, drought tolerant.',
            ],
            [
                'name'           => 'Onion Seed (50g)',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 650,
                'discount_price' => 580,
                'stock_quantity' => 400,
                'is_featured'    => 0,
                'image'          => 'plantix_images/onion_seed.png',
                'description'    => 'Red onion variety with high pungency and excellent storage life. Suitable for Rabi season.',
            ],
            [
                'name'           => 'Kitchen Gardening Seed Combo',
                'category_id'    => $cSeeds,
                'vendor_id'      => $v1,
                'price'          => 690,
                'discount_price' => 620,
                'stock_quantity' => 600,
                'is_featured'    => 0,
                'image'          => 'plantix_images/kitchen_gardening_Seeds.png',
                'description'    => 'Mixed kitchen-garden pack with seasonal vegetable seeds for small household beds and containers.',
            ],

            // ── Fertilizers (8) — all have images ────────────────────────────
            [
                'name'           => 'Sona DAP Granular (50kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 12450,
                'discount_price' => null,
                'stock_quantity' => 200,
                'is_featured'    => 1,
                'image'          => 'plantix_images/sona dap.png',
                'description'    => 'Di-Ammonium Phosphate (18-46-0) for basal phosphorus and starter nitrogen in major field crops.',
            ],
            [
                'name'           => 'Sona Urea Granular (50kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 4550,
                'discount_price' => null,
                'stock_quantity' => 300,
                'is_featured'    => 1,
                'image'          => 'plantix_images/sona urea.png',
                'description'    => '46% nitrogen urea for split top dressing in wheat, rice, maize, and fodder.',
            ],
            [
                'name'           => 'Bloom Super NPK (25kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v4,
                'price'          => 5850,
                'discount_price' => 5400,
                'stock_quantity' => 150,
                'is_featured'    => 0,
                'image'          => 'plantix_images/bloom super npk.png',
                'description'    => 'Balanced NPK blend for flowering and vegetative support in vegetables and fruit crops.',
            ],
            [
                'name'           => 'Nitrophos Sarsabz NP (50kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 9950,
                'discount_price' => null,
                'stock_quantity' => 100,
                'is_featured'    => 0,
                'image'          => 'plantix_images/nitrophos-sarsabz.png',
                'description'    => 'NP compound fertilizer used as basal dose where both nitrogen and phosphorus are needed.',
            ],
            [
                'name'           => 'Ammonium Sulphate 21% (50kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 5200,
                'discount_price' => 4900,
                'stock_quantity' => 80,
                'is_featured'    => 0,
                'image'          => 'plantix_images/Ammonium-Sulphate.jpg',
                'description'    => 'Ammonium sulphate source for nitrogen plus sulphur, useful in alkaline and sulphur-deficient soils.',
            ],
            [
                'name'           => 'Organic Compost Premium (40kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 1800,
                'discount_price' => 1650,
                'stock_quantity' => 250,
                'is_featured'    => 0,
                'image'          => 'plantix_images/organic_compost_premium.png',
                'description'    => 'Fully composted organic matter enriched with vermicast. Improves soil structure, water retention, and microbial activity.',
            ],
            [
                'name'           => 'Magnesium Chloride Foliar (1kg)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 1320,
                'discount_price' => null,
                'stock_quantity' => 350,
                'is_featured'    => 0,
                'image'          => 'plantix_images/magnesium chloride.jpg',
                'description'    => 'Magnesium correction for chlorosis-prone crops and high-demand vegetative growth stages.',
            ],
            [
                'name'           => 'Sona FF Foliar Feed (1L)',
                'category_id'    => $cFert,
                'vendor_id'      => $v2,
                'price'          => 1080,
                'discount_price' => 960,
                'stock_quantity' => 200,
                'is_featured'    => 0,
                'image'          => 'plantix_images/sona ff.jpg',
                'description'    => 'Foliar feed for quick nutrient recovery during stress, transplant shock, and early flowering.',
            ],

            // ── Pesticides (8) — all have images ─────────────────────────────
            [
                'name'           => 'Keera Maar Insecticide (500ml)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 890,
                'discount_price' => 790,
                'stock_quantity' => 300,
                'is_featured'    => 0,
                'image'          => 'plantix_images/keera_maar.jpg',
                'description'    => 'Farmer-grade broad-spectrum insecticide for chewing and sucking pest pressure in vegetables and cotton.',
            ],
            [
                'name'           => 'Glyphosate 41% SL (1L)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 1400,
                'discount_price' => null,
                'stock_quantity' => 200,
                'is_featured'    => 0,
                'image'          => 'plantix_images/glyphosate_herbicide.png',
                'description'    => 'Systemic non-selective herbicide for pre-planting weed control in orchards, cotton fields, and roadways.',
            ],
            [
                'name'           => 'Copper Hydroxide 77% WP (500g)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 720,
                'discount_price' => 650,
                'stock_quantity' => 180,
                'is_featured'    => 0,
                'image'          => 'plantix_images/copper_hydroxide_fungicide.png',
                'description'    => 'Broad-spectrum copper fungicide for downy mildew, early blight, and bacterial spot in vegetables.',
            ],
            [
                'name'           => 'Imidacloprid 200SC (100ml)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 950,
                'discount_price' => 850,
                'stock_quantity' => 250,
                'is_featured'    => 1,
                'image'          => 'plantix_images/imidacloprid_insecticide.png',
                'description'    => 'Systemic neonicotinoid insecticide for whitefly, aphids, and thrips. Soil drench or foliar application.',
            ],
            [
                'name'           => 'Chlorpyrifos 40% EC (500ml)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 680,
                'discount_price' => null,
                'stock_quantity' => 320,
                'is_featured'    => 0,
                'image'          => 'plantix_images/chlorpyrifos_insecticide.png',
                'description'    => 'Contact and stomach organophosphate insecticide effective against stem borers and soil insects.',
            ],
            [
                'name'           => 'Mancozeb 80% WP (1kg)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 540,
                'discount_price' => 490,
                'stock_quantity' => 400,
                'is_featured'    => 0,
                'image'          => 'plantix_images/mancozeb_fungicide.png',
                'description'    => 'Protective multi-site fungicide for early and late blight, Alternaria, and rust diseases.',
            ],
            [
                'name'           => 'Weed Control Granules (5kg)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 1100,
                'discount_price' => null,
                'stock_quantity' => 140,
                'is_featured'    => 0,
                'image'          => 'plantix_images/weed_control_granules.png',
                'description'    => 'Pre-emergence granular herbicide for rice paddies. Controls annual grasses and sedges effectively.',
            ],
            [
                'name'           => 'Metaldehyde Bait 5% (500g)',
                'category_id'    => $cPest,
                'vendor_id'      => $v5,
                'price'          => 420,
                'discount_price' => null,
                'stock_quantity' => 220,
                'is_featured'    => 0,
                'image'          => 'plantix_images/metaldehyde_bait.png',
                'description'    => 'Molluscicide bait pellets for slug and snail control in vegetables, strawberries, and wheat.',
            ],

            // ── Farming Tools (5) — only those with confirmed images ──────────
            // Removed: Digital Soil pH Meter, Manual Seed Drill, Knapsack Crop Sprayer
            [
                'name'           => 'Heavy Duty Steel Spade',
                'category_id'    => $cTools,
                'vendor_id'      => $v3,
                'price'          => 1800,
                'discount_price' => 1580,
                'stock_quantity' => 80,
                'is_featured'    => 0,
                'image'          => 'plantix_images/steel_spade.png',
                'description'    => 'Forged steel spade with anti-rust coating and ergonomic ash wood handle. Ideal for sandy and clay soils.',
            ],
            [
                'name'           => 'Garden Trowel Set (4 Piece)',
                'category_id'    => $cTools,
                'vendor_id'      => $v3,
                'price'          => 1200,
                'discount_price' => null,
                'stock_quantity' => 120,
                'is_featured'    => 0,
                'image'          => 'plantix_images/garden_trowel_set.png',
                'description'    => 'Stainless steel trowel set including planting trowel, weeder, transplanter, and cultivator.',
            ],
            [
                'name'           => 'Bypass Pruning Shears',
                'category_id'    => $cTools,
                'vendor_id'      => $v3,
                'price'          => 950,
                'discount_price' => 820,
                'stock_quantity' => 150,
                'is_featured'    => 0,
                'image'          => 'plantix_images/bypass_pruning_shears.png',
                'description'    => 'High-carbon bypass pruning shears for clean cuts on branches up to 2cm diameter. Non-slip soft grip handle.',
            ],
            [
                'name'           => 'Irrigation Shovel Kana',
                'category_id'    => $cTools,
                'vendor_id'      => $v3,
                'price'          => 1400,
                'discount_price' => null,
                'stock_quantity' => 60,
                'is_featured'    => 0,
                'image'          => 'plantix_images/irrigation_shovel_kana.png',
                'description'    => 'Traditional Pakistani irrigation shovel (kana) used for channel opening and water management in fields.',
            ],
            [
                'name'           => 'Wheel Hoe Cultivator',
                'category_id'    => $cTools,
                'vendor_id'      => $v3,
                'price'          => 8500,
                'discount_price' => 7800,
                'stock_quantity' => 25,
                'is_featured'    => 1,
                'image'          => 'plantix_images/wheel_hoe_cultivator.png',
                'description'    => 'Single-wheel manual hoe cultivator for between-row weed control in vegetables. Adjustable tine widths.',
            ],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert(array_merge($p, [
                'slug'        => Str::slug($p['name']),
                'is_active'   => 1,
                'status'      => 'active',
                'sort_order'  => 0,
                'created_at'  => $now->copy()->subDays(rand(1, 180)),
                'updated_at'  => $now,
            ]));
        }
    }
}
