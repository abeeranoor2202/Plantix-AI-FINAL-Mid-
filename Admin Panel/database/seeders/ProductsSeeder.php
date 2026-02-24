<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now     = Carbon::now();
        $vendors = DB::table('vendors')->get()->keyBy('title');
        $cats    = DB::table('categories')->pluck('id', 'name');

        // ────────────────────────────────────────────────────────
        //  Helper: insert product + optional attribute variants
        // ────────────────────────────────────────────────────────
        $insert = function (
            string $vendor, string $category, string $name,
            string $desc, float $price, ?float $discountPrice,
            bool $featured, array $variants = []
        ) use ($now, $vendors, $cats, &$insert) {
            $v = $vendors[$vendor] ?? null;
            if (! $v) return;

            $productId = DB::table('products')->insertGetId([
                'vendor_id'      => $v->id,
                'category_id'    => $cats[$category] ?? null,
                'name'           => $name,
                'description'    => $desc,
                'price'          => $price,
                'discount_price' => $discountPrice,
                'is_active'      => true,
                'is_featured'    => $featured,
                'sort_order'     => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            foreach ($variants as $var) {
                DB::table('product_attributes')->insert([
                    'product_id' => $productId,
                    'name'       => $var['name'],
                    'price'      => $var['price'],
                    'type'       => $var['type'] ?? 'single',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        };

        // ────────────────────────────────────────────────────────
        //  SEEDS — Punjab Seeds Centre (Lahore)
        // ────────────────────────────────────────────────────────
        $insert('Punjab Seeds Centre', 'Seeds',
            'Fauji Hybrid Maize Seed (FH-1046)',
            'High-yielding hybrid maize seed certified by PARC. Yield potential 8–10 tonnes/acre. Suitable for Canal irrigated areas of Punjab.',
            2800.00, 2500.00, true,
            [['name' => '1 kg bag', 'price' => 2800], ['name' => '5 kg bag', 'price' => 13000], ['name' => '10 kg bag', 'price' => 24000]]
        );

        $insert('Punjab Seeds Centre', 'Seeds',
            'NIBGE Bt Cotton Seed (CIM-598)',
            'Bollworm-resistant Bt cotton seed variety developed by NIBGE. High ginning percentage and fibre strength for Punjab cotton belt.',
            3500.00, 3200.00, true,
            [['name' => '2.5 kg bag', 'price' => 3500], ['name' => '5 kg bag', 'price' => 6800]]
        );

        $insert('Punjab Seeds Centre', 'Seeds',
            'PARC Wheat Seed (AARI-2011)',
            'Rust-resistant, high-yielding wheat variety. Recommended for irrigated conditions of Punjab and Sindh. Yield 50–60 mounds/acre.',
            1200.00, 1050.00, false,
            [['name' => '20 kg bag', 'price' => 1200], ['name' => '40 kg bag', 'price' => 2300]]
        );

        $insert('Punjab Seeds Centre', 'Seeds',
            'Desi Basmati Rice Seed (Super Basmati)',
            'Aromatic, fine-grain Super Basmati variety. Preferred by rice millers. Suited for Gujranwala & Sheikhupura paddies.',
            2200.00, null, false,
            [['name' => '5 kg bag', 'price' => 2200], ['name' => '10 kg bag', 'price' => 4200]]
        );

        $insert('Punjab Seeds Centre', 'Seeds',
            'Sunflower Hybrid Seed (Hysun-33)',
            'Imported hybrid sunflower seed. Short duration, tolerates Punjabi summer heat. Oil content 45%.',
            4500.00, 4100.00, true,
            [['name' => '500 g can', 'price' => 4500]]
        );

        // ────────────────────────────────────────────────────────
        //  FERTILIZERS — Sona Fertilizer Store (Faisalabad)
        // ────────────────────────────────────────────────────────
        $insert('Sona Fertilizer Store', 'Fertilizers',
            'Engro Urea (46% N)',
            'Granular urea fertilizer – most widely used nitrogenous fertilizer in Pakistan. ENGRO brand, certified FCCI standard bag.',
            4200.00, 4000.00, true,
            [['name' => '50 kg bag', 'price' => 4200], ['name' => '10 kg bag', 'price' => 900]]
        );

        $insert('Sona Fertilizer Store', 'Fertilizers',
            'Fauji DAP (18-46-0)',
            'Di-ammonium Phosphate fertilizer by Fauji Fertilizer Company. Boosts root development and early flowering.',
            7800.00, 7400.00, true,
            [['name' => '50 kg bag', 'price' => 7800]]
        );

        $insert('Sona Fertilizer Store', 'Fertilizers',
            'SOP Potassium Sulphate (50% K2O)',
            'Sulphate of Potash – chloride-free potash source. Ideal for fruits, vegetables and tobacco in Pakistan.',
            6500.00, null, false,
            [['name' => '25 kg bag', 'price' => 6500], ['name' => '50 kg bag', 'price' => 12500]]
        );

        $insert('Sona Fertilizer Store', 'Fertilizers',
            'Nitrophos NP (23-23-0)',
            'Balanced nitrogen-phosphate fertilizer for basal application. Reduces need for separate urea + DAP application.',
            5200.00, 4900.00, false,
            [['name' => '50 kg bag', 'price' => 5200]]
        );

        $insert('Sona Fertilizer Store', 'Fertilizers',
            'Zinc Sulphate Micronutrient (33% Zn)',
            'Corrects zinc deficiency in rice, wheat and maize. Granular form. Approved by Faisalabad Agriculture University trials.',
            1800.00, 1600.00, false,
            [['name' => '5 kg bag', 'price' => 1800], ['name' => '25 kg bag', 'price' => 8500]]
        );

        // ────────────────────────────────────────────────────────
        //  PESTICIDES — Al-Rehman Agro Supplies (Multan)
        // ────────────────────────────────────────────────────────
        $insert('Al-Rehman Agro Supplies', 'Pesticides',
            'Syngenta Karate 2.5% WG (Lambda-Cyhalothrin)',
            'Broad-spectrum insecticide/pesticide for cotton, wheat and rice. Controls armyworm, thrips and leafhoppers.',
            1450.00, 1300.00, true,
            [['name' => '100 g pack', 'price' => 1450], ['name' => '500 g pack', 'price' => 6800]]
        );

        $insert('Al-Rehman Agro Supplies', 'Pesticides',
            'FMC Tilt 250 EC (Propiconazole)',
            'Systemic fungicide effective against brown rust, yellow rust and leaf spot in wheat. Absorbed quickly after rain.',
            2100.00, null, false,
            [['name' => '100 ml bottle', 'price' => 2100], ['name' => '500 ml bottle', 'price' => 9800]]
        );

        $insert('Al-Rehman Agro Supplies', 'Pesticides',
            'Bayer Confidor 200 SL (Imidacloprid)',
            'Systemic insecticide for sucking pest control in cotton, chilli and vegetables. Long residual activity.',
            3200.00, 2950.00, true,
            [['name' => '250 ml bottle', 'price' => 3200], ['name' => '1 litre bottle', 'price' => 11500]]
        );

        $insert('Al-Rehman Agro Supplies', 'Pesticides',
            'BASF Maestro 75% WP (Captan)',
            'Protectant fungicide for apple scab, brown rot and downy mildew in orchard and vegetable crops.',
            1850.00, 1700.00, false,
            [['name' => '200 g pack', 'price' => 1850], ['name' => '1 kg pack', 'price' => 8500]]
        );

        // ────────────────────────────────────────────────────────
        //  INSECTICIDES — Kisaan Agri Mart (Bahawalpur)
        // ────────────────────────────────────────────────────────
        $insert('Kisaan Agri Mart', 'Insecticides',
            'Bayer Regent 80 WG (Fipronil)',
            'Granular insecticide for soil application against white grub, termites and stem borers in sugarcane and maize.',
            2700.00, 2500.00, true,
            [['name' => '100 g pack', 'price' => 2700], ['name' => '500 g pack', 'price' => 12500]]
        );

        $insert('Kisaan Agri Mart', 'Insecticides',
            'Syngenta Actara 25 WG (Thiamethoxam)',
            '2nd-generation neonicotinoid. Controls whitefly, aphids, jassids and thrips in cotton. Quick knock-down effect.',
            3600.00, 3400.00, true,
            [['name' => '100 g pack', 'price' => 3600], ['name' => '250 g pack', 'price' => 8700]]
        );

        $insert('Kisaan Agri Mart', 'Insecticides',
            'Chlorpyrifos 40% EC',
            'Contact and stomach-acting organophosphate insecticide for cutworms, grubs and termites. Registered by DRAP.',
            900.00, 800.00, false,
            [['name' => '500 ml bottle', 'price' => 900], ['name' => '1 litre bottle', 'price' => 1700]]
        );

        $insert('Kisaan Agri Mart', 'Insecticides',
            'Deltamethrin 2.8% EC (Decis)',
            'Pyrethroid insecticide with fast knockdown. For bollworm, aphids and thrips in cotton and vegetables.',
            1200.00, null, false,
            [['name' => '250 ml bottle', 'price' => 1200], ['name' => '1 litre bottle', 'price' => 4500]]
        );

        // ────────────────────────────────────────────────────────
        //  SEEDS — Green Land Seeds (Gujranwala)
        // ────────────────────────────────────────────────────────
        $insert('Green Land Seeds', 'Seeds',
            'IRRI-6 Paddy Rice Seed',
            'Long-grain non-basmati variety. High yield, tolerant to blast disease. Suitable for Gujranwala, Hafizabad and Sialkot paddies.',
            1800.00, 1650.00, true,
            [['name' => '10 kg bag', 'price' => 1800], ['name' => '25 kg bag', 'price' => 4300]]
        );

        $insert('Green Land Seeds', 'Seeds',
            'Hybrid Canola Seed (Bulbul-98)',
            'High oil-content canola variety for rabi season. Yields 15–18 mounds/acre. Suited for Punjab and KPK.',
            2400.00, 2200.00, false,
            [['name' => '2 kg tin', 'price' => 2400]]
        );

        $insert('Green Land Seeds', 'Seeds',
            'Garden Pea Seed (Local Improved)',
            'Improved local pea variety for kitchen garden and commercial cultivation. Cold tolerant. Ready in 70 days.',
            650.00, null, false,
            [['name' => '500 g pack', 'price' => 650], ['name' => '2 kg pack', 'price' => 2400]]
        );

        // ────────────────────────────────────────────────────────
        //  FERTILIZERS — Ittehad Fertilizers (Sahiwal)
        // ────────────────────────────────────────────────────────
        $insert('Ittehad Fertilizers', 'Fertilizers',
            'Borax (Sodium Tetraborate 11% B)',
            'Boron micronutrient for improved fruit set in mango, citrus and cotton. Reduces hollow heart in potato.',
            1400.00, 1250.00, false,
            [['name' => '2 kg bag', 'price' => 1400], ['name' => '10 kg bag', 'price' => 6500]]
        );

        $insert('Ittehad Fertilizers', 'Fertilizers',
            'Iron Sulphate (FeSO4 – 19% Fe)',
            'Iron source for chlorosis correction in calcareous soils of Pakistan. Granular, easy to broadcast.',
            1100.00, null, false,
            [['name' => '5 kg bag', 'price' => 1100], ['name' => '25 kg bag', 'price' => 5000]]
        );

        // ────────────────────────────────────────────────────────
        //  PESTICIDES — KPK Agro Solutions (Peshawar)
        // ────────────────────────────────────────────────────────
        $insert('KPK Agro Solutions', 'Herbicides',
            'Stomp 330 EC (Pendimethalin)',
            'Pre-emergence herbicide for control of annual grasses and broadleaf weeds in maize, onion and tobacco.',
            2300.00, 2100.00, true,
            [['name' => '500 ml bottle', 'price' => 2300], ['name' => '1 litre bottle', 'price' => 4400]]
        );

        $insert('KPK Agro Solutions', 'Pesticides',
            'Ridomil Gold MZ 68% WG (Mancozeb + Metalaxyl)',
            'Systemic + contact fungicide, highly effective against late blight of potato and tomato in KPK highlands.',
            3800.00, 3500.00, true,
            [['name' => '100 g pack', 'price' => 3800], ['name' => '500 g pack', 'price' => 17500]]
        );

        // ────────────────────────────────────────────────────────
        //  BIO PESTICIDES — Biocare Agri Pakistan (Karachi)
        // ────────────────────────────────────────────────────────
        $insert('Biocare Agri Pakistan', 'Bio Pesticides',
            'BioLogic Bt (Bacillus thuringiensis) WP',
            'Biological insecticide – harmless to humans and beneficial insects. Controls caterpillars and leaf miners. Halal certified.',
            2600.00, 2350.00, true,
            [['name' => '250 g pack', 'price' => 2600], ['name' => '1 kg pack', 'price' => 9500]]
        );

        $insert('Biocare Agri Pakistan', 'Bio Pesticides',
            'Neem Azadirachtin 0.03% EC (NeemGuard)',
            'Cold-pressed neem extract. Repels and discourages feeding by mites, thrips, whitefly and aphids. Safe for organic farming.',
            1900.00, 1700.00, false,
            [['name' => '500 ml bottle', 'price' => 1900], ['name' => '1 litre bottle', 'price' => 3600]]
        );

        $insert('Biocare Agri Pakistan', 'Bio Pesticides',
            'Trichoderma Viride Bio-Fungicide',
            'Soil application fungicide – controls damping off, Fusarium root rot and Pythium. Compatible with organic farming program.',
            2200.00, null, false,
            [['name' => '500 g pack', 'price' => 2200]]
        );

        // ────────────────────────────────────────────────────────
        //  HERBICIDES — Sindh Crop Care (Sukkur)
        // ────────────────────────────────────────────────────────
        $insert('Sindh Crop Care', 'Herbicides',
            'Gramoxone 20% SL (Paraquat)',
            'Non-selective contact herbicide. Widely used for weed burndown in rice transplant fields and orchard strips.',
            1600.00, 1450.00, false,
            [['name' => '500 ml bottle', 'price' => 1600], ['name' => '1 litre bottle', 'price' => 3000]]
        );

        $insert('Sindh Crop Care', 'Herbicides',
            'Puma Super 7.5% EW (Fenoxaprop-P-Ethyl)',
            'Post-emergence herbicide for control of wild oat and weedy rices in wheat. Safe for broad-leaf plants.',
            4200.00, 3900.00, true,
            [['name' => '500 ml bottle', 'price' => 4200], ['name' => '1 litre bottle', 'price' => 8000]]
        );

        $insert('Sindh Crop Care', 'Herbicides',
            'Roundup 41% SL (Glyphosate)',
            'Systemic broad-spectrum herbicide. Used for land clearing and orchard weed management across Sindh.',
            2800.00, 2600.00, false,
            [['name' => '500 ml bottle', 'price' => 2800], ['name' => '1 litre bottle', 'price' => 5400]]
        );

        // ────────────────────────────────────────────────────────
        //  FUNGICIDES — Balochistan Crop Protect (Quetta)
        // ────────────────────────────────────────────────────────
        $insert('Balochistan Crop Protect', 'Fungicides',
            'Score 250 EC (Difenoconazole)',
            'Systemic triazole fungicide for apple scab, cherry leaf spot and peach shot-hole disease in Balochistan orchards.',
            4800.00, 4500.00, true,
            [['name' => '100 ml bottle', 'price' => 4800], ['name' => '500 ml bottle', 'price' => 22000]]
        );

        $insert('Balochistan Crop Protect', 'Fungicides',
            'Mancozeb 80% WP (Dithane M-45)',
            'Broad-spectrum protectant fungicide for early & late blight, downy mildew in potato, tomato & grapes.',
            850.00, 780.00, false,
            [['name' => '200 g pack', 'price' => 850], ['name' => '1 kg pack', 'price' => 3900]]
        );

        $insert('Balochistan Crop Protect', 'Fungicides',
            'Topsin M 70% WP (Thiophanate-Methyl)',
            'Systemic benzimidazole fungicide against grey mould (Botrytis), powdery mildew and white rot in apple and plum.',
            2900.00, 2700.00, false,
            [['name' => '200 g pack', 'price' => 2900], ['name' => '1 kg pack', 'price' => 13000]]
        );

        $this->command->info('ProductsSeeder: ' . DB::table('products')->count() . ' products, ' . DB::table('product_attributes')->count() . ' attributes inserted.');
    }
}
