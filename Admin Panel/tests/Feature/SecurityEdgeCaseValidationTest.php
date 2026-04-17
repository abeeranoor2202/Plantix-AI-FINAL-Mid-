<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityEdgeCaseValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('real_time_notifications')) {
            Schema::create('real_time_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->unsignedBigInteger('receiver_id')->nullable();
                $table->string('role', 30)->default('user');
                $table->string('type', 120)->nullable();
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->string('status', 40)->default('unread');
                $table->string('action_url')->nullable();
                $table->json('metadata')->nullable();
                $table->string('dedup_key')->nullable();
                $table->boolean('read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /** @test */
    public function customer_cannot_access_another_customers_order(): void
    {
        $owner = User::factory()->create(['role' => 'user', 'active' => true]);
        $other = User::factory()->create(['role' => 'user', 'active' => true]);
        $vendor = Vendor::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'vendor_id' => $vendor->id,
        ]);

        Sanctum::actingAs($other);

        $this->getJson('/api/v1/orders/' . $order->id)
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function duplicate_notification_payloads_are_deduplicated(): void
    {
        $sender = User::factory()->create(['role' => 'admin', 'active' => true]);
        $receiver = User::factory()->create(['role' => 'user', 'active' => true]);
        $service = app(NotificationCenterService::class);

        $service->notify($sender, $receiver, 'system', 'Test duplicate', '/x', [], 'Test', 'fixed-key');
        $service->notify($sender, $receiver, 'system', 'Test duplicate', '/x', [], 'Test', 'fixed-key');

        $this->assertSame(1, Notification::query()->where('receiver_id', $receiver->id)->count());
    }

    /** @test */
    public function validation_errors_do_not_break_contract_shape(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'data', 'errors'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.');
    }
}
