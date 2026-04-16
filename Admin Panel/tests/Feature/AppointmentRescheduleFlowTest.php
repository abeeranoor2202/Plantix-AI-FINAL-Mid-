<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentReschedule;
use App\Models\Expert;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentRescheduleFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function expert_proposal_is_visible_to_customer_and_customer_can_accept_it(): void
    {
        $customer = User::factory()->create([
            'role' => 'user',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $expertUser = User::factory()->create([
            'role' => 'expert',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $expert = Expert::factory()->approved()->create([
            'user_id' => $expertUser->id,
            'rating_avg' => 0,
            'total_appointments' => 0,
            'total_completed' => 0,
            'total_cancelled' => 0,
        ]);
        ExpertProfile::create([
            'expert_id' => $expert->id,
            'approval_status' => 'approved',
            'city' => 'Lahore',
            'country' => 'Pakistan',
        ]);

        $originalTime = now()->addDays(3)->setTime(10, 0, 0);
        $proposedTime = now()->addDays(4)->setTime(14, 30, 0);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'expert_id' => $expert->id,
            'type' => 'online',
            'scheduled_at' => $originalTime,
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_CONFIRMED,
            'fee' => 1500,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($expertUser, 'expert')
            ->post(route('expert.appointments.reschedule', $appointment), [
                'proposed_datetime' => $proposedTime->toDateTimeString(),
                'reason' => 'Need to adjust due to field emergency.',
            ])
            ->assertRedirect(route('expert.appointments.show', $appointment));

        $appointment->refresh();
        $this->assertSame(Appointment::STATUS_RESCHEDULE_REQUESTED, $appointment->status);

        $this->assertDatabaseHas('appointment_reschedules', [
            'appointment_id' => $appointment->id,
            'status' => 'pending',
        ]);

        $visibleToCustomer = $customer->appointments()
            ->where('status', Appointment::STATUS_RESCHEDULE_REQUESTED)
            ->whereKey($appointment->id)
            ->exists();

        $this->assertTrue($visibleToCustomer, 'Customer should see pending reschedule request.');

        $this->actingAs($customer, 'web')
            ->post(route('appointment.reschedule.response', $appointment->id), [
                'action' => 'accept',
            ])
            ->assertRedirect(route('appointment.details', $appointment->id));

        $appointment->refresh();
        $this->assertSame(Appointment::STATUS_RESCHEDULED, $appointment->status);
        $this->assertSame($proposedTime->format('Y-m-d H:i:00'), $appointment->scheduled_at?->format('Y-m-d H:i:00'));

        $this->assertDatabaseHas('appointment_reschedules', [
            'appointment_id' => $appointment->id,
            'status' => 'accepted',
        ]);
    }

    /** @test */
    public function customer_can_reject_proposed_reschedule_and_status_returns_to_confirmed(): void
    {
        $customer = User::factory()->create([
            'role' => 'user',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $expertUser = User::factory()->create([
            'role' => 'expert',
            'active' => true,
            'email_verified_at' => now(),
        ]);

        $expert = Expert::factory()->approved()->create([
            'user_id' => $expertUser->id,
            'rating_avg' => 0,
            'total_appointments' => 0,
            'total_completed' => 0,
            'total_cancelled' => 0,
        ]);
        ExpertProfile::create([
            'expert_id' => $expert->id,
            'approval_status' => 'approved',
            'city' => 'Lahore',
            'country' => 'Pakistan',
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'expert_id' => $expert->id,
            'type' => 'online',
            'scheduled_at' => now()->addDays(2)->setTime(9, 0, 0),
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_RESCHEDULE_REQUESTED,
            'fee' => 1500,
            'payment_status' => 'paid',
        ]);

        AppointmentReschedule::create([
            'appointment_id' => $appointment->id,
            'requested_by' => $expertUser->id,
            'original_scheduled_at' => $appointment->scheduled_at,
            'proposed_scheduled_at' => now()->addDays(3)->setTime(12, 0, 0),
            'reason' => 'Need to move this slot.',
            'status' => 'pending',
        ]);

        $this->actingAs($customer, 'web')
            ->post(route('appointment.reschedule.response', $appointment->id), [
                'action' => 'reject',
            ])
            ->assertRedirect(route('appointment.details', $appointment->id));

        $appointment->refresh();
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);

        $this->assertDatabaseHas('appointment_reschedules', [
            'appointment_id' => $appointment->id,
            'status' => 'rejected',
        ]);
    }
}
