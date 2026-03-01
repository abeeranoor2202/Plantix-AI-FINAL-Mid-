<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleLog;
use App\Models\User;
use App\Services\Security\RBACService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * RBACTest
 *
 * Covers:
 * - Permission cache is populated on first call and served from cache second call
 * - Cache key is invalidated correctly after syncPermissions
 * - userHasPermission returns true for matching permission
 * - userHasPermission returns false for missing permission
 * - Super-admin bypass always grants permission
 * - assignRole() writes a RoleLog entry
 * - assignRole() blocks escalation to super-admin role by non-super-admin actor
 * - removeRole() prevents removing the last admin user
 * - deleteRole() is blocked when users are still assigned
 */
class RBACTest extends TestCase
{
    use RefreshDatabase;

    private RBACService $service;
    private Role $adminRole;
    private Role $customerRole;
    private Permission $permission;
    private User $adminUser;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RBACService::class);

        $this->adminRole = Role::create([
            'name'        => 'Admin',
            'slug'        => 'admin',
            'description' => 'Administrator role',
        ]);

        $this->customerRole = Role::create([
            'name'        => 'Customer',
            'slug'        => 'customer',
            'description' => 'Regular customer',
        ]);

        $this->permission = Permission::create([
            'name' => 'Manage Products',
            'slug' => 'manage-products',
        ]);

        $this->adminRole->permissions()->attach($this->permission->id);

        $this->adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->actor     = User::factory()->create(['role_id' => $this->adminRole->id]);

        // Clear any leftover cache
        Cache::flush();
    }

    /** @test */
    public function permissions_for_role_are_cached_after_first_call(): void
    {
        $cacheKey = "rbac:role:{$this->adminRole->id}:permissions";
        $this->assertFalse(Cache::has($cacheKey));

        $this->service->permissionsForRole($this->adminRole->id);

        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function second_call_hits_cache_without_extra_db_query(): void
    {
        $this->service->permissionsForRole($this->adminRole->id);

        // Second call — detach permissions in DB; cache should still have them
        $this->adminRole->permissions()->detach();

        $perms = $this->service->permissionsForRole($this->adminRole->id);
        $this->assertContains('manage-products', $perms->pluck('slug')->toArray());
    }

    /** @test */
    public function cache_is_cleared_after_sync_permissions(): void
    {
        $this->service->permissionsForRole($this->adminRole->id);
        $cacheKey = "rbac:role:{$this->adminRole->id}:permissions";
        $this->assertTrue(Cache::has($cacheKey));

        $this->service->syncPermissions($this->adminRole, [], $this->actor);

        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function user_has_permission_returns_true_for_assigned_permission(): void
    {
        $this->adminUser->update(['role_id' => $this->adminRole->id]);

        $this->assertTrue($this->service->userHasPermission($this->adminUser, 'manage-products'));
    }

    /** @test */
    public function user_has_permission_returns_false_for_unassigned_permission(): void
    {
        $this->adminUser->update(['role_id' => $this->customerRole->id]);

        $this->assertFalse($this->service->userHasPermission($this->adminUser, 'manage-products'));
    }

    /** @test */
    public function super_admin_user_bypasses_permission_checks(): void
    {
        $superAdminRole = Role::create([
            'name'        => 'Super Admin',
            'slug'        => 'super-admin',
            'description' => 'Full system access',
        ]);

        $superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);

        $this->assertTrue($this->service->userHasPermission($superAdmin, 'non-existent-permission'));
    }

    /** @test */
    public function assign_role_writes_role_log_entry(): void
    {
        $target = User::factory()->create(['role_id' => $this->customerRole->id]);

        $this->service->assignRole($target, $this->adminRole->id, $this->actor);

        $this->assertDatabaseHas('role_logs', [
            'target_user_id' => $target->id,
            'actor_id'       => $this->actor->id,
            'action'         => RoleLog::ACTION_ROLE_ASSIGNED,
        ]);
    }

    /** @test */
    public function assign_role_blocks_escalation_to_super_admin_by_non_super_admin(): void
    {
        $superAdminRole = Role::create([
            'name'        => 'Super Admin',
            'slug'        => 'super-admin',
            'description' => 'Full access',
        ]);

        $target = User::factory()->create(['role_id' => $this->customerRole->id]);

        $this->expectException(\RuntimeException::class);

        $this->service->assignRole($target, $superAdminRole->id, $this->actor);
    }

    /** @test */
    public function remove_role_is_blocked_when_actor_is_the_last_admin(): void
    {
        // Only one user with adminRole
        User::where('role_id', $this->adminRole->id)
            ->where('id', '!=', $this->adminUser->id)
            ->delete();

        $this->expectException(\RuntimeException::class);

        $this->service->removeRole($this->adminUser, $this->adminUser);
    }

    /** @test */
    public function delete_role_is_blocked_when_users_are_assigned(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service->deleteRole($this->adminRole, $this->actor);
    }
}
