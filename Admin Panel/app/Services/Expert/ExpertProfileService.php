<?php

namespace App\Services\Expert;

use App\Models\Expert;
use App\Models\ExpertProfile;
use App\Models\ExpertSpecialization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ExpertProfileService
 *
 * Handles all profile management logic for expert/agency users.
 * Keeps controllers thin by encapsulating mutation + validation logic.
 */
class ExpertProfileService
{
    /**
     * Update the expert's core profile fields.
     */
    public function updateProfile(Expert $expert, array $data): Expert
    {
        DB::transaction(function () use ($expert, $data) {
            // Update base user fields
            $expert->user->update(array_filter([
                'name'  => $data['name']  ?? null,
                'phone' => $data['phone'] ?? null,
            ]));

            // Update expert base record
            $expert->update(array_filter([
                'specialty'    => $data['specialty']    ?? null,
                'bio'          => $data['bio']           ?? null,
                'is_available' => $data['is_available']  ?? null,
                'hourly_rate'  => $data['hourly_rate']   ?? null,
            ]));

            // Upsert extended profile
            $expert->profile()->updateOrCreate(
                ['expert_id' => $expert->id],
                array_filter([
                    'agency_name'           => $data['agency_name']           ?? null,
                    'specialization'        => $data['specialization']        ?? null,
                    'experience_years'      => $data['experience_years']      ?? null,
                    'certifications'        => $data['certifications']        ?? null,
                    'availability_schedule' => $data['availability_schedule'] ?? null,
                    'website'               => $data['website']               ?? null,
                    'linkedin'              => $data['linkedin']              ?? null,
                    'contact_phone'         => $data['contact_phone']         ?? null,
                    'city'                  => $data['city']                  ?? null,
                    'address'               => $data['address']               ?? null,
                    'map_link'              => $data['map_link']              ?? null,
                    'country'               => $data['country']               ?? null,
                    'account_type'          => $data['account_type']          ?? null,
                ])
            );
        });

        return $expert->fresh(['user', 'profile']);
    }

    /**
     * Upload and store the expert avatar; removes the old one.
     */
    public function uploadAvatar(Expert $expert, UploadedFile $file): string
    {
        if ($expert->profile_image && Storage::disk('public')->exists($expert->profile_image)) {
            Storage::disk('public')->delete($expert->profile_image);
        }

        $path = $file->store('experts/avatars', 'public');
        $expert->update(['profile_image' => $path]);

        return $path;
    }

    /**
     * Sync specializations for an expert.
     */
    public function syncSpecializations(Expert $expert, array $specializations): void
    {
        // $specializations = [['name' => 'Soil Science', 'level' => 'expert'], …]
        $expert->specializations()->delete();

        $records = collect($specializations)->map(fn ($s) => [
            'expert_id'  => $expert->id,
            'name'       => $s['name'],
            'level'      => $s['level'] ?? 'intermediate',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        ExpertSpecialization::insert($records);
    }

    /**
     * Retrieve full profile DTO for dashboard display.
     */
    public function getProfileData(Expert $expert): array
    {
        $expert->load(['user', 'profile', 'specializations']);

        return [
            'expert'           => $expert,
            'profile'          => $expert->profile,
            'specializations'  => $expert->specializations,
            'unread_count'     => $expert->notificationLogs()->unread()->count(),
        ];
    }
}
