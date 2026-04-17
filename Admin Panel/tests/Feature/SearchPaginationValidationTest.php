<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ForumThread;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchPaginationValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function forum_endpoint_supports_pagination_and_search_filters_consistently(): void
    {
        $user = User::factory()->create(['role' => 'user', 'active' => true]);
        Sanctum::actingAs($user);

        ForumThread::factory()->count(22)->create(['is_approved' => true, 'title' => 'General thread']);
        ForumThread::factory()->create(['is_approved' => true, 'title' => 'Nitrogen deficiency report']);

        $response = $this->getJson('/api/v1/forum/threads?search=Nitrogen&limit=10&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.limit', 10)
            ->assertJsonPath('data.pagination.page', 1)
            ->assertJsonPath('data.pagination.total', 1);
    }

    /** @test */
    public function orders_endpoint_supports_scoped_pagination_and_filters(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'active' => true]);
        $vendorUser = User::factory()->create(['role' => 'vendor', 'active' => true]);
        $vendor = Vendor::factory()->create(['author_id' => $vendorUser->id]);

        Order::factory()->count(5)->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_PENDING,
            'total' => 120,
        ]);

        Order::factory()->count(4)->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_DELIVERED,
            'total' => 400,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/v1/orders?status=delivered&min_total=300&limit=2&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.limit', 2)
            ->assertJsonPath('data.pagination.total', 4);
    }

    /** @test */
    public function appointments_endpoint_supports_date_filters_and_limit(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'active' => true]);

        Appointment::factory()->count(3)->create([
            'user_id' => $customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDays(1),
        ]);

        Appointment::factory()->count(2)->create([
            'user_id' => $customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDays(20),
        ]);

        Sanctum::actingAs($customer);

        $from = now()->toDateString();
        $to = now()->addDays(7)->toDateString();

        $response = $this->getJson("/api/v1/appointments?date_from={$from}&date_to={$to}&limit=2&page=1");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.limit', 2)
            ->assertJsonPath('data.pagination.total', 3);
    }
}
