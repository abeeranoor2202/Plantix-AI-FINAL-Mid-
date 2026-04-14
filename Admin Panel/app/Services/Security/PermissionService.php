<?php

namespace App\Services\Security;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    private const CACHE_TTL = 1800;
    private const CACHE_PREFIX = 'rbac:role:';

    public function checkPermission(User $user, string $permission): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $roleIds = $this->resolvedRoleIds($user);
        if ($roleIds === []) {
            return false;
        }

        $all = $this->permissionsForRoleIds($roleIds);

        return in_array($permission, $all, true)
            || in_array($this->toSlug($permission), $all, true);
    }

    public function checkAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->checkPermission($user, (string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasGroup(User $user, string $group): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $roleIds = $this->resolvedRoleIds($user);
        if ($roleIds === []) {
            return false;
        }

        $all = $this->permissionsForRoleIds($roleIds);

        if (in_array($group, $all, true)) {
            return true;
        }

        $prefix = $group . '.';
        foreach ($all as $perm) {
            if (str_starts_with($perm, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function allPermissionsForUser(User $user): array
    {
        if ($this->isSuperAdmin($user)) {
            return ['*'];
        }

        $roleIds = $this->resolvedRoleIds($user);
        if ($roleIds === []) {
            return [];
        }

        return $this->permissionsForRoleIds($roleIds);
    }

    public function clearRoleCache(int $roleId): void
    {
        Cache::forget($this->cacheKey($roleId));
    }

    public function clearAllRoleCaches(): void
    {
        Role::query()->pluck('id')->each(fn (int $id) => $this->clearRoleCache($id));
    }

    private function resolvedRoleIds(User $user): array
    {
        $legacyRoleId = $user->role_id ? [(int) $user->role_id] : [];

        if (! method_exists($user, 'roles')) {
            return $legacyRoleId;
        }

        $manyRoleIds = $user->roles()->pluck('role.id')->map(fn ($id) => (int) $id)->toArray();

        return array_values(array_unique(array_merge($legacyRoleId, $manyRoleIds)));
    }

    private function permissionsForRoleIds(array $roleIds): array
    {
        $perRole = [];

        foreach ($roleIds as $roleId) {
            $perRole[] = $this->permissionsForRole((int) $roleId);
        }

        return array_values(array_unique(array_merge(...$perRole)));
    }

    private function permissionsForRole(int $roleId): array
    {
        return Cache::remember($this->cacheKey($roleId), self::CACHE_TTL, function () use ($roleId): array {
            return DB::table('permissions')
                ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleId)
                ->select('permissions.name', 'permissions.slug', 'permissions.group')
                ->get()
                ->flatMap(fn ($row) => [$row->name, $row->slug, $row->group])
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        });
    }

    private function cacheKey(int $roleId): string
    {
        return self::CACHE_PREFIX . $roleId . ':permissions';
    }

    private function toSlug(string $permission): string
    {
        return str_replace('-', '.', strtolower($permission));
    }

    private function isSuperAdmin(User $user): bool
    {
        if ($user->role === 'admin' && ! $user->role_id) {
            return true;
        }

        return (bool) ($user->adminRole?->slug === 'super-admin');
    }
}