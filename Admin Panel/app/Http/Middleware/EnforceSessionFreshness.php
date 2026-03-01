<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforceSessionFreshness
 *
 * Invalidates sessions that were created BEFORE the user's last password change.
 *
 * How it works:
 *   1. On login, we store 'session_authenticated_at' in the session.
 *   2. This middleware compares it against users.password_changed_at.
 *   3. If the session is older than the password change, we force logout.
 *
 * This covers:
 *   - Admin resets another user's password → that user's active sessions die
 *   - User changes own password → their OTHER active sessions die
 *   - Role changed in DB → covered by the active flag check in RoleMiddleware
 *
 * Applied to: all authenticated web/admin/vendor/expert routes.
 */
class EnforceSessionFreshness
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach (['web', 'admin', 'vendor', 'expert'] as $guard) {
            $user = auth($guard)->user();

            if (!$user) {
                continue;
            }

            // Stamp session auth time on first request after login
            if (!$request->session()->has('session_authenticated_at')) {
                $request->session()->put('session_authenticated_at', now()->timestamp);
                break;
            }

            // No password_changed_at means no invalidation needed
            if (!$user->password_changed_at) {
                break;
            }

            $sessionTime     = $request->session()->get('session_authenticated_at', 0);
            $passwordChanged = \Carbon\Carbon::parse($user->password_changed_at)->timestamp;

            if ($sessionTime < $passwordChanged) {
                // Session predates password change — invalidate
                auth($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your session has been invalidated due to a password change. Please log in again.',
                        'code'    => 'SESSION_INVALIDATED',
                        'errors'  => [],
                    ], 401);
                }

                $loginRoute = match($guard) {
                    'admin'  => 'admin.login',
                    'vendor' => 'vendor.login',
                    'expert' => 'expert.login',
                    default  => 'login',
                };

                return redirect()->route($loginRoute)
                    ->with('status', 'Your session was invalidated. Please log in again.');
            }

            break;
        }

        return $next($request);
    }
}
