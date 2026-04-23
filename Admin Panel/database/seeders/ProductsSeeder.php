<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ProductsSeeder — 55 agriculture products across 8 categories.
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Resolve IDs dynamically so the seeder works in isolation
        $vendors  = DB::table('vendors')->orderBy('id')->pluck('id')->toArray();
        $cats     = DB::table('categories')->pluck('id', 'name');

        $v1 = $vendors[0] ?? 1; // GreenHarvest
        $v2 = $vendors[1] ?? 2; // PakiAgroPro
        $v3 = $vendors[2] ?? 3; // FarmTech
        $v4 = $vendors[3] ?? 4; // KisanMart
        $v5 = $vendors[4] ?? 5; // AgroShield
        $v6 = $vendors[5] ?? 6; // NatureCrop

        $cSeeds     = $cats['Seeds & Planting']            ?? 1;
        $cFert      = $cats['Fertilizers & Soil Nutrients'] ?? 2;
        $cPest      = $cats['Pesticides & Herbicides']      ?? 3;
        $cTools     = $cats['Farming Tools & Equipment']    ?? 4;
        $cIrr       = $cats['Irrigation & Water Systems']   ?? 5;
        $cGreen     = $cats['Greenhouse Supplies']          ?? 6;
        $cFeed      = $cats['Animal Feed & Livestock']      ?? 7;
        $cOrg       = $cats['Organic & Natural Products']   ?? 8;

        $products = [
            // ── Seeds (8) ──────────────────────────────────────────────────────
            ['name' => 'Wheat Seed Galaxy-2013 (40kg)', 'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' => 2450, 'discount_price' => 2190, 'stock_quantity' => 500, 'is_featured' => 1, 'image' => 'plantix_images/wheat_Seed.jpg', 'description' => 'Certified wheat seed for irrigated Punjab fields with stable tillering and high grain recovery.'],
            ['name' => 'Hybrid Tomato F1 Seed (10g)',   'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' =>  850, 'discount_price' =>  750, 'stock_quantity' => 300, 'is_featured' => 0, 'description' => 'Determinate hybrid tomato with disease resistance. Produces firm, medium-sized fruits ideal for fresh market.'],
            ['name' => 'Basmati Rice Seed 1121 (5kg)',  'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' => 1850, 'discount_price' => null, 'stock_quantity' => 250, 'is_featured' => 1, 'image' => 'plantix_images/rice_Seed.png', 'description' => 'Aromatic long-grain Basmati 1121 seed for Kharif transplanting in irrigated paddy belts.'],
            ['name' => 'Jowar Hybrid Seed (5kg)',       'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' => 2950, 'discount_price' => 2700, 'stock_quantity' => 180, 'is_featured' => 0, 'image' => 'plantix_images/jawar hybrid.png', 'description' => 'Hybrid jowar seed with strong stand and good fodder plus grain performance in warm districts.'],
            ['name' => 'Cotton BT-121 Seed (500g)',     'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' => 4200, 'discount_price' => null, 'stock_quantity' => 120, 'is_featured' => 1, 'description' => 'Bt-transgenic cotton seed with built-in bollworm resistance. Ideal for Punjab cotton belt.'],
            ['name' => 'Sunflower Hybrid Seed (1kg)',   'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' => 2800, 'discount_price' => 2500, 'stock_quantity' => 90,  'is_featured' => 0, 'description' => 'High-oil hybrid sunflower for oilseed production. Short duration of 100 days, drought tolerant.'],
            ['name' => 'Onion Seed (50g)',              'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' =>  650, 'discount_price' =>  580, 'stock_quantity' => 400, 'is_featured' => 0, 'description' => 'Red onion variety with high pungency and excellent storage life. Suitable for Rabi season.'],
            ['name' => 'Kitchen Gardening Seed Combo',  'category_id' => $cSeeds, 'vendor_id' => $v1, 'price' =>  690, 'discount_price' => 620, 'stock_quantity' => 600, 'is_featured' => 0, 'image' => 'plantix_images/kitchen_gardening_Seeds.png', 'description' => 'Mixed kitchen-garden pack with seasonal vegetable seeds for small household beds and containers.'],

            // ── Fertilizers (8) ───────────────────────────────────────────────
            ['name' => 'Sona DAP Granular (50kg)',              'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 12450, 'discount_price' => null,  'stock_quantity' => 200, 'is_featured' => 1, 'image' => 'plantix_images/sona dap.png', 'description' => 'Di-Ammonium Phosphate (18-46-0) for basal phosphorus and starter nitrogen in major field crops.'],
            ['name' => 'Sona Urea Granular (50kg)',             'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 4550,  'discount_price' => null,  'stock_quantity' => 300, 'is_featured' => 1, 'image' => 'plantix_images/sona urea.png', 'description' => '46% nitrogen urea for split top dressing in wheat, rice, maize, and fodder.'],
            ['name' => 'Bloom Super NPK (25kg)',                'category_id' => $cFert, 'vendor_id' => $v4, 'price' => 5850,  'discount_price' => 5400,  'stock_quantity' => 150, 'is_featured' => 0, 'image' => 'plantix_images/bloom super npk.png', 'description' => 'Balanced NPK blend for flowering and vegetative support in vegetables and fruit crops.'],
            ['name' => 'Nitrophos Sarsabz NP (50kg)',           'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 9950,  'discount_price' => null,  'stock_quantity' => 100, 'is_featured' => 0, 'image' => 'plantix_images/nitrophos-sarsabz.png', 'description' => 'NP compound fertilizer used as basal dose where both nitrogen and phosphorus are needed.'],
            ['name' => 'Ammonium Sulphate 21% (50kg)',          'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 5200,  'discount_price' => 4900,  'stock_quantity' => 80,  'is_featured' => 0, 'image' => 'plantix_images/Ammonium-Sulphate.jpg', 'description' => 'Ammonium sulphate source for nitrogen plus sulphur, useful in alkaline and sulphur-deficient soils.'],
            ['name' => 'Organic Compost Premium (40kg)',         'category_id' => $cFert, 'vendor_id' => $v6, 'price' => 1800,  'discount_price' => 1650,  'stock_quantity' => 250, 'is_featured' => 0, 'description' => 'Fully composted organic matter enriched with vermicast. Improves soil structure, water retention, and microbial activity.'],
            ['name' => 'Magnesium Chloride Foliar (1kg)',       'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 1320,  'discount_price' => null,  'stock_quantity' => 350, 'is_featured' => 0, 'image' => 'plantix_images/magnesium chloride.jpg', 'description' => 'Magnesium correction for chlorosis-prone crops and high-demand vegetative growth stages.'],
            ['name' => 'Sona FF Foliar Feed (1L)',              'category_id' => $cFert, 'vendor_id' => $v2, 'price' => 1080,  'discount_price' =>  960,  'stock_quantity' => 200, 'is_featured' => 0, 'image' => 'plantix_images/sona ff.jpg', 'description' => 'Foliar feed for quick nutrient recovery during stress, transplant shock, and early flowering.'],

            // ── Pesticides (8) ────────────────────────────────────────────────
            ['name' => 'Keera Maar Insecticide (500ml)',      'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  890, 'discount_price' =>  790, 'stock_quantity' => 300, 'is_featured' => 0, 'image' => 'plantix_images/keera_maar.jpg', 'description' => 'Farmer-grade broad-spectrum insecticide for chewing and sucking pest pressure in vegetables and cotton.'],
            ['name' => 'Glyphosate 41% SL (1L)',             'category_id' => $cPest, 'vendor_id' => $v5, 'price' => 1400, 'discount_price' => null, 'stock_quantity' => 200, 'is_featured' => 0, 'description' => 'Systemic non-selective herbicide for pre-planting weed control in orchards, cotton fields, and roadways.'],
            ['name' => 'Copper Hydroxide 77% WP (500g)',     'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  720, 'discount_price' =>  650, 'stock_quantity' => 180, 'is_featured' => 0, 'description' => 'Broad-spectrum copper fungicide for downy mildew, early blight, and bacterial spot in vegetables.'],
            ['name' => 'Imidacloprid 200SC (100ml)',         'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  950, 'discount_price' =>  850, 'stock_quantity' => 250, 'is_featured' => 1, 'description' => 'Systemic neonicotinoid insecticide for whitefly, aphids, and thrips. Soil drench or foliar application.'],
            ['name' => 'Chlorpyrifos 40% EC (500ml)',        'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  680, 'discount_price' => null, 'stock_quantity' => 320, 'is_featured' => 0, 'description' => 'Contact and stomach organophosphate insecticide effective against stem borers and soil insects.'],
            ['name' => 'Mancozeb 80% WP (1kg)',              'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  540, 'discount_price' =>  490, 'stock_quantity' => 400, 'is_featured' => 0, 'description' => 'Protective multi-site fungicide for early and late blight, Alternaria, and rust diseases.'],
            ['name' => 'Weed Control Granules (5kg)',        'category_id' => $cPest, 'vendor_id' => $v5, 'price' => 1100, 'discount_price' => null, 'stock_quantity' => 140, 'is_featured' => 0, 'description' => 'Pre-emergence granular herbicide for rice paddies. Controls annual grasses and sedges effectively.'],
            ['name' => 'Metaldehyde Bait 5% (500g)',         'category_id' => $cPest, 'vendor_id' => $v5, 'price' =>  420, 'discount_price' => null, 'stock_quantity' => 220, 'is_featured' => 0, 'description' => 'Molluscicide bait pellets for slug and snail control in vegetables, strawberries, and wheat.'],

            // ── Farming Tools (8) ─────────────────────────────────────────────
            ['name' => 'Heavy Duty Steel Spade',          'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 1800, 'discount_price' => 1580, 'stock_quantity' => 80,  'is_featured' => 0, 'description' => 'Forged steel spade with anti-rust coating and ergonomic ash wood handle. Ideal for sandy and clay soils.'],
            ['name' => 'Garden Trowel Set (4 Piece)',      'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 1200, 'discount_price' => null, 'stock_quantity' => 120, 'is_featured' => 0, 'description' => 'Stainless steel trowel set including planting trowel, weeder, transplanter, and cultivator.'],
            ['name' => 'Bypass Pruning Shears',            'category_id' => $cTools, 'vendor_id' => $v3, 'price' =>  950, 'discount_price' =>  820, 'stock_quantity' => 150, 'is_featured' => 0, 'description' => 'High-carbon bypass pruning shears for clean cuts on branches up to 2cm diameter. Non-slip soft grip handle.'],
            ['name' => 'Irrigation Shovel Kana',           'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 1400, 'discount_price' => null, 'stock_quantity' => 60,  'is_featured' => 0, 'description' => 'Traditional Pakistani irrigation shovel (kana) used for channel opening and water management in fields.'],
            ['name' => 'Wheel Hoe Cultivator',             'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 8500, 'discount_price' => 7800, 'stock_quantity' => 25,  'is_featured' => 1, 'description' => 'Single-wheel manual hoe cultivator for between-row weed control in vegetables. Adjustable tine widths.'],
            ['name' => 'Digital Soil pH Meter',            'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 3200, 'discount_price' => 2900, 'stock_quantity' => 55,  'is_featured' => 1, 'description' => 'Professional 3-in-1 soil tester measuring pH, moisture, and light intensity. No batteries required.'],
            ['name' => 'Manual Seed Drill (2-Row)',        'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 12000,'discount_price' =>11000, 'stock_quantity' => 15,  'is_featured' => 0, 'description' => 'Hand-pushed 2-row seed drill for accurate planting depth and spacing. Suitable for wheat, maize, and vegetables.'],
            ['name' => 'Knapsack Crop Sprayer 16L',        'category_id' => $cTools, 'vendor_id' => $v3, 'price' => 4500, 'discount_price' => 4000, 'stock_quantity' => 70,  'is_featured' => 0, 'description' => 'Manual backpack sprayer 16L with adjustable brass nozzle. For pesticide and fertilizer foliar application.'],

            // ── Irrigation (6) ────────────────────────────────────────────────
            ['name' => 'Drip Irrigation Kit (1 Kanal)',    'category_id' => $cIrr, 'vendor_id' => $v3, 'price' => 22000,'discount_price' =>19500, 'stock_quantity' => 20,  'is_featured' => 1, 'description' => 'Complete drip irrigation kit for 1 kanal (500 sq. metres). Includes mainline, lateral pipes, drippers, and filter.'],
            ['name' => 'Rotary Sprinkler Set (12 Units)',  'category_id' => $cIrr, 'vendor_id' => $v3, 'price' => 8500, 'discount_price' => 7800, 'stock_quantity' => 30,  'is_featured' => 0, 'description' => 'Heavy-duty rotary sprinkler kit covering 10m radius. Suitable for lawns, orchards, and row crops.'],
            ['name' => 'HDPE Main Line Pipe 32mm (100m)',  'category_id' => $cIrr, 'vendor_id' => $v3, 'price' => 6800, 'discount_price' => null, 'stock_quantity' => 50,  'is_featured' => 0, 'description' => '32mm high-density polyethylene irrigation mainline pipe. Pressure rated 6 bar, UV stabilised.'],
            ['name' => 'Agricultural Water Pump 1.5HP',    'category_id' => $cIrr, 'vendor_id' => $v3, 'price' =>18000,'discount_price' =>16500, 'stock_quantity' => 18,  'is_featured' => 1, 'description' => 'Single-phase 1.5HP centrifugal pump for tube well and canal-based irrigation. Max head 35m.'],
            ['name' => 'Rain Gun Sprinkler (1.5" Inlet)',  'category_id' => $cIrr, 'vendor_id' => $v3, 'price' => 9500, 'discount_price' => null, 'stock_quantity' => 22,  'is_featured' => 0, 'description' => 'Heavy-duty brass rain gun covering 30m radius. Ideal for sugar cane, wheat, and large orchards.'],
            ['name' => 'Soaker Hose 15m',                 'category_id' => $cIrr, 'vendor_id' => $v3, 'price' => 1600, 'discount_price' => 1400, 'stock_quantity' => 90,  'is_featured' => 0, 'description' => 'Porous rubber soaker hose for slow deep watering at root level. Reduces evaporation by 70%.'],

            // ── Greenhouse (4) ────────────────────────────────────────────────
            ['name' => 'Greenhouse UV Film (200 micron, 10x30m)', 'category_id' => $cGreen, 'vendor_id' => $v6, 'price' => 28000,'discount_price' =>25000, 'stock_quantity' => 10,  'is_featured' => 0, 'description' => '5-layer UV-stabilised 200 micron greenhouse film. Provides 90% light transmission and 4-year outdoor durability.'],
            ['name' => 'Shade Net 50% (4x10m)',                    'category_id' => $cGreen, 'vendor_id' => $v6, 'price' => 4200, 'discount_price' => 3800, 'stock_quantity' => 40,  'is_featured' => 0, 'description' => '50% black HDPE shade net for nurseries, vegetables, and fruit orchards. Reduces temperature by 8–10°C.'],
            ['name' => 'Greenhouse Plastic Clips (100 pcs)',       'category_id' => $cGreen, 'vendor_id' => $v6, 'price' =>  650, 'discount_price' => null, 'stock_quantity' => 200, 'is_featured' => 0, 'description' => 'Heavy-duty spring clips for attaching shade nets and plastic film to greenhouse frames.'],
            ['name' => 'Mini Hydroponic NFT Kit (20 Plants)',      'category_id' => $cGreen, 'vendor_id' => $v6, 'price' => 15000,'discount_price' =>13500, 'stock_quantity' => 12,  'is_featured' => 1, 'description' => 'Nutrient Film Technique kit for leafy greens and herbs. Includes channels, pump, reservoir, net cups, and nutrients.'],

            // ── Animal Feed (5) ───────────────────────────────────────────────
            ['name' => 'Broiler Starter Feed (25kg)',      'category_id' => $cFeed, 'vendor_id' => $v4, 'price' => 3800, 'discount_price' => 3500, 'stock_quantity' => 100, 'is_featured' => 0, 'description' => 'High protein (22%) crumble feed for day-old to 14-day broiler chicks. Includes coccidiostat.'],
            ['name' => 'Dairy Cattle Concentrate (50kg)',  'category_id' => $cFeed, 'vendor_id' => $v4, 'price' => 7200, 'discount_price' => null, 'stock_quantity' => 60,  'is_featured' => 0, 'description' => 'Balanced energy-protein dairy concentrate boosting milk yield. Contains vitamins, minerals, and bypass protein.'],
            ['name' => 'Goat Feed Pellets (25kg)',         'category_id' => $cFeed, 'vendor_id' => $v4, 'price' => 3200, 'discount_price' => 2900, 'stock_quantity' => 80,  'is_featured' => 0, 'description' => 'Multi-ingredient pellet feed for goats. Supports growth, reproduction, and lactation in all breeds.'],
            ['name' => 'Layer Hen Feed (50kg)',            'category_id' => $cFeed, 'vendor_id' => $v4, 'price' => 5800, 'discount_price' => null, 'stock_quantity' => 70,  'is_featured' => 0, 'description' => 'Mash feed optimised for egg production layers 18+ weeks. Contains shell-forming calcium 4%.'],
            ['name' => 'Fish Pellets (20kg)',              'category_id' => $cFeed, 'vendor_id' => $v4, 'price' => 4500, 'discount_price' => 4100, 'stock_quantity' => 40,  'is_featured' => 0, 'description' => 'Sinking pellets (3mm) for catfish, tilapia, and Rohu. 32% protein, balanced amino acid profile.'],

            // ── Organic (8) ───────────────────────────────────────────────────
            ['name' => 'Vermicompost Premium (20kg)',          'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 1600, 'discount_price' => 1400, 'stock_quantity' => 200, 'is_featured' => 1, 'description' => 'Pure earthworm vermicompost enriched with humic acid, enzymes, and beneficial bacteria. Improves soil fertility organically.'],
            ['name' => 'Neem Azal Biopesticide (1L)',          'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 1800, 'discount_price' => 1620, 'stock_quantity' => 150, 'is_featured' => 0, 'description' => 'Cold-pressed neem oil extract (0.3% Azadirachtin). Controls aphids, whitefly, mites, and nematodes.'],
            ['name' => 'Bio-Enzyme Plant Activator (500ml)',   'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 1200, 'discount_price' => null, 'stock_quantity' => 180, 'is_featured' => 0, 'description' => 'Fermented plant enzyme activator improving nutrient uptake, stress tolerance, and root development.'],
            ['name' => 'Seaweed Extract Liquid (1L)',           'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 2200, 'discount_price' => 2000, 'stock_quantity' => 120, 'is_featured' => 1, 'description' => 'ECKLONIA maxima kelp extract with natural plant growth hormones (auxins, cytokinins, gibberellins). 100% organic.'],
            ['name' => 'Humic Acid 70% Powder (1kg)',          'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 1400, 'discount_price' => 1260, 'stock_quantity' => 200, 'is_featured' => 0, 'description' => 'Leonardite-derived 70% humic acid for soil conditioning. Chelates micro-nutrients and improves CEC.'],
            ['name' => 'Mycorrhizal Fungi Inoculant (500g)',   'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 2800, 'discount_price' => null, 'stock_quantity' => 80,  'is_featured' => 0, 'description' => 'Mixed mycorrhiza containing Glomus species. Applied to seed rows to boost phosphorus uptake and drought tolerance.'],
            ['name' => 'Beneficial Bacteria WSP (1kg)',        'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 2400, 'discount_price' => 2160, 'stock_quantity' => 100, 'is_featured' => 0, 'description' => 'Water-soluble powder with Bacillus subtilis + Trichoderma harzianum for biological disease suppression.'],
            ['name' => 'Biodegradable Mulch Film (1.2m x 200m)', 'category_id' => $cOrg, 'vendor_id' => $v6, 'price' => 5500, 'discount_price' => 5000, 'stock_quantity' => 35,  'is_featured' => 0, 'description' => 'Oxo-biodegradable black mulch film for weed suppression and moisture retention in vegetable beds.'],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert(array_merge($p, [
                'slug'        => Str::slug($p['name']),
                'is_active'   => 1,
                'status'      => 'active',   // column added by migration 2026_03_01_999999
                'sort_order'  => 0,
                'created_at'  => $now->copy()->subDays(rand(1, 180)),
                'updated_at'  => $now,
            ]));
        }
    }
}


