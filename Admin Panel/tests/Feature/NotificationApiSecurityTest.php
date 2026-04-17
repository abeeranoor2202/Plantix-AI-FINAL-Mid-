<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_cannot_mark_another_users_notification_as_read(): void
    {
        $actor = User::factory()->create([
            'role' => 'user',
            'active' => true,
        ]);

        $owner = User::factory()->create([
            'role' => 'user',
            'active' => true,
        ]);

        $notification = Notification::query()->create([
            'sender_id' => null,
            'receiver_id' => $owner->id,
            'recipient_id' => $owner->id,
            'role' => 'user',
            'type' => 'system',
            'title' => 'Owner notice',
            'message' => 'Private notification',
            'status' => 'unread',
            'read' => false,
            'sent_at' => now(),
        ]);

        Sanctum::actingAs($actor);

        $response = $this->patchJson('/api/customer/notifications/' . $notification->id . '/read');

        $response->assertStatus(403);

        $this->assertDatabaseHas('real_time_notifications', [
            'id' => $notification->id,
            'read' => 0,
            'status' => 'unread',
        ]);
    }

    /** @test */
    public function customer_notification_endpoints_use_standard_success_envelope_and_log_actions(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'active' => true,
        ]);

        Notification::query()->create([
            'sender_id' => null,
            'receiver_id' => $user->id,
            'recipient_id' => $user->id,
            'role' => 'user',
            'type' => 'order',
            'title' => 'Order update',
            'message' => 'Order status changed',
            'status' => 'unread',
            'read' => false,
            'sent_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $index = $this->getJson('/api/customer/notifications');

        $index->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'pagination' => ['page', 'limit', 'total', 'last_page'],
                ],
                'errors',
            ]);

        $readAll = $this->patchJson('/api/customer/notifications/mark-all-read');

        $readAll->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'All notifications marked as read.');

        $this->assertDatabaseHas('platform_activities', [
            'actor_user_id' => $user->id,
            'action' => 'notification.read_all',
            'entity_type' => 'notification',
        ]);
    }
}
