<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\ExpertAvailability;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Services\Shared\ScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentConsultationRulesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function online_appointment_acceptance_requires_meeting_link(): void
    {
        [$expertUser, $expert] = $this->createApprovedExpert(withProfile: true);
        $customer = User::factory()->create([
            'role' => 'user',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'expert_id' => $expert->id,
            'type' => 'online',
            'scheduled_at' => now()->addDay()->setTime(10, 0, 0),
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            'fee' => 1500,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($expertUser, 'expert')
            ->from(route('expert.appointments.show', $appointment))
            ->post(route('expert.appointments.accept', $appointment), [])
            ->assertRedirect(route('expert.appointments.show', $appointment))
            ->assertSessionHasErrors('meeting_link');

        $this->assertSame(Appointment::STATUS_PENDING_EXPERT_APPROVAL, $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->meeting_link);
    }

    /** @test */
    public function physical_booking_is_rejected_when_expert_has_no_location(): void
    {
        $customer = User::factory()->create([
            'role' => 'user',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        [, $expert] = $this->createApprovedExpert(withProfile: false);

        $scheduledAt = now()->addDays(2)->setTime(10, 0, 0);
        $this->createAvailabilityForDate($expert, $scheduledAt, '09:00:00', '17:00:00');

        $this->actingAs($customer, 'web')
            ->from(route('appointment.book'))
            ->post(route('appointment.store'), [
                'expert_id' => $expert->id,
                'type' => 'physical',
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'notes' => 'Need on-site inspection',
            ])
            ->assertRedirect(route('appointment.book'))
            ->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('appointments', [
            'expert_id' => $expert->id,
            'user_id' => $customer->id,
            'type' => 'physical',
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function overlapping_booking_time_is_blocked_for_same_expert(): void
    {
        [, $expert] = $this->createApprovedExpert(withProfile: false);

        $scheduledAt = now()->addDays(3)->setTime(11, 0, 0);
        $this->createAvailabilityForDate($expert, $scheduledAt, '09:00:00', '17:00:00');

        Appointment::create([
            'user_id' => User::factory()->create(['role' => 'user', 'active' => true])->id,
            'expert_id' => $expert->id,
            'type' => 'online',
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_CONFIRMED,
            'fee' => 1000,
            'payment_status' => 'paid',
        ]);

        $service = app(ScheduleService::class);

        try {
            $service->assertBookingAllowed($expert, Carbon::parse($scheduledAt), 'online', 60);
            $this->fail('Expected overlap validation to block the second booking.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('scheduled_at', $e->errors());
        }
    }

    private function createApprovedExpert(bool $withProfile = true): array
    {
        $expertUser = User::factory()->create([
            'role' => 'expert',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $expert = Expert::factory()->create([
            'user_id' => $expertUser->id,
            'status' => Expert::STATUS_APPROVED,
            'is_available' => true,
            'consultation_duration_minutes' => 60,
            'consultation_price' => 1500,
            'rating_avg' => 0,
            'total_appointments' => 0,
            'total_completed' => 0,
            'total_cancelled' => 0,
        ]);

        if ($withProfile) {
            ExpertProfile::create([
                'expert_id' => $expert->id,
                'approval_status' => 'approved',
                'city' => 'Lahore',
                'country' => 'Pakistan',
            ]);
        }

        return [$expertUser, $expert];
    }

    private function createAvailabilityForDate(Expert $expert, Carbon $date, string $startTime, string $endTime): void
    {
        ExpertAvailability::create([
            'expert_id' => $expert->id,
            'day' => strtolower($date->englishDayOfWeek),
            'day_of_week' => $date->dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'slot_duration' => 60,
            'is_active' => true,
        ]);
    }
}
