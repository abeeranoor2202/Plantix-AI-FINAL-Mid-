<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Shared\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use Tests\TestCase;

class UnifiedPaymentConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
    }

    /** @test */
    public function cart_checkout_then_webhook_marks_order_paid(): void
    {
        $this->partialMock(StripeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createOrderCheckoutSession')
                ->andReturnUsing(function (Order $order) {
                    return [
                        'session' => (object) ['id' => 'cs_test_' . $order->id],
                        'paymentIntent' => (object) [
                            'id' => 'pi_order_' . $order->id,
                            'client_secret' => 'cs_order_' . $order->id,
                        ],
                        'client_secret' => 'cs_order_' . $order->id,
                        'checkout_url' => 'https://checkout.stripe.test/order/' . $order->id,
                    ];
                });
        });

        [$customer, $vendor] = $this->createCustomerAndVendor();

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Nitrogen Booster',
            'price' => 1200,
            'status' => Product::STATUS_ACTIVE,
            'is_active' => true,
            'track_stock' => false,
        ]);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1200,
        ]);

        $this->actingAs($customer, 'web')
            ->post(route('checkout.place'), [
                'delivery_address' => 'Test address',
                'payment_method' => 'stripe',
            ])
            ->assertRedirect('https://checkout.stripe.test/order/1');

        $order = Order::query()->latest('id')->firstOrFail();

        $this->signedWebhook([
            'id' => 'evt_order_checkout_1',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_order_' . $order->id,
                    'status' => 'succeeded',
                    'metadata' => ['payment_type' => 'product'],
                ],
            ],
        ])
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => 'paid',
        ]);
    }

    /** @test */
    public function webhook_marks_appointment_confirmed(): void
    {
        [$customer, $vendor] = $this->createCustomerAndVendor();

        $expertUser = User::factory()->create(['active' => true]);

        $expert = \App\Models\Expert::create([
            'user_id' => $expertUser->id,
            'status' => \App\Models\Expert::STATUS_APPROVED,
            'specialty' => 'Soil Science',
            'bio' => 'Test expert',
            'is_available' => true,
            'hourly_rate' => 100,
            'consultation_price' => 100,
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
            'fee' => 150,
            'payment_status' => 'unpaid',
            'stripe_payment_intent_id' => 'pi_appt_1',
        ]);

        $linkedOrder = Order::create([
            'order_number' => 'ORD-APPT-LINK-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 150,
            'delivery_fee' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 150,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'delivery_address' => 'x',
        ]);

        Payment::create([
            'order_id' => $linkedOrder->id,
            'appointment_id' => $appointment->id,
            'user_id' => $customer->id,
            'gateway' => 'stripe',
            'payment_type' => 'appointment',
            'stripe_payment_intent_id' => 'pi_appt_1',
            'gateway_transaction_id' => 'pi_appt_1',
            'amount' => 150,
            'currency' => 'pkr',
            'status' => 'pending',
        ]);

        $this->signedWebhook([
            'id' => 'evt_appt_checkout_1',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_appt_1',
                    'status' => 'succeeded',
                    'metadata' => ['payment_type' => 'appointment'],
                ],
            ],
        ])
            ->assertOk();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'payment_status' => 'paid',
        ]);
    }

    /** @test */
    public function payment_routes_use_consistent_messages(): void
    {
        [$customer, $vendor] = $this->createCustomerAndVendor();

        $order = Order::create([
            'order_number' => 'ORD-CONSISTENT-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 100,
            'delivery_fee' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'delivery_address' => 'x',
        ]);

        $this->actingAs($customer, 'web')
            ->get(route('payment.cancel', ['order_id' => $order->id]))
            ->assertRedirect(route('order.details', ['id' => $order->id]))
            ->assertSessionHas('error', 'Payment failed or cancelled');

        $order->update(['payment_status' => 'paid']);

        $this->actingAs($customer, 'web')
            ->get(route('payment.success', ['order_id' => $order->id]))
            ->assertOk()
            ->assertSessionHas('success', 'Payment completed successfully');
    }

    /** @test */
    public function order_api_vendor_name_is_not_null(): void
    {
        $customer = User::factory()->create(['active' => true]);
        $vendorOwner = User::factory()->create(['active' => true]);

        $vendor = Vendor::create([
            'author_id' => $vendorOwner->id,
            'title' => 'Prime Agri Store',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-API-VENDOR-1',
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 500,
            'delivery_fee' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 500,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'delivery_address' => 'x',
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/customer/orders/' . $order->id)
            ->assertOk();

        $this->assertNotNull($response->json('order.vendor_name'));
    }

    private function createCustomerAndVendor(): array
    {
        $customer = User::factory()->create(['active' => true]);
        $vendorOwner = User::factory()->create(['active' => true]);

        $vendor = Vendor::create([
            'author_id' => $vendorOwner->id,
            'title' => 'Unified Vendor',
            'is_active' => true,
            'is_approved' => true,
        ]);

        return [$customer, $vendor];
    }

    private function signedWebhook(array $payload): TestResponse
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
}
