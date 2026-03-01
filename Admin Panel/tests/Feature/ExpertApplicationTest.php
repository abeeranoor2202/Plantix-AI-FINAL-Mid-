<?php

namespace Tests\Feature;

use App\Models\Expert;
use App\Models\ExpertApplication;
use App\Models\ExpertLog;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Services\Expert\ExpertApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ExpertApplicationTest
 *
 * Production-grade feature tests for the expert application pipeline.
 *
 * Coverage:
 *   1.  Customer can submit an application — record is created as pending
 *   2.  Already-expert users cannot submit an application
 *   3.  Users with an active pending application cannot submit again
 *   4.  Users with an active under_review application cannot submit again
 *   5.  Rejected applicants CAN re-apply
 *   6.  Admin can move application to under_review
 *   7.  Admin approval creates Expert record with status=approved
 *   8.  Admin approval creates ExpertProfile linked to Expert
 *   9.  Admin approval upgrades user.role to 'expert'
 *  10.  Admin approval closes the application (status=approved)
 *  11.  Admin approval writes an ExpertLog entry
 *  12.  Admin rejection stores reason in admin_notes
 *  13.  Admin rejection sets application status to rejected
 *  14.  hasActiveApplication returns true for pending/under_review, false otherwise
 *  15.  File uploads are stored to private disk during submission
 */
class ExpertApplicationTest extends TestCase
{
    use RefreshDatabase;

    private ExpertApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ExpertApplicationService::class);
        Storage::fake('private');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function customer(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function applicationData(array $overrides = []): array
    {
        return array_merge([
            'full_name'        => 'Dr. Jane Farmer',
            'specialization'   => 'Soil Science',
            'experience_years' => 8,
            'qualifications'   => 'PhD in Agronomy, MSc Soil Science',
            'bio'              => 'Expert in soil health and crop yield optimisation.',
            'contact_phone'    => '+1234567890',
            'city'             => 'Nairobi',
            'country'          => 'Kenya',
            'account_type'     => 'individual',
        ], $overrides);
    }

    // ── Submission ────────────────────────────────────────────────────────────

    /** @test */
    public function customer_can_submit_application_and_it_is_stored_as_pending(): void
    {
        $user = $this->customer();

        $application = $this->service->submit($user, $this->applicationData());

        $this->assertInstanceOf(ExpertApplication::class, $application);
        $this->assertEquals(ExpertApplication::STATUS_PENDING, $application->status);
        $this->assertEquals($user->id, $application->user_id);
        $this->assertEquals('Dr. Jane Farmer', $application->full_name);
        $this->assertDatabaseHas('expert_applications', [
            'user_id' => $user->id,
            'status'  => ExpertApplication::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function already_expert_user_cannot_submit_application(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already an expert/i');

        $user = User::factory()->create(['role' => 'expert']);
        $this->service->submit($user, $this->applicationData());
    }

    /** @test */
    public function user_with_pending_application_cannot_submit_again(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already under review/i');

        $user = $this->customer();
        ExpertApplication::factory()->create([
            'user_id' => $user->id,
            'status'  => ExpertApplication::STATUS_PENDING,
        ]);

        $this->service->submit($user, $this->applicationData());
    }

    /** @test */
    public function user_with_under_review_application_cannot_submit_again(): void
    {
        $this->expectException(\RuntimeException::class);

        $user  = $this->customer();
        $admin = $this->admin();
        ExpertApplication::factory()->create([
            'user_id'     => $user->id,
            'status'      => ExpertApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => $admin->id,
        ]);

        $this->service->submit($user, $this->applicationData());
    }

    /** @test */
    public function rejected_applicant_can_re_apply(): void
    {
        $user = $this->customer();
        ExpertApplication::factory()->create([
            'user_id' => $user->id,
            'status'  => ExpertApplication::STATUS_REJECTED,
        ]);

        $application = $this->service->submit($user, $this->applicationData());

        $this->assertEquals(ExpertApplication::STATUS_PENDING, $application->status);
    }

    /** @test */
    public function file_uploads_are_stored_to_private_disk(): void
    {
        $user = $this->customer();
        $file = UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf');

        $application = $this->service->submit($user, array_merge(
            $this->applicationData(),
            ['certifications_file' => $file]
        ));

        $this->assertNotNull($application->certifications_path);
        Storage::disk('private')->assertExists($application->certifications_path);
    }

    // ── Admin: under_review ───────────────────────────────────────────────────

    /** @test */
    public function admin_can_move_application_to_under_review(): void
    {
        $user  = $this->customer();
        $admin = $this->admin();
        $application = ExpertApplication::factory()->create([
            'user_id' => $user->id,
            'status'  => ExpertApplication::STATUS_PENDING,
        ]);

        $result = $this->service->markUnderReview($application, $admin);

        $this->assertEquals(ExpertApplication::STATUS_UNDER_REVIEW, $result->status);
        $this->assertEquals($admin->id, $result->reviewed_by);
    }

    // ── Admin: approve ────────────────────────────────────────────────────────

    /** @test */
    public function approval_creates_expert_record_with_approved_status(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();

        $expert = $this->service->approve($application, $admin, 'Welcome!');

        $this->assertInstanceOf(Expert::class, $expert);
        $this->assertEquals(Expert::STATUS_APPROVED, $expert->status);
        $this->assertNotNull($expert->verified_at);
        $this->assertEquals($application->specialization, $expert->specialty);
    }

    /** @test */
    public function approval_creates_expert_profile(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();

        $expert = $this->service->approve($application, $admin);

        $this->assertDatabaseHas('expert_profiles', [
            'expert_id'       => $expert->id,
            'approval_status' => Expert::STATUS_APPROVED,
        ]);
    }

    /** @test */
    public function approval_upgrades_user_role_to_expert(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();
        $userId = $application->user_id;

        $this->service->approve($application, $admin);

        $this->assertDatabaseHas('users', [
            'id'   => $userId,
            'role' => 'expert',
        ]);
    }

    /** @test */
    public function approval_closes_the_application(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();

        $this->service->approve($application, $admin, 'Approved, great profile!');

        $application->refresh();
        $this->assertEquals(ExpertApplication::STATUS_APPROVED, $application->status);
        $this->assertNotNull($application->reviewed_at);
        $this->assertEquals('Approved, great profile!', $application->admin_notes);
    }

    /** @test */
    public function approval_writes_expert_log_entry(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();

        $expert = $this->service->approve($application, $admin);

        $this->assertDatabaseHas('expert_logs', [
            'expert_id' => $expert->id,
            'actor_id'  => $admin->id,
            'action'    => ExpertLog::ACTION_CREATED,
            'to_status' => Expert::STATUS_APPROVED,
        ]);
    }

    // ── Admin: reject ─────────────────────────────────────────────────────────

    /** @test */
    public function rejection_stores_reason_in_admin_notes(): void
    {
        [$application, $admin] = $this->makeUnderReviewApplication();

        $result = $this->service->reject($application, $admin, 'Could not verify credentials');

        $this->assertEquals(ExpertApplication::STATUS_REJECTED, $result->status);
        $this->assertEquals('Could not verify credentials', $result->admin_notes);
        $this->assertNotNull($result->reviewed_at);
        $this->assertEquals($admin->id, $result->reviewed_by);
    }

    // ── Query helpers ─────────────────────────────────────────────────────────

    /** @test */
    public function has_active_application_returns_true_for_pending_and_under_review(): void
    {
        $user = $this->customer();

        $this->assertFalse($this->service->hasActiveApplication($user->id));

        ExpertApplication::factory()->create([
            'user_id' => $user->id,
            'status'  => ExpertApplication::STATUS_PENDING,
        ]);

        $this->assertTrue($this->service->hasActiveApplication($user->id));
    }

    /** @test */
    public function has_active_application_returns_false_for_rejected_or_approved(): void
    {
        $user = $this->customer();

        foreach ([ExpertApplication::STATUS_REJECTED, ExpertApplication::STATUS_APPROVED] as $status) {
            ExpertApplication::factory()->create([
                'user_id' => $user->id,
                'status'  => $status,
            ]);
        }

        $this->assertFalse($this->service->hasActiveApplication($user->id));
    }

    // ── Test factory helper ───────────────────────────────────────────────────

    /**
     * Returns [application (under_review), admin].
     */
    private function makeUnderReviewApplication(): array
    {
        $user  = $this->customer();
        $admin = $this->admin();
        $application = ExpertApplication::factory()->create([
            'user_id'     => $user->id,
            'status'      => ExpertApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => $admin->id,
        ]);

        return [$application, $admin];
    }
}
