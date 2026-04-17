<?php

namespace Tests\Feature;

use App\Models\PlatformActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1ContractTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_v1_request_uses_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(401)
            ->assertJsonStructure(['success', 'message', 'data', 'errors'])
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ]);
    }

    /** @test */
    public function login_invalid_credentials_returns_standard_error_envelope(): void
    {
        User::factory()->create([
            'email' => 'qa@example.com',
            'password' => bcrypt('correct-password'),
            'role' => 'user',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'qa@example.com',
            'password' => 'wrong-password',
            'device_name' => 'qa-suite',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['success', 'message', 'data', 'errors'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    /** @test */
    public function non_admin_cannot_access_admin_activity_log_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'user', 'active' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/activity/logs');

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden.');
    }

    /** @test */
    public function admin_can_access_activity_logs_with_standard_pagination_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        PlatformActivity::factory()->count(3)->create([
            'actor_user_id' => $admin->id,
            'actor_role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/activity/logs?limit=2&page=1');

        $response->assertOk()
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
    }
}
