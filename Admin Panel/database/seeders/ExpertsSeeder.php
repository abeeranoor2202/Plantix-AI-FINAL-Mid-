<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpertsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $expertUsers = DB::table('users')->where('role', 'expert')->orderBy('id')->get();

        $definitions = [
            [
                'email'   => 'ahmed.raza@plantix.com',
                'status'  => 'approved',
                'specialty' => 'Soil Science & Nutrition',
                'bio'     => 'Dr. Ahmed Raza is a PhD soil scientist with 15 years of field and laboratory expertise. He has developed fertiliser recommendation models for Punjab\'s major crops.',
                'hourly_rate'         => 2000,
                'consultation_price'  => 2500,
                'is_available'        => 1,
                'rating_avg'          => 4.80,
                'total_appointments'  => 120,
                'total_completed'     => 112,
                'total_cancelled'     => 8,
                'verified_at'         => $now->copy()->subMonths(6),
                'profile'             => [
                    'agency_name'       => null,
                    'specialization'    => 'Soil Fertility & Plant Nutrition',
                    'experience_years'  => 15,
                    'certifications'    => 'PhD Soil Science (UAF), FAO Certified Nutrient Management',
                    'city'              => 'Lahore',
                    'account_type'      => 'individual',
                    'approval_status'   => 'approved',
                    'approved_at'       => $now->copy()->subMonths(6),
                ],
                'specializations' => [
                    ['name' => 'Soil Fertility', 'level' => 'expert'],
                    ['name' => 'Crop Nutrition', 'level' => 'expert'],
                    ['name' => 'Fertilizer Management', 'level' => 'expert'],
                ],
            ],
            [
                'email'   => 'amina.malik@plantix.com',
                'status'  => 'approved',
                'specialty' => 'Crop Disease Management',
                'bio'     => 'Professor Amina Malik specialises in plant pathology and integrated pest management. She has published 30+ research papers on fungal and bacterial diseases in Pakistan.',
                'hourly_rate'         => 2200,
                'consultation_price'  => 2800,
                'is_available'        => 1,
                'rating_avg'          => 4.65,
                'total_appointments'  => 95,
                'total_completed'     => 90,
                'total_cancelled'     => 5,
                'verified_at'         => $now->copy()->subMonths(8),
                'profile'             => [
                    'agency_name'       => 'Agri Diagnostics Lab',
                    'specialization'    => 'Plant Disease & IPM',
                    'experience_years'  => 18,
                    'certifications'    => 'MSc Plant Pathology, PhD IPM (UAAR)',
                    'city'              => 'Rawalpindi',
                    'account_type'      => 'agency',
                    'approval_status'   => 'approved',
                    'approved_at'       => $now->copy()->subMonths(8),
                ],
                'specializations' => [
                    ['name' => 'Fungal Disease Control', 'level' => 'expert'],
                    ['name' => 'Integrated Pest Management', 'level' => 'expert'],
                    ['name' => 'Pesticide Evaluation', 'level' => 'intermediate'],
                ],
            ],
            [
                'email'   => 'usman.tariq@plantix.com',
                'status'  => 'approved',
                'specialty' => 'Irrigation Systems',
                'bio'     => 'Engr. Usman Tariq is an irrigation engineer with 12 years of project experience in drip, sprinkler, and surface irrigation systems across Pakistan.',
                'hourly_rate'         => 1800,
                'consultation_price'  => 2200,
                'is_available'        => 1,
                'rating_avg'          => 4.45,
                'total_appointments'  => 75,
                'total_completed'     => 70,
                'total_cancelled'     => 5,
                'verified_at'         => $now->copy()->subMonths(4),
                'profile'             => [
                    'agency_name'       => null,
                    'specialization'    => 'Water Management & Irrigation',
                    'experience_years'  => 12,
                    'certifications'    => 'BSc Agricultural Engineering (UAF), PEC Registered',
                    'city'              => 'Faisalabad',
                    'account_type'      => 'individual',
                    'approval_status'   => 'approved',
                    'approved_at'       => $now->copy()->subMonths(4),
                ],
                'specializations' => [
                    ['name' => 'Drip Irrigation Design', 'level' => 'expert'],
                    ['name' => 'Water Resource Management', 'level' => 'expert'],
                    ['name' => 'Sprinkler Systems', 'level' => 'intermediate'],
                ],
            ],
            [
                'email'   => 'fatima.nawaz@plantix.com',
                'status'  => 'approved',
                'specialty' => 'Organic Farming',
                'bio'     => 'Dr. Fatima Nawaz is an organic farming advocate with a focus on sustainable agriculture practices, biofertilisers, and soil biology.',
                'hourly_rate'         => 400,
                'consultation_price'  => 500,
                'is_available'        => 1,
                'rating_avg'          => 3.5,
                'total_appointments'  => 20,
                'total_completed'     => 15,
                'total_cancelled'     => 5,
                'verified_at'         => $now->copy()->subMonths(2),
                'profile'             => [
                    'agency_name'       => null,
                    'specialization'    => 'Organic Certification & Soil Health',
                    'experience_years'  => 7,
                    'certifications'    => 'MSc Organic Agriculture (SAU), USDA Organic Certified Advisor',
                    'city'              => 'Islamabad',
                    'account_type'      => 'individual',
                    'approval_status'   => 'approved',
                    'approved_at'       => $now->copy()->subMonths(2),
                ],
                'specializations' => [
                    ['name' => 'Organic Crop Production', 'level' => 'expert'],
                    ['name' => 'Biofertilizers', 'level' => 'intermediate'],
                ],
            ],
            [
                'email'   => 'hassan.iqbal@plantix.com',
                'status'  => 'approved',
                'specialty' => 'Pest & Weed Management',
                'bio'     => 'Dr. Hassan Iqbal is a highly experienced pest management specialist. He has 25 years of experience in weed control and herbicides.',
                'hourly_rate'         => 4500,
                'consultation_price'  => 5000,
                'is_available'        => 1,
                'rating_avg'          => 4.9,
                'total_appointments'  => 250,
                'total_completed'     => 245,
                'total_cancelled'     => 5,
                'verified_at'         => $now->copy()->subMonths(12),
                'profile'             => [
                    'agency_name'       => null,
                    'specialization'    => 'Weed Science & Herbicide Use',
                    'experience_years'  => 25,
                    'certifications'    => 'PhD Agronomy',
                    'city'              => 'Multan',
                    'account_type'      => 'individual',
                    'approval_status'   => 'approved',
                    'approved_at'       => $now->copy()->subMonths(12),
                ],
                'specializations' => [
                    ['name' => 'Weed Control', 'level' => 'expert'],
                ],
            ],
        ];

        foreach ($definitions as $def) {
            // Find user
            $user = DB::table('users')->where('email', $def['email'])->first();
            if (! $user) {
                continue;
            }

            // Upsert expert — update if already exists so status is always correct
            $existing = DB::table('experts')->where('user_id', $user->id)->first();

            if ($existing) {
                DB::table('experts')->where('user_id', $user->id)->update([
                    'status'                       => $def['status'],
                    'approval_status'              => $def['status'],
                    'specialty'                    => $def['specialty'],
                    'bio'                          => $def['bio'],
                    'is_available'                 => $def['is_available'],
                    'hourly_rate'                  => $def['hourly_rate'],
                    'consultation_price'           => $def['consultation_price'],
                    'consultation_fee'             => $def['consultation_price'],
                    'consultation_duration_minutes'=> 60,
                    'rating_avg'                   => $def['rating_avg'],
                    'total_appointments'           => $def['total_appointments'],
                    'total_completed'              => $def['total_completed'],
                    'total_cancelled'              => $def['total_cancelled'],
                    'verified_at'                  => $def['verified_at'],
                    'suspended_at'                 => null,
                    'rejection_reason'             => null,
                    'updated_at'                   => $now,
                ]);
                $expertId = $existing->id;
            } else {
                $expertId = DB::table('experts')->insertGetId([
                    'user_id'                      => $user->id,
                    'status'                       => $def['status'],
                    'approval_status'              => $def['status'],
                    'specialty'                    => $def['specialty'],
                    'bio'                          => $def['bio'],
                    'profile_image'                => null,
                    'is_available'                 => $def['is_available'],
                    'hourly_rate'                  => $def['hourly_rate'],
                    'consultation_price'           => $def['consultation_price'],
                    'consultation_fee'             => $def['consultation_price'],
                    'consultation_duration_minutes'=> 60,
                    'rating_avg'                   => $def['rating_avg'],
                    'total_appointments'           => $def['total_appointments'],
                    'total_completed'              => $def['total_completed'],
                    'total_cancelled'              => $def['total_cancelled'],
                    'verified_at'                  => $def['verified_at'],
                    'suspended_at'                 => null,
                    'rejection_reason'             => null,
                    'created_at'                   => $now->copy()->subMonths(rand(3, 12)),
                    'updated_at'                   => $now,
                ]);
            }

            // Upsert expert profile
            $profileExists = DB::table('expert_profiles')->where('expert_id', $expertId)->exists();
            if ($profileExists) {
                DB::table('expert_profiles')->where('expert_id', $expertId)->update(array_merge($def['profile'], [
                    'website'       => null,
                    'linkedin'      => null,
                    'contact_phone' => $user->phone ?? null,
                    'country'       => 'Pakistan',
                    'admin_notes'   => null,
                    'updated_at'    => $now,
                ]));
            } else {
                DB::table('expert_profiles')->insert(array_merge($def['profile'], [
                    'expert_id'     => $expertId,
                    'website'       => null,
                    'linkedin'      => null,
                    'contact_phone' => $user->phone ?? null,
                    'country'       => 'Pakistan',
                    'admin_notes'   => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]));
            }

            // Upsert specialisations
            DB::table('expert_specializations')->where('expert_id', $expertId)->delete();
            foreach ($def['specializations'] as $spec) {
                DB::table('expert_specializations')->insert([
                    'expert_id'  => $expertId,
                    'name'       => $spec['name'],
                    'level'      => $spec['level'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
