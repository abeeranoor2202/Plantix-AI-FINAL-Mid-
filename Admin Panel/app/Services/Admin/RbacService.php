<?php

namespace App\Services\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RbacService — Admin Panel Role-Based Access Control
 *
 * All RBAC business logic lives here.  Controllers are thin wrappers
 * that delegate to this service and transform the results into responses.
 *
 * Concepts:
 *   Role       — a named set of permissions, e.g. "Store Manager"
 *   Permission — a fine-grained action, e.g. name="stores.edit", group="stores"
 *   User       — an admin/staff user assigned exactly one Role (or no role = super-admin)
 *
 * Caching:
 *   Role → Permission lists are cached per role_id to reduce DB round trips.
 *   Cache is invalidated whenever a role's permissions are changed.
 */
class RbacService
{
    private const CACHE_TTL     = 1800; // 30 minutes
    private const CACHE_PREFIX  = 'rbac_perms_';

    // ──────────────────────────────────────────────────────────────────────────
    // ROLES
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return all admin roles, optionally with their permission counts.
     *
     * @return Collection<Role>
     */
    public function allRoles(bool $withPermissions = false): Collection
    {
        $query = Role::where('guard', 'admin')->orderBy('role_name');

        if ($withPermissions) {
            $query->withCount('permissions');
        }

        return $query->get();
    }

    /**
     * Find a single role by ID (admin guard only).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findRole(int $id): Role
    {
        return Role::where('guard', 'admin')->with('permissions')->findOrFail($id);
    }

    /**
     * Create a new admin role.
     *
     * @param  array{role_name: string, is_active?: bool}  $data
     */
    public function createRole(array $data): Role
    {
        $role = Role::create([
                'name'      => $data['role_name'],
            'role_name' => $data['role_name'],
            'guard'     => 'admin',
            'is_active' => $data['is_active'] ?? true,
        ]);

        Log::info('[RBAC] Role created', ['role_id' => $role->id, 'name' => $role->role_name]);

        return $role;
    }

    /**
     * Update an existing admin role's name / active state.
     *
     * @param  array{role_name?: string, is_active?: bool}  $data
     */
    public function updateRole(int $id, array $data): Role
    {
        $role = $this->findRole($id);
        $role->update(array_filter([
            'role_name' => $data['role_name'] ?? null,
            'is_active' => $data['is_active'] ?? null,
        ], fn ($v) => $v !== null));

        $this->clearRoleCache($id);

        Log::info('[RBAC] Role updated', ['role_id' => $id]);

        return $role->fresh();
    }

    /**
     * Delete a role.  Detaches all permissions first, then updates users
     * who had this role to have no role (role_id = null).
     */
    public function deleteRole(int $id): void
    {
        $role = $this->findRole($id);

        // Unassign users before deleting
        User::where('role_id', $id)->update(['role_id' => null]);

        // Detach all permissions
        $role->permissions()->detach();

        $role->delete();

        $this->clearRoleCache($id);

        Log::warning('[RBAC] Role deleted', ['role_id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PERMISSIONS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return all permissions, optionally grouped by their group column.
     *
     * @return Collection<Permission>|array<string, Collection<Permission>>
     */
    public function allPermissions(bool $grouped = false): Collection|array
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get();

        if ($grouped) {
            return $permissions->groupBy('group')->toArray();
        }

        return $permissions;
    }

    /**
     * Return the permission IDs attached to a given role.
     *
     * @return array<int>
     */
    public function rolePermissionIds(int $roleId): array
    {
        return DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->toArray();
    }

    /**
     * Create a new permission.
     *
     * @param  array{name: string, group: string, display_name: string}  $data
     */
    public function createPermission(array $data): Permission
    {
        $permission = Permission::create([
            'name'         => $data['name'],
            'group'        => $data['group'],
            'display_name' => $data['display_name'],
        ]);

        Log::info('[RBAC] Permission created', ['permission' => $permission->name]);

        return $permission;
    }

    /**
     * Update an existing permission's metadata.
     *
     * @param  array{name?: string, group?: string, display_name?: string}  $data
     */
    public function updatePermission(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update(array_filter($data, fn ($v) => $v !== null));

        // Invalidate all role caches that include this permission
        $this->clearAllRoleCaches();

        return $permission->fresh();
    }

    /**
     * Delete a permission (automatically detaches from all roles).
     */
    public function destroyPermission(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $permission->roles()->detach();
        $permission->delete();

        $this->clearAllRoleCaches();

        Log::warning('[RBAC] Permission deleted', ['permission_id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROLE ↔ PERMISSION SYNCING
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Sync (replace) the full permission set for a role.
     *
     * @param  array<int>  $permissionIds
     */
    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->findRole($roleId);
        $role->permissions()->sync($permissionIds);

        $this->clearRoleCache($roleId);

        Log::info('[RBAC] Role permissions synced', [
            'role_id'        => $roleId,
            'permission_ids' => $permissionIds,
        ]);
    }

    /**
     * Grant a single permission to a role (idempotent).
     */
    public function grantPermission(int $roleId, int $permissionId): void
    {
        $role = $this->findRole($roleId);
        $role->permissions()->syncWithoutDetaching([$permissionId]);
        $this->clearRoleCache($roleId);
    }

    /**
     * Revoke a single permission from a role.
     */
    public function revokePermission(int $roleId, int $permissionId): void
    {
        $role = $this->findRole($roleId);
        $role->permissions()->detach($permissionId);
        $this->clearRoleCache($roleId);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // USER ↔ ROLE ASSIGNMENT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Assign a role to a staff user.
     */
    public function assignRole(int $userId, int $roleId): void
    {
        $user = User::findOrFail($userId);

        if (! in_array($user->role, ['admin', 'staff'])) {
            throw new \LogicException("User {$userId} is not an admin/staff user.");
        }

        $user->update(['role_id' => $roleId]);

        Log::info('[RBAC] Role assigned to user', [
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Remove any role assignment from a staff user.
     */
    public function removeRole(int $userId): void
    {
        User::where('id', $userId)->update(['role_id' => null]);
        Log::info('[RBAC] Role removed from user', ['user_id' => $userId]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // RUNTIME PERMISSION CHECK (programmatic — not middleware)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Check whether a user has a specific permission at runtime.
     *
     * Uses cache to avoid N+1 DB calls in views / conditional logic.
     *
     * @param  User|int  $user
     */
    public function userHasPermission(User|int $user, string $permissionName): bool
    {
        $user = $user instanceof User ? $user : User::findOrFail($user);

        // Super-admins always pass
        if ($user->role === 'admin' && ! $user->role_id) {
            return true;
        }

        if (! $user->role_id) {
            return false;
        }

        $permissions = $this->getCachedRolePermissionNames($user->role_id);

        return in_array($permissionName, $permissions, true);
    }

    /**
     * Return all permission slugs for a role, with caching.
     *
     * @return array<string>
     */
    public function getCachedRolePermissionNames(int $roleId): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $roleId,
            self::CACHE_TTL,
            function () use ($roleId): array {
                return DB::table('permissions')
                    ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                    ->where('role_permissions.role_id', $roleId)
                    ->select('permissions.name', 'permissions.group', 'permissions.slug')
                    ->get()
                    ->flatMap(fn ($p) => [$p->name, $p->slug, $p->group])
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CACHE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Check if an admin user has at least one permission in the given group.
     * Used by AuthServiceProvider group-level gates (admin.{group}).
     */
    public function adminHasGroup(User|int $user, string $group): bool
    {
        if (is_int($user)) {
            $user = User::findOrFail($user);
        }

        // Super-admin guard (already handled by Gate::before, but be safe)
        if ($user->role === 'admin' && ! $user->role_id) {
            return true;
        }

        if (! $user->role_id) {
            return false;
        }

        $perms = $this->getCachedRolePermissionNames($user->role_id);

        return in_array($group, $perms, true);
    }

    public function clearRoleCache(int $roleId): void
    {
        Cache::forget(self::CACHE_PREFIX . $roleId);
    }

    public function clearAllRoleCaches(): void
    {
        Role::where('guard', 'admin')->pluck('id')->each(
            fn (int $id) => $this->clearRoleCache($id)
        );
    }
}
