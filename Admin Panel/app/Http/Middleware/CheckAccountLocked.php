<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAccountLocked
 *
 * Applied globally to all web/auth routes.
 * Prevents locked accounts from accessing the system even if their session
 * is still active (e.g. admin manually locked the account via DB).
 *
 * When lockout expires, the lock is automatically cleared by AuthSecurityService.
 */
class CheckAccountLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach (['web', 'admin', 'vendor', 'expert'] as $guard) {
            $user = auth($guard)->user();

            if (!$user) {
                continue;
            }

            if (!$user->locked_until) {
                break;
            }

            if ($user->locked_until->isFuture()) {
                // Account is still locked — force logout
                auth($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $remaining = now()->diffInMinutes($user->locked_until);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Your account is temporarily locked due to too many failed login attempts. Try again in {$remaining} minute(s).",
                        'code'    => 'ACCOUNT_LOCKED',
                        'errors'  => [],
                    ], 423);
                }

                return redirect()->route('login')
                    ->withErrors(['email' => "Account locked. Try again in {$remaining} minute(s)."]);
            }

            // Lock has expired — clear it
            $user->update(['locked_until' => null, 'failed_login_attempts' => 0]);
            break;
        }

        return $next($request);
    }
}
