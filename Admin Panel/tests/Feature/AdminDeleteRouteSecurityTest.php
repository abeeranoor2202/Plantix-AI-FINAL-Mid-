<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeleteRouteSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_delete_route_rejects_get_requests(): void
    {
        $actor = User::factory()->create([
            'role' => 'admin',
            'active' => true,
            'role_id' => null,
        ]);

        $target = User::factory()->create();

        $response = $this->actingAs($actor, 'admin')
            ->get(route('admin.users.delete', $target->id));

        $response->assertStatus(405);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    /** @test */
    public function authorized_admin_can_delete_user_via_delete_request(): void
    {
        $actor = User::factory()->create([
            'role' => 'admin',
            'active' => true,
            'role_id' => null,
        ]);

        $target = User::factory()->create();

        $response = $this->actingAs($actor, 'admin')
            ->delete(route('admin.users.delete', $target->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    /** @test */
    public function unauthorized_admin_context_cannot_delete_user(): void
    {
        $limitedRole = Role::create([
            'role_name' => 'Limited Admin',
            'guard' => 'admin',
            'is_active' => true,
        ]);

        $actor = User::factory()->create([
            'role' => 'admin',
            'active' => true,
            'role_id' => $limitedRole->id,
        ]);

        $target = User::factory()->create();

        $response = $this->actingAs($actor, 'admin')
            ->delete(route('admin.users.delete', $target->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }
}
