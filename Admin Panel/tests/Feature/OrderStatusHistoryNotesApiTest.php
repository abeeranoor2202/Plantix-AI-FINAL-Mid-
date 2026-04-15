<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusHistoryNotesApiTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();
        $vendorOwner = User::factory()->create();

        $this->vendor = Vendor::create([
            'author_id' => $vendorOwner->id,
            'title' => 'Agri Vendor',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    /** @test */
    public function cancelling_order_saves_status_history_notes_without_null_values(): void
    {
        $order = $this->createOrderForCustomer(status: 'pending');

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/customer/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('success', true);

        $history = OrderStatusHistory::query()
            ->where('order_id', $order->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($history);
        $this->assertSame('cancelled', $history->status);
        $this->assertNotNull($history->notes);
        $this->assertSame('Cancelled by customer.', $history->notes);
    }

    /** @test */
    public function order_details_api_returns_notes_in_status_history_payload(): void
    {
        $order = $this->createOrderForCustomer(status: 'pending');

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'notes' => 'Initial order placed.',
            'changed_by' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/customer/orders/{$order->id}")
            ->assertOk();

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('order.status_history.0.notes', 'Initial order placed.');

        $historyPayload = $response->json('order.status_history.0');
        $this->assertArrayHasKey('notes', $historyPayload);
        $this->assertArrayNotHasKey('note', $historyPayload);
    }

    private function createOrderForCustomer(string $status = 'pending'): Order
    {
        return Order::create([
            'order_number' => 'ORD-TST-' . strtoupper(substr(uniqid('', true), -8)),
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'status' => $status,
            'subtotal' => 1000.00,
            'delivery_fee' => 100.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total' => 1100.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'delivery_address' => 'Test Address',
        ]);
    }
}
