<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\User;
use App\Notifications\Appointment\AdminPaymentFailureNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentRefundTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        // Boot the admin guard session
        $this->actingAs($this->admin, 'admin');
    }

    private function confirmedPaidAppointment(): Appointment
    {
        $expert = Expert::factory()->hasUser()->create();
        return Appointment::factory()->create([
            'user_id'                  => User::factory()->create()->id,
            'expert_id'                => $expert->id,
            'status'                   => Appointment::STATUS_CONFIRMED,
            'stripe_payment_intent_id' => 'pi_test_refund_100',
            'stripe_payment_status'    => 'succeeded',
            'fee'                      => 2000.00,
            'is_refunded'              => false,
        ]);
    }

    /** @test */
    public function admin_can_issue_full_refund(): void
    {
        Notification::fake();
        $appointment = $this->confirmedPaidAppointment();

        $response = $this->postJson(
            route('admin.appointments.refund', $appointment->id),
            ['reason' => 'Customer complaint resolved.']
        );

        $response->assertStatus(200);
        $this->assertTrue($appointment->fresh()->is_refunded);
    }

    /** @test */
    public function admin_can_issue_partial_refund(): void
    {
        Notification::fake();
        $appointment = $this->confirmedPaidAppointment();

        $response = $this->postJson(
            route('admin.appointments.refund', $appointment->id),
            ['amount' => 500, 'reason' => 'Partial service rendered.']
        );

        $response->assertStatus(200);
        $this->assertEquals(500.00, (float) $appointment->fresh()->refund_amount);
    }

    /** @test */
    public function admin_cannot_refund_already_refunded_appointment(): void
    {
        $appointment = $this->confirmedPaidAppointment();
        $appointment->update(['is_refunded' => true]);

        $this->postJson(
            route('admin.appointments.refund', $appointment->id),
            ['reason' => 'Double refund attempt.']
        )->assertStatus(422);
    }

    /** @test */
    public function admin_cannot_refund_unpaid_appointment(): void
    {
        $expert = Expert::factory()->hasUser()->create();
        $appointment = Appointment::factory()->create([
            'user_id'               => User::factory()->create()->id,
            'expert_id'             => $expert->id,
            'status'                => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            'stripe_payment_status' => null,
        ]);

        $this->postJson(
            route('admin.appointments.refund', $appointment->id),
            ['reason' => 'Invalid refund attempt.']
        )->assertStatus(422);
    }

    /** @test */
    public function admin_can_reassign_appointment_to_another_expert(): void
    {
        Notification::fake();
        $appointment  = $this->confirmedPaidAppointment();
        $newExpertUser = User::factory()->create();
        $newExpert    = Expert::factory()->create([
            'user_id'          => $newExpertUser->id,
            'is_available'     => true,
            'approval_status'  => 'approved',
        ]);

        $this->postJson(
            route('admin.appointments.reassign', $appointment->id),
            ['expert_id' => $newExpert->id, 'reason' => 'Original expert unavailable.']
        )->assertStatus(200);

        $this->assertEquals($newExpert->id, $appointment->fresh()->expert_id);
    }
}
