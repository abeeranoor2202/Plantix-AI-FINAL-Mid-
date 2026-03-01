<?php

namespace App\Services\Expert;

use App\Models\Expert;
use App\Models\ExpertApplication;
use App\Models\ExpertLog;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ExpertApplicationService
 *
 * Manages the complete application lifecycle:
 *   1. Customer submits application (with optional file uploads)
 *   2. Admin moves it to under_review
 *   3. Admin approves → Expert record created, user.role updated
 *   4. Admin rejects → reason stored, applicant notified
 *
 * Security:
 *   - A user can only have ONE non-rejected active application at a time
 *   - Already-expert users cannot re-apply
 *   - File uploads: PDF / JPEG / PNG only, max 5MB each
 */
class ExpertApplicationService
{
    // ── Submission ────────────────────────────────────────────────────────────

    /**
     * Store a new application submitted by a customer.
     *
     * @param  User  $user
     * @param  array $data  Validated form data
     * @return ExpertApplication
     * @throws \RuntimeException if user already has an active application or is an expert
     */
    public function submit(User $user, array $data): ExpertApplication
    {
        // Guard: user must not already be an expert
        if ($user->role === 'expert') {
            throw new \RuntimeException('User is already an expert.');
        }

        // Guard: no pending / under_review application already exists
        $existing = ExpertApplication::where('user_id', $user->id)
            ->whereIn('status', [
                ExpertApplication::STATUS_PENDING,
                ExpertApplication::STATUS_UNDER_REVIEW,
            ])
            ->exists();

        if ($existing) {
            throw new \RuntimeException('An active application is already under review.');
        }

        return DB::transaction(function () use ($user, $data) {
            $application = ExpertApplication::create([
                'user_id'          => $user->id,
                'full_name'        => $data['full_name'],
                'specialization'   => $data['specialization'],
                'experience_years' => $data['experience_years'] ?? 0,
                'qualifications'   => $data['qualifications'] ?? null,
                'bio'              => $data['bio'] ?? null,
                'contact_phone'    => $data['contact_phone'] ?? null,
                'city'             => $data['city'] ?? null,
                'country'          => $data['country'] ?? null,
                'website'          => $data['website'] ?? null,
                'linkedin'         => $data['linkedin'] ?? null,
                'account_type'     => $data['account_type'] ?? 'individual',
                'agency_name'      => $data['agency_name'] ?? null,
                'status'           => ExpertApplication::STATUS_PENDING,
            ]);

            // Handle file uploads
            if (isset($data['certifications_file'])) {
                $application->certifications_path = $this->storeDocumentFile(
                    $data['certifications_file'],
                    "applications/{$application->id}/certifications"
                );
            }

            if (isset($data['id_document_file'])) {
                $application->id_document_path = $this->storeDocumentFile(
                    $data['id_document_file'],
                    "applications/{$application->id}/id_document"
                );
            }

            $application->save();

            return $application;
        });
    }

    // ── Admin actions ─────────────────────────────────────────────────────────

    /**
     * Admin: move application to under_review.
     */
    public function markUnderReview(ExpertApplication $application, User $admin): ExpertApplication
    {
        $application->update([
            'status'      => ExpertApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => $admin->id,
        ]);

        return $application->fresh();
    }

    /**
     * Admin: approve application → create Expert + ExpertProfile, update user role.
     */
    public function approve(ExpertApplication $application, User $admin, ?string $notes = null): Expert
    {
        return DB::transaction(function () use ($application, $admin, $notes) {
            $user = $application->user;

            // Create Expert record (status = approved)
            $expert = Expert::create([
                'user_id'   => $user->id,
                'status'    => Expert::STATUS_APPROVED,
                'specialty' => $application->specialization,
                'bio'       => $application->bio,
                'verified_at' => now(),
            ]);

            // Create extended profile
            ExpertProfile::create([
                'expert_id'        => $expert->id,
                'agency_name'      => $application->agency_name,
                'specialization'   => $application->specialization,
                'experience_years' => $application->experience_years,
                'contact_phone'    => $application->contact_phone,
                'city'             => $application->city,
                'country'          => $application->country,
                'website'          => $application->website,
                'linkedin'         => $application->linkedin,
                'account_type'     => $application->account_type,
                'approval_status'  => Expert::STATUS_APPROVED,
                'approved_at'      => now(),
                'admin_notes'      => $notes,
            ]);

            // Upgrade user role
            $user->update(['role' => 'expert']);

            // Close the application
            $application->update([
                'status'      => ExpertApplication::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Initial audit log
            ExpertLog::create([
                'expert_id'   => $expert->id,
                'actor_id'    => $admin->id,
                'action'      => ExpertLog::ACTION_CREATED,
                'from_status' => null,
                'to_status'   => Expert::STATUS_APPROVED,
                'notes'       => 'Expert created via approved application #' . $application->id,
                'metadata'    => ['application_id' => $application->id],
            ]);

            return $expert;
        });
    }

    /**
     * Admin: reject application.
     */
    public function reject(
        ExpertApplication $application,
        User              $admin,
        string            $reason
    ): ExpertApplication {
        $application->update([
            'status'      => ExpertApplication::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $reason,
        ]);

        return $application->fresh();
    }

    // ── Query helpers ─────────────────────────────────────────────────────────

    /**
     * Get latest application for a user.
     */
    public function getLatestApplicationForUser(int $userId): ?ExpertApplication
    {
        return ExpertApplication::where('user_id', $userId)
            ->latest()
            ->first();
    }

    /**
     * Check whether a user has an active (non-completed) application.
     */
    public function hasActiveApplication(int $userId): bool
    {
        return ExpertApplication::where('user_id', $userId)
            ->whereIn('status', [
                ExpertApplication::STATUS_PENDING,
                ExpertApplication::STATUS_UNDER_REVIEW,
            ])
            ->exists();
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function storeDocumentFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'private');
    }
}
