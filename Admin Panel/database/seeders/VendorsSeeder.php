<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendorsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Vendor user IDs are: 1-6 (inserted first by UsersSeeder)
        // Category IDs: 1=Seeds, 2=Fertilizers, 3=Pesticides, 4=Tools, 5=Irrigation, 6=Greenhouse, 7=Animal Feed, 8=Organic
        $vendorUserIds  = DB::table('users')->where('role', 'vendor')->pluck('id')->toArray();
        $catSeedsId     = DB::table('categories')->where('name', 'Seeds & Planting')->value('id');
        $catFertId      = DB::table('categories')->where('name', 'Fertilizers & Soil Nutrients')->value('id');
        $catPestId      = DB::table('categories')->where('name', 'Pesticides & Herbicides')->value('id');
        $catToolsId     = DB::table('categories')->where('name', 'Farming Tools & Equipment')->value('id');
        $catOrganicId   = DB::table('categories')->where('name', 'Organic & Natural Products')->value('id');
        $catFeedId      = DB::table('categories')->where('name', 'Animal Feed & Livestock')->value('id');

        // zone_id is nullable on vendors; the `zones` table was removed in migration 2026_02_28_200001.

        $vendors = [
            [
                'author_id'      => $vendorUserIds[0] ?? 1,
                'category_id'    => $catSeedsId,
                'title'          => 'GreenHarvest Seeds Co.',
                'description'    => 'Pakistan\'s largest certified seed supplier with over 20 years of field experience. We offer hybrid and open-pollinated varieties for all major crops.',
                'address'        => 'Hall Road, Lahore, Punjab',
                'latitude'       => 31.5204,
                'longitude'      => 74.3587,
                'phone'          => '+923021200001',
                'rating'         => 4.60,
                'review_count'   => 142,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '08:00:00',
                'close_time'     => '20:00:00',
                'delivery_fee'   => 150.00,
                'min_order_amount'=> 500.00,
                'preparation_time'=> 1440,
                'commission_rate' => 8.00,
            ],
            [
                'author_id'      => $vendorUserIds[1] ?? 2,
                'category_id'    => $catFertId,
                'title'          => 'PakiAgroPro',
                'description'    => 'Trusted supplier of premium fertilizers, soil amendments, and plant nutrition products serving farmers across Sindh and Balochistan.',
                'address'        => 'Tariq Road, Karachi, Sindh',
                'latitude'       => 24.8607,
                'longitude'      => 67.0011,
                'phone'          => '+923021200002',
                'rating'         => 4.35,
                'review_count'   => 98,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '09:00:00',
                'close_time'     => '19:00:00',
                'delivery_fee'   => 200.00,
                'min_order_amount'=> 1000.00,
                'preparation_time'=> 1440,
                'commission_rate' => 7.50,
            ],
            [
                'author_id'      => $vendorUserIds[2] ?? 3,
                'category_id'    => $catToolsId,
                'title'          => 'FarmTech Solutions',
                'description'    => 'Manufacturer and retailer of high-quality farming tools, soil testing meters, and mechanised equipment for smallholder and commercial farms in Pakistan.',
                'address'        => 'G.T. Road, Faisalabad, Punjab',
                'latitude'       => 31.4154,
                'longitude'      => 73.0791,
                'phone'          => '+923021200003',
                'rating'         => 4.20,
                'review_count'   => 67,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '08:30:00',
                'close_time'     => '18:30:00',
                'delivery_fee'   => 250.00,
                'min_order_amount'=> 750.00,
                'preparation_time'=> 2880,
                'commission_rate' => 9.00,
            ],
            [
                'author_id'      => $vendorUserIds[3] ?? 4,
                'category_id'    => $catFertId,
                'title'          => 'KisanMart Supply',
                'description'    => 'One-stop agri supply shop for farmers in South Punjab. Fertilizers, seeds, and basic tools delivered to your doorstep.',
                'address'        => 'Hussain Agahi Road, Multan, Punjab',
                'latitude'       => 30.1575,
                'longitude'      => 71.5249,
                'phone'          => '+923021200004',
                'rating'         => 3.90,
                'review_count'   => 54,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '08:00:00',
                'close_time'     => '21:00:00',
                'delivery_fee'   => 120.00,
                'min_order_amount'=> 500.00,
                'preparation_time'=> 1440,
                'commission_rate' => 8.50,
            ],
            [
                'author_id'      => $vendorUserIds[4] ?? 5,
                'category_id'    => $catPestId,
                'title'          => 'AgroShield Pesticides',
                'description'    => 'Registered supplier of crop protection products including herbicides, insecticides, and fungicides. All products are government-certified.',
                'address'        => 'Peshawar Road, Peshawar, KPK',
                'latitude'       => 34.0151,
                'longitude'      => 71.5249,
                'phone'          => '+923021200005',
                'rating'         => 4.50,
                'review_count'   => 88,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '09:00:00',
                'close_time'     => '18:00:00',
                'delivery_fee'   => 180.00,
                'min_order_amount'=> 800.00,
                'preparation_time'=> 1440,
                'commission_rate' => 7.00,
            ],
            [
                'author_id'      => $vendorUserIds[5] ?? 6,
                'category_id'    => $catOrganicId,
                'title'          => 'NatureCrop Organics',
                'description'    => 'Pakistan\'s first dedicated organic farming supply platform. We stock vermicompost, neem-based pesticides, biofertilisers, and mycorrhizal inoculants.',
                'address'        => 'Blue Area, Islamabad',
                'latitude'       => 33.7294,
                'longitude'      => 73.0931,
                'phone'          => '+923021200006',
                'rating'         => 4.75,
                'review_count'   => 112,
                'is_active'      => 1,
                'is_approved'    => 1,
                'open_time'      => '10:00:00',
                'close_time'     => '20:00:00',
                'delivery_fee'   => 100.00,
                'min_order_amount'=> 600.00,
                'preparation_time'=> 1440,
                'commission_rate' => 10.00,
            ],
        ];

        foreach ($vendors as $v) {
            DB::table('vendors')->insert(array_merge($v, [
                'cover_photo'    => null,
                'image'          => null,
                'stripe_account_id' => null,
                'created_at'     => $now->copy()->subDays(rand(30, 300)),
                'updated_at'     => $now,
            ]));
        }
    }
}

