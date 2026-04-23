<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * VendorsSeeder
 *
 * Creates 6 vendor profiles matching the 6 vendor users in UsersSeeder.
 *
 * Migration 2026_04_12_010000 added these columns to vendors:
 *   owner_name, business_email, business_phone, tax_id, business_category,
 *   city, region, bank_name, bank_account_name, bank_account_number, iban,
 *   cnic_document, business_license_document, tax_certificate_document,
 *   status, reviewed_by, submitted_at, reviewed_at, approved_at, rejected_at,
 *   suspended_at.
 *
 * All new nullable columns default to NULL unless explicitly set.
 * status defaults to 'approved' for seeded vendors so they are immediately usable.
 */
class VendorsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $vendorUserIds  = DB::table('users')->where('role', 'vendor')->pluck('id')->toArray();
        $catSeedsId     = DB::table('categories')->where('name', 'Seeds & Planting')->value('id');
        $catFertId      = DB::table('categories')->where('name', 'Fertilizers & Soil Nutrients')->value('id');
        $catPestId      = DB::table('categories')->where('name', 'Pesticides & Herbicides')->value('id');
        $catToolsId     = DB::table('categories')->where('name', 'Farming Tools & Equipment')->value('id');
        $catOrganicId   = DB::table('categories')->where('name', 'Organic & Natural Products')->value('id');

        $adminId = DB::table('users')->where('email', 'admin@gmail.com')->value('id');

        $vendors = [
            [
                'author_id'         => $vendorUserIds[0] ?? 1,
                'category_id'       => $catSeedsId,
                'title'             => 'GreenHarvest Seeds Co.',
                'description'       => 'Pakistan\'s largest certified seed supplier with over 20 years of field experience. We offer hybrid and open-pollinated varieties for all major crops.',
                'address'           => 'Hall Road, Lahore, Punjab',
                'city'              => 'Lahore',
                'region'            => 'Punjab',
                'latitude'          => 31.5204,
                'longitude'         => 74.3587,
                'phone'             => '+923021200001',
                'business_phone'    => '+923021200001',
                'business_email'    => 'khalid@greenharvest.pk',
                'business_category' => 'Seeds & Planting',
                'rating'            => 4.60,
                'review_count'      => 142,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '08:00:00',
                'close_time'        => '20:00:00',
                'delivery_fee'      => 150.00,
                'min_order_amount'  => 500.00,
                'preparation_time'  => 1440,
                'commission_rate'   => 8.00,
                'approved_at'       => $now->copy()->subDays(200),
            ],
            [
                'author_id'         => $vendorUserIds[1] ?? 2,
                'category_id'       => $catFertId,
                'title'             => 'PakiAgroPro',
                'description'       => 'Trusted supplier of premium fertilizers, soil amendments, and plant nutrition products serving farmers across Sindh and Balochistan.',
                'address'           => 'Tariq Road, Karachi, Sindh',
                'city'              => 'Karachi',
                'region'            => 'Sindh',
                'latitude'          => 24.8607,
                'longitude'         => 67.0011,
                'phone'             => '+923021200002',
                'business_phone'    => '+923021200002',
                'business_email'    => 'asma@pakiagropro.pk',
                'business_category' => 'Fertilizers & Soil Nutrients',
                'rating'            => 4.35,
                'review_count'      => 98,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '09:00:00',
                'close_time'        => '19:00:00',
                'delivery_fee'      => 200.00,
                'min_order_amount'  => 1000.00,
                'preparation_time'  => 1440,
                'commission_rate'   => 7.50,
                'approved_at'       => $now->copy()->subDays(180),
            ],
            [
                'author_id'         => $vendorUserIds[2] ?? 3,
                'category_id'       => $catToolsId,
                'title'             => 'FarmTech Solutions',
                'description'       => 'Manufacturer and retailer of high-quality farming tools, soil testing meters, and mechanised equipment for smallholder and commercial farms in Pakistan.',
                'address'           => 'G.T. Road, Faisalabad, Punjab',
                'city'              => 'Faisalabad',
                'region'            => 'Punjab',
                'latitude'          => 31.4154,
                'longitude'         => 73.0791,
                'phone'             => '+923021200003',
                'business_phone'    => '+923021200003',
                'business_email'    => 'tariq@farmtech.pk',
                'business_category' => 'Farming Tools & Equipment',
                'rating'            => 4.20,
                'review_count'      => 67,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '08:30:00',
                'close_time'        => '18:30:00',
                'delivery_fee'      => 250.00,
                'min_order_amount'  => 750.00,
                'preparation_time'  => 2880,
                'commission_rate'   => 9.00,
                'approved_at'       => $now->copy()->subDays(150),
            ],
            [
                'author_id'         => $vendorUserIds[3] ?? 4,
                'category_id'       => $catFertId,
                'title'             => 'KisanMart Supply',
                'description'       => 'One-stop agri supply shop for farmers in South Punjab. Fertilizers, seeds, and basic tools delivered to your doorstep.',
                'address'           => 'Hussain Agahi Road, Multan, Punjab',
                'city'              => 'Multan',
                'region'            => 'Punjab',
                'latitude'          => 30.1575,
                'longitude'         => 71.5249,
                'phone'             => '+923021200004',
                'business_phone'    => '+923021200004',
                'business_email'    => 'rukhsana@kisanmart.pk',
                'business_category' => 'Fertilizers & Soil Nutrients',
                'rating'            => 3.90,
                'review_count'      => 54,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '08:00:00',
                'close_time'        => '21:00:00',
                'delivery_fee'      => 120.00,
                'min_order_amount'  => 500.00,
                'preparation_time'  => 1440,
                'commission_rate'   => 8.50,
                'approved_at'       => $now->copy()->subDays(120),
            ],
            [
                'author_id'         => $vendorUserIds[4] ?? 5,
                'category_id'       => $catPestId,
                'title'             => 'AgroShield Pesticides',
                'description'       => 'Registered supplier of crop protection products including herbicides, insecticides, and fungicides. All products are government-certified.',
                'address'           => 'Peshawar Road, Peshawar, KPK',
                'city'              => 'Peshawar',
                'region'            => 'KPK',
                'latitude'          => 34.0151,
                'longitude'         => 71.5249,
                'phone'             => '+923021200005',
                'business_phone'    => '+923021200005',
                'business_email'    => 'imran@agrishield.pk',
                'business_category' => 'Pesticides & Herbicides',
                'rating'            => 4.50,
                'review_count'      => 88,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '09:00:00',
                'close_time'        => '18:00:00',
                'delivery_fee'      => 180.00,
                'min_order_amount'  => 800.00,
                'preparation_time'  => 1440,
                'commission_rate'   => 7.00,
                'approved_at'       => $now->copy()->subDays(90),
            ],
            [
                'author_id'         => $vendorUserIds[5] ?? 6,
                'category_id'       => $catOrganicId,
                'title'             => 'NatureCrop Organics',
                'description'       => 'Pakistan\'s first dedicated organic farming supply platform. We stock vermicompost, neem-based pesticides, biofertilisers, and mycorrhizal inoculants.',
                'address'           => 'Blue Area, Islamabad',
                'city'              => 'Islamabad',
                'region'            => 'Federal',
                'latitude'          => 33.7294,
                'longitude'         => 73.0931,
                'phone'             => '+923021200006',
                'business_phone'    => '+923021200006',
                'business_email'    => 'zainab@naturecrop.pk',
                'business_category' => 'Organic & Natural Products',
                'rating'            => 4.75,
                'review_count'      => 112,
                'is_active'         => 1,
                'is_approved'       => 1,
                'status'            => 'approved',
                'open_time'         => '10:00:00',
                'close_time'        => '20:00:00',
                'delivery_fee'      => 100.00,
                'min_order_amount'  => 600.00,
                'preparation_time'  => 1440,
                'commission_rate'   => 10.00,
                'approved_at'       => $now->copy()->subDays(60),
            ],
        ];

        foreach ($vendors as $v) {
            DB::table('vendors')->insert(array_merge($v, [
                'cover_photo'            => null,
                'image'                  => null,
                'stripe_account_id'      => null,
                'owner_name'             => null,
                'tax_id'                 => null,
                'bank_name'              => null,
                'bank_account_name'      => null,
                'bank_account_number'    => null,
                'iban'                   => null,
                'cnic_document'          => null,
                'business_license_document' => null,
                'tax_certificate_document'  => null,
                'reviewed_by'            => $adminId,
                'submitted_at'           => $v['approved_at'] ?? null,
                'reviewed_at'            => $v['approved_at'] ?? null,
                'rejected_at'            => null,
                'suspended_at'           => null,
                'created_at'             => $now->copy()->subDays(rand(30, 300)),
                'updated_at'             => $now,
            ]));
        }
    }
}
