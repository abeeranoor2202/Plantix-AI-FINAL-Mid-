<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
    }

    private function signedWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload);
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

    private function createPendingStripeOrder(): array
    {
        $customer = User::factory()->create(['active' => true]);
        $vendorOwner = User::factory()->create(['active' => true]);
        $vendor = Vendor::create([
            'author_id' => $vendorOwner->id,
            'title' => 'Webhook Test Vendor',
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'order_number' => 'ORD-WEBHOOK-SEC-1',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 120.00,
            'delivery_fee' => 0.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total' => 120.00,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'payment_intent_id' => 'pi_integrity_001',
            'delivery_address' => 'Test Address',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'stripe',
            'payment_type' => 'product',
            'gateway_transaction_id' => 'pi_integrity_001',
            'stripe_payment_intent_id' => 'pi_integrity_001',
            'amount' => 120.00,
            'currency' => 'usd',
            'status' => 'pending',
        ]);

        return [$customer, $order];
    }

    /** @test */
    public function valid_webhook_updates_order_status(): void
    {
        [, $order] = $this->createPendingStripeOrder();

        $response = $this->signedWebhook([
            'id' => 'evt_integrity_001',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_integrity_001',
                    'status' => 'succeeded',
                    'metadata' => ['payment_type' => 'product'],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('stripe_webhook_events', [
            'provider' => 'stripe',
            'event_id' => 'evt_integrity_001',
        ]);
    }

    /** @test */
    public function replayed_webhook_event_is_ignored_by_event_idempotency(): void
    {
        [, $order] = $this->createPendingStripeOrder();

        $payload = [
            'id' => 'evt_integrity_replay_001',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_integrity_001',
                    'status' => 'succeeded',
                    'metadata' => ['payment_type' => 'product'],
                ],
            ],
        ];

        $this->signedWebhook($payload)->assertStatus(200);
        $this->signedWebhook($payload)->assertStatus(200);

        $this->assertDatabaseCount('stripe_webhook_events', 1);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
        ]);
    }

    /** @test */
    public function direct_checkout_post_cannot_mark_order_paid(): void
    {
        [$customer, $order] = $this->createPendingStripeOrder();

        $this->actingAs($customer, 'web')
            ->post(route('checkout.pay.confirm', $order->id), [
                'card_name' => 'Client Attempt',
                'card_number' => '4242 4242 4242 4242',
                'card_exp' => '12 / 30',
                'card_cvc' => '123',
            ])
            ->assertRedirect(route('checkout.pay', $order->id));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function direct_appointment_pay_post_cannot_confirm_appointment(): void
    {
        $customer = User::factory()->create(['active' => true]);
        $expertUser = User::factory()->create(['active' => true]);
        $expert = Expert::create([
            'user_id' => $expertUser->id,
            'status' => Expert::STATUS_APPROVED,
            'specialty' => 'Soil Science',
            'bio' => 'Test expert profile',
            'is_available' => true,
            'hourly_rate' => 100.00,
            'consultation_price' => 120.00,
            'consultation_duration_minutes' => 60,
            'rating_avg' => 0,
            'total_appointments' => 0,
            'total_completed' => 0,
            'total_cancelled' => 0,
        ]);

        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'expert_id' => $expert->id,
            'scheduled_at' => now()->addDay(),
            'status' => Appointment::STATUS_PENDING_PAYMENT,
            'fee' => 80.00,
            'payment_status' => 'unpaid',
            'stripe_payment_intent_id' => 'pi_appt_integrity_001',
        ]);

        $this->actingAs($customer, 'web')
            ->post(route('appointment.pay.process', $appointment->id), [
                'card_name' => 'Client Attempt',
                'card_number' => '4242 4242 4242 4242',
                'card_exp' => '12 / 30',
                'card_cvc' => '123',
            ])
            ->assertRedirect(route('appointment.details', $appointment->id));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_PENDING_PAYMENT,
            'payment_status' => 'unpaid',
        ]);
    }
}
