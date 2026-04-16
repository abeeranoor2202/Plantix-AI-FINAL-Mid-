<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualPaymentFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingOrder(): array
    {
        $customer = User::factory()->create(['active' => true]);
        $vendorOwner = User::factory()->create(['active' => true]);

        $vendor = Vendor::create([
            'author_id' => $vendorOwner->id,
            'title' => 'Manual Flag Vendor',
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'order_number' => 'ORD-MANUAL-FLAG-1',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 100.00,
            'delivery_fee' => 0.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total' => 100.00,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'delivery_address' => 'Test address',
        ]);

        return [$customer, $order];
    }

    /** @test */
    public function manual_payment_routes_are_inaccessible_when_flag_is_off(): void
    {
        config(['payment.manual_payment_enabled' => false]);

        [$customer, $order] = $this->createPendingOrder();

        $this->actingAs($customer, 'web')
            ->get(route('checkout.pay', $order->id))
            ->assertNotFound();

        $this->actingAs($customer, 'web')
            ->post(route('checkout.pay.confirm', $order->id), [
                'card_name' => 'Blocked Attempt',
                'card_number' => '4242 4242 4242 4242',
                'card_exp' => '12 / 30',
                'card_cvc' => '123',
            ])
            ->assertNotFound();
    }

    /** @test */
    public function manual_payment_page_is_accessible_when_flag_is_on(): void
    {
        config(['payment.manual_payment_enabled' => true]);

        [$customer, $order] = $this->createPendingOrder();

        $this->actingAs($customer, 'web')
            ->get(route('checkout.pay', $order->id))
            ->assertOk();
    }

    /** @test */
    public function stripe_route_remains_accessible_when_manual_flag_is_off(): void
    {
        config(['payment.manual_payment_enabled' => false]);

        [$customer, $order] = $this->createPendingOrder();

        $this->actingAs($customer, 'web')
            ->get(route('checkout.stripe.pay', $order->id))
            ->assertStatus(302);
    }
}
