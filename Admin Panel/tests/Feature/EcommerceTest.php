<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUserUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Review;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderShippedNotification;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * EcommerceTest — 10 scenario coverage.
 *
 * Scenarios:
 *  1. add_to_cart_stores_item_and_calculates_totals
 *  2. cod_checkout_deducts_stock_immediately
 *  3. stripe_initiate_creates_pending_payment_order_without_stock_deduction
 *  4. stripe_webhook_confirms_payment_and_deducts_stock   (idempotency)
 *  5. concurrent_buyers_one_item_only_one_succeeds        (race condition)
 *  6. coupon_per_user_limit_enforced
 *  7. invalid_order_status_transition_throws_domain_exception
 *  8. cancelled_paid_order_restores_stock
 *  9. review_blocked_without_delivered_order
 * 10. admin_can_force_status_customer_cannot
 */
class EcommerceTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function createUserWithCart(): array
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 100.00,
            'stock_quantity' => 5,
            'status'         => 'active',
        ]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'coupon_id' => null]);
        CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->price,
        ]);

        return compact('user', 'product', 'cart');
    }

    private function buildWebhookRequest(array $payload): \Illuminate\Testing\TestResponse
    {
        $secret = config('services.stripe.webhook_secret', 'whsec_test');
        $body   = json_encode($payload);
        $ts     = time();
        $sig    = 't=' . $ts . ',v1=' . hash_hmac('sha256', $ts . '.' . $body, $secret);

        return $this->call(
            'POST',
            '/api/stripe/webhook',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'],
            $body
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1 — Add to cart
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function add_to_cart_stores_item_and_calculates_totals(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10, 'status' => 'active']);

        $this->actingAs($user, 'web')
             ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3])
             ->assertRedirect();

        $cart = Cart::where('user_id', $user->id)->with('items')->first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);
        $this->assertEquals(3, $cart->items->first()->quantity);
        $this->assertEquals(150.00, $cart->items->sum(fn($i) => $i->quantity * $i->price));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2 — COD checkout deducts stock immediately
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function cod_checkout_deducts_stock_immediately(): void
    {
        ['user' => $user, 'product' => $product] = $this->createUserWithCart();

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        $order = $service->placeCodOrder($user, [
            'payment_method'   => 'cod',
            'shipping_address' => 'Test Street 1',
        ]);

        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_method' => 'cod']);

        // Stock should be deducted
        $this->assertEquals(3, $product->fresh()->stock_quantity); // 5 - 2 = 3
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3 — Stripe initiate creates pending_payment order WITHOUT deducting stock
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function stripe_initiate_creates_pending_payment_order_without_stock_deduction(): void
    {
        config(['services.stripe.secret' => 'sk_test_placeholder']);

        ['user' => $user, 'product' => $product] = $this->createUserWithCart();

        // Mock Stripe PaymentIntent::create
        $mockIntent = (object) ['id' => 'pi_test_abc', 'client_secret' => 'pi_test_abc_secret'];

        \Stripe\Stripe::setApiKey('sk_test_placeholder');

        $this->mock(\Stripe\PaymentIntent::class, function ($mock) use ($mockIntent) {
            $mock->shouldReceive('create')->andReturn($mockIntent);
        });

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        // Wrap in try-catch since we can't fully mock Stripe in integration test
        try {
            $result = $service->initiate($user, [
                'payment_method'   => 'stripe',
                'shipping_address' => 'Test Street 1',
            ]);

            $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $result['order']->status);
            // Stock must NOT be deducted at this stage
            $this->assertEquals(5, $product->fresh()->stock_quantity);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Expected in test environment without valid Stripe key
            $this->markTestSkipped('Stripe API not available in test environment.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4 — Stripe webhook confirms order + deducts stock (idempotency)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function stripe_webhook_confirms_payment_and_is_idempotent(): void
    {
        Notification::fake();
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        ['user' => $user, 'product' => $product] = $this->createUserWithCart();

        // Create a pending_payment order manually
        $order = Order::create([
            'user_id'           => $user->id,
            'vendor_id'         => $product->vendor_id ?? 1,
            'order_number'      => 'ORD-TEST-001',
            'status'            => Order::STATUS_PENDING_PAYMENT,
            'payment_status'    => 'pending',
            'payment_method'    => 'stripe',
            'payment_intent_id' => 'pi_test_webhook_001',
            'subtotal'          => 200.00,
            'grand_total'       => 200.00,
            'shipping_address'  => 'Test',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => 100.00,
        ]);

        Payment::create([
            'order_id'               => $order->id,
            'user_id'                => $user->id,
            'gateway'                => 'stripe',
            'gateway_transaction_id' => 'pi_test_webhook_001',
            'amount'                 => 200.00,
            'currency'               => 'usd',
            'status'                 => 'pending',
        ]);

        $webhookPayload = [
            'id'   => 'evt_test_001',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id'     => 'pi_test_webhook_001',
                    'amount' => 20000,
                    'status' => 'succeeded',
                ],
            ],
        ];

        // First call
        $response1 = $this->buildWebhookRequest($webhookPayload);
        $response1->assertStatus(200);

        // Second call — idempotency: should not double-deduct
        $response2 = $this->buildWebhookRequest($webhookPayload);
        $response2->assertStatus(200);

        // Stock deducted exactly once
        $this->assertEquals(3, $product->fresh()->stock_quantity); // 5 - 2 = 3
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->fresh()->status);

        Notification::assertSentTo($user, PaymentSuccessNotification::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5 — Race condition: 2 buyers, 1 item in stock — only 1 succeeds
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function concurrent_buyers_with_one_item_in_stock_only_one_succeeds(): void
    {
        $product = Product::factory()->create([
            'price'          => 100.00,
            'stock_quantity' => 1,
            'status'         => 'active',
        ]);

        ProductStock::factory()->create(['product_id' => $product->id, 'quantity' => 1]);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        $successCount = 0;
        $failCount    = 0;

        foreach (range(1, 2) as $i) {
            $user = User::factory()->create();
            $cart = Cart::create(['user_id' => $user->id]);
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => 1,
                'price'      => $product->price,
            ]);

            try {
                $service->placeCodOrder($user, [
                    'payment_method'   => 'cod',
                    'shipping_address' => 'Test St',
                ]);
                $successCount++;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $failCount++;
            }
        }

        $this->assertEquals(1, $successCount, 'Exactly one buyer should succeed');
        $this->assertEquals(1, $failCount, 'The second buyer should fail due to stock exhaustion');
        $this->assertEquals(0, $product->fresh()->stock_quantity, 'Stock should be zero');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 6 — Coupon per_user_limit enforced
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function coupon_per_user_limit_is_enforced(): void
    {
        ['user' => $user] = $this->createUserWithCart();

        $coupon = Coupon::factory()->create([
            'code'            => 'SAVE20',
            'type'            => 'percentage',
            'value'           => 20,
            'per_user_limit'  => 1,
            'usage_limit'     => 100,
            'used_count'      => 0,
            'is_active'       => true,
            'expires_at'      => now()->addDays(7),
        ]);

        // Simulate first use
        CouponUserUsage::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => 9999, // dummy
        ]);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $service->placeCodOrder($user, [
            'payment_method'   => 'cod',
            'shipping_address' => 'Test',
            'coupon_code'      => 'SAVE20',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 7 — Invalid state transition throws DomainException
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function invalid_order_status_transition_throws_domain_exception(): void
    {
        $user  = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => Order::STATUS_DELIVERED, // terminal-ish
        ]);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        $this->expectException(\DomainException::class);

        // Can't go from delivered → pending
        $service->updateStatus($order, Order::STATUS_PENDING, null, $user);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 8 — Cancelling a paid order restores stock
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function cancelling_a_paid_order_restores_stock(): void
    {
        ['user' => $user, 'product' => $product] = $this->createUserWithCart();

        // Set stock to 3 (as if 2 were deducted when order was created)
        $product->update(['stock_quantity' => 3]);
        $product->stock()->update(['quantity' => 3]);

        $order = Order::factory()->create([
            'user_id'        => $user->id,
            'status'         => Order::STATUS_CONFIRMED, // paid
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->price,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        $service->updateStatus($order, Order::STATUS_CANCELLED, 'Admin cancellation', $admin);

        // Stock restored: 3 + 2 = 5
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 9 — Review blocked if no delivered order
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function review_is_blocked_without_delivered_order(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);

        // User has order for the product but status is NOT delivered
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => Order::STATUS_CONFIRMED, // not delivered
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => $product->price,
        ]);

        $response = $this->actingAs($user, 'web')
             ->post(route('reviews.store', $product->id), [
                 'order_id' => $order->id,
                 'rating'   => 5,
                 'comment'  => 'Great product!',
             ]);

        $response->assertSessionHasErrors('order_id');
        $this->assertDatabaseMissing('reviews', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 10 — Admin can force status; customer cannot
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_force_status_that_customer_cannot(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $admin    = User::factory()->create(['role' => 'admin']);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status'  => Order::STATUS_SHIPPED,
        ]);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        // Customer cannot force an invalid transition
        $this->expectException(\DomainException::class);
        $service->updateStatus($order, Order::STATUS_CANCELLED, 'customer cancel', $customer);
    }

    /** @test */
    public function admin_can_force_cancel_from_shipped(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::factory()->create([
            'status'         => Order::STATUS_SHIPPED,
            'payment_status' => 'pending', // unpaid so no stock restore
        ]);

        /** @var CartCheckoutService $service */
        $service = app(CartCheckoutService::class);

        // Admin override — should NOT throw
        $updated = $service->updateStatus($order, Order::STATUS_CANCELLED, 'admin force cancel', $admin);
        $this->assertEquals(Order::STATUS_CANCELLED, $updated->status);
    }
}
