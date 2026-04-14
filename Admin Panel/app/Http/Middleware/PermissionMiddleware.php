<?php

namespace App\Http\Middleware;

use App\Services\Security\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware — Admin Panel ONLY
 *
 * This middleware enforces RBAC exclusively for the Admin Panel.
 * It MUST only be applied to routes under /admin/* which are already
 * protected by EnsureAdminGuard (the 'admin' middleware alias).
 *
 * Usage in routes:
 *   ->middleware('permission:stores,stores.edit')
 *
 * Arguments:
 *   $group  — permission group name (e.g. 'stores')
 *   $name   — specific permission slug (e.g. 'stores.edit')
 *
 * Super-admins (role === 'admin' with no role_id) bypass ALL checks.
 * Staff users (role === 'staff' with role_id set) are checked against
 * the permissions cached in the session by CheckUserRoleMiddleware.
 */
class PermissionMiddleware
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next, ?string $group = null, ?string $name = null): Response
    {
        // Prefer admin guard for panel routes, fallback to authenticated API user.
        $user = auth('admin')->user() ?: $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login');
        }

        if ($group && ! $this->permissions->hasGroup($user, $group)) {
            abort(403, "Access denied — missing permission group: {$group}");
        }

        if ($name && ! $this->permissions->checkPermission($user, $name)) {
            abort(403, "Access denied — missing permission: {$name}");
        }

        return $next($request);
    }
}