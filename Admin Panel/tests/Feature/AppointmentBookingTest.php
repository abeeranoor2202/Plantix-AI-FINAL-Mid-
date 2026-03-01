<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Expert;
use App\Models\User;
use App\Notifications\Appointment\AppointmentBookingCreatedNotification;
use App\Notifications\Appointment\AdminNewBookingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private User   $customer;
    private Expert $expert;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create();
        $this->expert   = Expert::factory()->hasUser()->create([
            'is_available'     => true,
            'approval_status'  => 'approved',
            'consultation_fee' => 1500.00,
        ]);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    /** @test */
    public function customer_can_initiate_a_booking_and_receives_client_secret(): void
    {
        Notification::fake();

        // Create an available slot
        $slot = AppointmentSlot::factory()->create([
            'expert_id'    => $this->expert->id,
            'date'         => now()->addDays(3)->toDateString(),
            'start_time'   => '10:00:00',
            'end_time'     => '11:00:00',
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/customer/appointments', [
                'expert_id' => $this->expert->id,
                'slot_id'   => $slot->id,
                'topic'     => 'Crop disease issue',
                'notes'     => 'My wheat crop has spots.',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'status', 'client_secret', 'payment_intent_id']]);
        $this->assertEquals(Appointment::STATUS_PENDING_PAYMENT, $response->json('data.status'));

        Notification::assertSentTo($this->customer, AppointmentBookingCreatedNotification::class);
    }

    // ── Double-booking prevention ─────────────────────────────────────────────

    /** @test */
    public function second_booking_on_same_slot_is_rejected(): void
    {
        Notification::fake();

        $slot = AppointmentSlot::factory()->create([
            'expert_id'    => $this->expert->id,
            'date'         => now()->addDays(3)->toDateString(),
            'start_time'   => '10:00:00',
            'end_time'     => '11:00:00',
            'is_available' => true,
        ]);

        $customer2 = User::factory()->create();

        // First booking succeeds
        $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/customer/appointments', [
                'expert_id' => $this->expert->id,
                'slot_id'   => $slot->id,
            ])
            ->assertStatus(201);

        // Second booking on same slot must fail
        $this->actingAs($customer2, 'sanctum')
            ->postJson('/api/customer/appointments', [
                'expert_id' => $this->expert->id,
                'slot_id'   => $slot->id,
            ])
            ->assertStatus(422);
    }

    // ── Rate limiting ─────────────────────────────────────────────────────────

    /** @test */
    public function booking_endpoint_is_rate_limited_after_5_attempts(): void
    {
        $slot = AppointmentSlot::factory()->create([
            'expert_id'  => $this->expert->id,
            'date'       => now()->addDays(5)->toDateString(),
            'start_time' => '14:00:00',
            'end_time'   => '15:00:00',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->customer, 'sanctum')
                ->postJson('/api/customer/appointments', [
                    'expert_id' => $this->expert->id,
                    'slot_id'   => $slot->id,
                ]);
        }

        $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/customer/appointments', [
                'expert_id' => $this->expert->id,
                'slot_id'   => $slot->id,
            ])
            ->assertStatus(429);
    }

    // ── Customer cancel ───────────────────────────────────────────────────────

    /** @test */
    public function customer_can_cancel_a_pending_payment_appointment(): void
    {
        Notification::fake();

        $appointment = Appointment::factory()->create([
            'user_id'   => $this->customer->id,
            'expert_id' => $this->expert->id,
            'status'    => Appointment::STATUS_PENDING_PAYMENT,
        ]);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/customer/appointments/{$appointment->id}/cancel", [
                'reason' => 'Changed my mind.',
            ])
            ->assertStatus(200);

        $this->assertEquals(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }

    /** @test */
    public function customer_cannot_cancel_a_completed_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id'   => $this->customer->id,
            'expert_id' => $this->expert->id,
            'status'    => Appointment::STATUS_COMPLETED,
        ]);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/customer/appointments/{$appointment->id}/cancel")
            ->assertStatus(422);
    }
}
