<?php

namespace Tests\Feature;

use App\Models\PlatformActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchAndActivityParityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function global_search_returns_module_level_items_and_pagination_contract(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/search?query=test&page=1&limit=5');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'query',
                    'modules' => [
                        'users' => ['items', 'pagination' => ['page', 'limit', 'total', 'last_page']],
                        'orders' => ['items', 'pagination' => ['page', 'limit', 'total', 'last_page']],
                        'appointments' => ['items', 'pagination' => ['page', 'limit', 'total', 'last_page']],
                        'forum_threads' => ['items', 'pagination' => ['page', 'limit', 'total', 'last_page']],
                        'products' => ['items', 'pagination' => ['page', 'limit', 'total', 'last_page']],
                    ],
                ],
                'errors',
            ]);
    }

    /** @test */
    public function activity_endpoint_supports_q_filter_same_as_admin_panel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'active' => true,
        ]);

        PlatformActivity::factory()->create([
            'actor_user_id' => $admin->id,
            'actor_role' => 'admin',
            'action' => 'order.dispute.escalated',
            'entity_type' => 'order_dispute',
            'context' => ['note' => 'critical refund case'],
        ]);

        PlatformActivity::factory()->create([
            'actor_user_id' => $admin->id,
            'actor_role' => 'admin',
            'action' => 'forum.thread.created',
            'entity_type' => 'forum_thread',
            'context' => ['note' => 'normal post'],
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/activity/logs?q=critical');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.action', 'order.dispute.escalated');
    }
}
