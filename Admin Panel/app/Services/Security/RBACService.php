<?php

namespace App\Services\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleLog;
use App\Models\User;
use App\Services\Security\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * RBACService
 *
 * Single source of truth for role and permission management.
 *
 * Architecture:
 *   - Permissions cached in Redis per role_id — TTL 30 min
 *   - Cache is invalidated on every role/permission change
 *   - All role changes are logged in role_logs (immutable)
 *   - Super-admin protection: cannot be demoted by a non-super-admin
 *   - Prevents removing the last admin from the system
 *
 * Controllers must call this service — never manipulate roles directly.
 */
class RBACService
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    /** Cache TTL for permission sets */
    const CACHE_TTL_SECONDS = 1800; // 30 minutes

    // ── Permission Resolution ─────────────────────────────────────────────────

    /**
     * Get all permission slugs for a role, using Redis cache.
     * Falls back to DB on cache miss.
     */
    public function permissionsForRole(int $roleId): Collection
    {
        $cacheKey = $this->permCacheKey($roleId);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($roleId) {
            return Permission::query()
                ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleId)
                ->select('permissions.*')
                ->get();
        });
    }

    /**
     * Check if a role has a specific permission slug.
     * Uses cache — no additional DB query if cache is warm.
     */
    public function roleHasPermission(int $roleId, string $permissionSlug): bool
    {
        return $this->permissionsForRole($roleId)->contains(function (Permission $permission) use ($permissionSlug): bool {
            return in_array($permissionSlug, [$permission->name, $permission->slug], true);
        });
    }

    /**
     * Check if a user has a permission.
     * Super-admin (role='admin', no role_id) always returns true.
     *
     * @param User   $user
     * @param string $permissionSlug
     */
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        return $this->permissions->checkPermission($user, $permissionSlug);
    }

    // ── Role Assignment ───────────────────────────────────────────────────────

    /**
     * Assign a role_id to a user.
     * Guards against super-admin escalation.
     *
     * @throws \RuntimeException
     */
    public function assignRole(User $target, int $roleId, User $actor, ?Request $request = null): void
    {
        $role = Role::findOrFail($roleId);

        // Block assigning a super-admin role if actor is not super-admin
        if ($role->slug === 'super-admin' && !$this->isSuperAdmin($actor)) {
            $this->writeRoleLog(
                $target->id,
                $actor->id,
                RoleLog::ACTION_SUPER_ADMIN_BLOCK,
                $target->role_id ? ($target->adminRole->role_name ?? null) : null,
                $role->role_name,
                $request
            );
            throw new \RuntimeException('Super-admin escalation blocked. Only super-admins can assign this role.');
        }

        $oldRole = $target->adminRole?->role_name;

        DB::transaction(function () use ($target, $roleId, $actor, $request, $oldRole, $role) {
            $target->update(['role_id' => $roleId]);
            $this->invalidateUserPermissionCache($target);

            $this->writeRoleLog(
                $target->id,
                $actor->id,
                RoleLog::ACTION_ROLE_ASSIGNED,
                $oldRole,
                $role->role_name,
                $request
            );
        });
    }

    /**
     * Remove a role assignment from a user.
     * Does NOT allow removing the last super-admin.
     *
     * @throws \RuntimeException
     */
    public function removeRole(User $target, User $actor, ?Request $request = null): void
    {
        if ($this->isLastAdmin($target)) {
            throw new \RuntimeException('Cannot remove the last admin from the system.');
        }

        $oldRole = $target->adminRole?->role_name;

        DB::transaction(function () use ($target, $actor, $request, $oldRole) {
            $target->update(['role_id' => null]);
            $this->invalidateUserPermissionCache($target);

            $this->writeRoleLog(
                $target->id,
                $actor->id,
                RoleLog::ACTION_ROLE_REMOVED,
                $oldRole,
                null,
                $request
            );
        });
    }

    // ── Role CRUD ─────────────────────────────────────────────────────────────

    /**
     * Create a new role with an optional set of permission IDs.
     */
    public function createRole(string $name, string $guard = 'admin', array $permissionIds = [], User $actor = null, ?Request $request = null): Role
    {
        return DB::transaction(function () use ($name, $guard, $permissionIds, $actor, $request) {
            $slug = \Illuminate\Support\Str::slug($name);
            $role = Role::create([
                'name'      => $name,
                'role_name' => $name,
                'slug'      => $slug,
                'guard'     => $guard,
                'is_active' => true,
            ]);

            if (!empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
                $this->invalidateRolePermissionCache($role->id);
            }

            if ($actor) {
                $this->writeRoleLog(null, $actor->id, RoleLog::ACTION_ROLE_CREATED, null, $name, $request);
            }

            return $role;
        });
    }

    /**
     * Sync permissions on a role (replaces existing set).
     */
    public function syncPermissions(Role $role, array $permissionIds, User $actor, ?Request $request = null): void
    {
        DB::transaction(function () use ($role, $permissionIds, $actor, $request) {
            $role->permissions()->sync($permissionIds);
            $this->invalidateRolePermissionCache($role->id);

            $this->writeRoleLog(
                null, $actor->id,
                RoleLog::ACTION_PERMISSION_ADDED,
                null,
                $role->role_name . ': synced ' . count($permissionIds) . ' permissions',
                $request
            );
        });
    }

    /**
     * Delete a role. Refuses if users are still assigned to it.
     *
     * @throws \RuntimeException
     */
    public function deleteRole(Role $role, User $actor, ?Request $request = null): void
    {
        if ($role->users()->count() > 0) {
            throw new \RuntimeException("Cannot delete role '{$role->role_name}': users are still assigned to it.");
        }

        DB::transaction(function () use ($role, $actor, $request) {
            $this->writeRoleLog(null, $actor->id, RoleLog::ACTION_ROLE_DELETED, $role->role_name, null, $request);
            $role->permissions()->detach();
            $this->invalidateRolePermissionCache($role->id);
            $role->delete();
        });
    }

    // ── Cache Management ──────────────────────────────────────────────────────

    public function invalidateRolePermissionCache(int $roleId): void
    {
        Cache::forget($this->permCacheKey($roleId));
    }

    public function invalidateUserPermissionCache(User $user): void
    {
        if ($user->role_id) {
            $this->invalidateRolePermissionCache($user->role_id);
        }
    }

    /** Warm the permission cache for all active roles */
    public function warmPermissionCache(): void
    {
        Role::where('is_active', true)->each(function (Role $role) {
            Cache::forget($this->permCacheKey($role->id));
            $this->permissionsForRole($role->id); // Triggers DB fetch + cache write
        });
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    public function isSuperAdmin(User $user): bool
    {
        if ($user->role === 'admin' && ! $user->role_id) {
            return true;
        }

        return (bool) ($user->adminRole?->slug === 'super-admin' || $user->adminRole?->role_name === 'Super Admin');
    }

    /**
     * Returns true if $target is the only user with admin access.
     * Prevents removing the last admin.
     */
    public function isLastAdmin(User $target): bool
    {
        if (! $target->role_id) {
            return false;
        }
        $count = User::where('role_id', $target->role_id)
                     ->where('id', '!=', $target->id)
                     ->count();
        return $count === 0;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function permCacheKey(int $roleId): string
    {
        return "rbac:role:{$roleId}:permissions";
    }

    private function writeRoleLog(
        ?int     $targetUserId,
        ?int     $actorId,
        string   $action,
        ?string  $oldValue,
        ?string  $newValue,
        ?Request $request
    ): void {
        RoleLog::create([
            'target_user_id' => $targetUserId,
            'actor_id'       => $actorId,
            'action'         => $action,
            'old_value'      => $oldValue,
            'new_value'      => $newValue,
            'ip_address'     => $request?->ip(),
            'context'        => $request ? ['uri' => $request->getRequestUri()] : null,
        ]);
    }
}
