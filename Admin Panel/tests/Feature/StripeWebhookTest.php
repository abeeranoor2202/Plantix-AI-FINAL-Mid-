<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Expert;
use App\Models\User;
use App\Notifications\Appointment\AppointmentPaymentSuccessNotification;
use App\Notifications\Appointment\ExpertNewBookingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Stripe\WebhookSignature;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
    }

    private function buildSignedRequest(array $payload): \Illuminate\Testing\TestResponse
    {
        $body      = json_encode($payload);
        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $body, $this->webhookSecret);

        return $this->call(
            'POST',
            '/api/stripe/webhook',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $body
        );
    }

    // ── payment_intent.succeeded ──────────────────────────────────────────────

    /** @test */
    public function payment_intent_succeeded_webhook_confirms_appointment(): void
    {
        Notification::fake();

        $expert   = Expert::factory()->hasUser()->create();
        $customer = User::factory()->create();
        $slot     = AppointmentSlot::factory()->create(['expert_id' => $expert->id]);

        $appointment = Appointment::factory()->create([
            'user_id'                  => $customer->id,
            'expert_id'                => $expert->id,
            'status'                   => Appointment::STATUS_PENDING_PAYMENT,
            'stripe_payment_intent_id' => 'pi_test_123',
            'slot_id'                  => $slot->id,
        ]);

        $response = $this->buildSignedRequest([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id'     => 'pi_test_123',
                    'status' => 'succeeded',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(
            Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            $appointment->fresh()->status
        );

        Notification::assertSentTo($customer, AppointmentPaymentSuccessNotification::class);
        Notification::assertSentTo($expert->user, ExpertNewBookingNotification::class);
    }

    // ── payment_intent.payment_failed ─────────────────────────────────────────

    /** @test */
    public function payment_failed_webhook_moves_to_payment_failed_and_releases_slot(): void
    {
        Notification::fake();

        $expert   = Expert::factory()->hasUser()->create();
        $customer = User::factory()->create();
        $slot     = AppointmentSlot::factory()->create(['expert_id' => $expert->id, 'is_available' => false]);

        $appointment = Appointment::factory()->create([
            'user_id'                  => $customer->id,
            'expert_id'                => $expert->id,
            'status'                   => Appointment::STATUS_PENDING_PAYMENT,
            'stripe_payment_intent_id' => 'pi_test_fail_456',
            'slot_id'                  => $slot->id,
        ]);

        $response = $this->buildSignedRequest([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id'     => 'pi_test_fail_456',
                    'status' => 'requires_payment_method',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Appointment::STATUS_PAYMENT_FAILED, $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_available);
    }

    // ── Tampered signature ─────────────────────────────────────────────────────

    /** @test */
    public function webhook_with_invalid_signature_returns_400(): void
    {
        $response = $this->withHeaders([
            'Stripe-Signature' => 't=0000000,v1=invalidsig',
            'Content-Type'     => 'application/json',
        ])->post('/api/stripe/webhook', ['type' => 'payment_intent.succeeded']);

        $response->assertStatus(400);
    }

    /** @test */
    public function webhook_is_idempotent_on_duplicate_delivery(): void
    {
        Notification::fake();

        $expert   = Expert::factory()->hasUser()->create();
        $customer = User::factory()->create();

        $appointment = Appointment::factory()->create([
            'user_id'                  => $customer->id,
            'expert_id'                => $expert->id,
            'status'                   => Appointment::STATUS_PENDING_EXPERT_APPROVAL, // already moved
            'stripe_payment_intent_id' => 'pi_test_dup_789',
            'stripe_payment_status'    => 'succeeded',
        ]);

        // Stripe retries the same event — should NOT throw, still return 200
        $response = $this->buildSignedRequest([
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_test_dup_789', 'status' => 'succeeded']],
        ]);

        $response->assertStatus(200);
        // Status must not regress
        $this->assertEquals(Appointment::STATUS_PENDING_EXPERT_APPROVAL, $appointment->fresh()->status);
    }
}
