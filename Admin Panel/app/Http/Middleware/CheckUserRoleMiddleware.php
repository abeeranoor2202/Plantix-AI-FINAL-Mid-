<?php

namespace App\Http\Middleware;

use App\Services\Security\PermissionService;
use Closure;
use Illuminate\Http\Request;

/**
 * CheckUserRoleMiddleware — Admin Panel RBAC session seeder
 *
 * Runs on every web request.  When an admin-guard user is authenticated
 * it loads their role name and all permission slugs + group names into
 * the session so PermissionMiddleware can check them cheaply (no extra
 * DB queries per route).
 *
 * Session keys written (admin guard only):
 *   admin_role        — human-readable role name, e.g. "Super Admin"
 *   admin_permissions — JSON-encoded array of permission slugs/groups
 *   admin_user_id     — prevents stale data for a different user
 *
 * Non-admin guards (vendor, expert, web/customer) are intentionally
 * skipped — they use their own guard middleware, not RBAC.
 */
class CheckUserRoleMiddleware
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $adminUser = auth('admin')->user();

        if ($adminUser) {
            $sessionKey = 'admin_user_id';

            // Only reload when the session is stale or user changed
            if (! session()->has('admin_permissions') || session($sessionKey) !== $adminUser->id) {

                $roleName    = null;
                $permissionSet = [];

                if ($adminUser->role === 'admin' && ! $adminUser->role_id) {
                    // Super-admin: wildcard marker — PermissionMiddleware short-circuits
                    $roleName    = 'Super Admin';
                    $permissionSet = ['*'];
                } else {
                    $roleName = $adminUser->adminRole?->role_name ?? $adminUser->role;
                    $permissionSet = $this->permissions->allPermissionsForUser($adminUser);
                }

                session([
                    'admin_role'        => $roleName,
                    'admin_permissions' => json_encode($permissionSet),
                    $sessionKey         => $adminUser->id,
                ]);
            }
        }

        return $next($request);
    }
}