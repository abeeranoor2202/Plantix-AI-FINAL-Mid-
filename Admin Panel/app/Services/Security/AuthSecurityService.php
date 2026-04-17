<?php

namespace App\Services\Security;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * AuthSecurityService
 *
 * Owns all authentication security enforcement:
 *   - Account lockout after N failed attempts
 *   - Rate limiting via Laravel RateLimiter (Redis-backed)
 *   - Auth event logging to auth_logs table
 *   - Session invalidation on password change
 *   - Last login tracking
 *   - Password strength validation
 *
 * Controllers must NOT contain any of this logic directly.
 */
class AuthSecurityService
{
    /** Maximum failed attempts before account lock */
    const MAX_ATTEMPTS = 5;

    /** Lockout duration in minutes */
    const LOCKOUT_MINUTES = 30;

    /** Rate limit: max login attempts per minute per IP+email key */
    const RATE_LIMIT_LOGIN    = 10;
    const RATE_LIMIT_REGISTER = 5;
    const RATE_LIMIT_RESET    = 5;

    // ── Lockout ───────────────────────────────────────────────────────────────

    /**
     * Record a failed login attempt.
     * Locks the account when MAX_ATTEMPTS is reached.
     */
    public function recordFailedAttempt(User $user, Request $request): void
    {
        $user->increment('failed_login_attempts');

        if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
            $lockedUntil = now()->addMinutes(self::LOCKOUT_MINUTES);
            $user->update(['locked_until' => $lockedUntil]);

            $this->writeLog($user->id, $user->email, AuthLog::EVENT_ACCOUNT_LOCKED, $request, [
                'attempts'     => $user->failed_login_attempts,
                'locked_until' => $lockedUntil->toIso8601String(),
            ]);
        } else {
            $this->writeLog($user->id, $user->email, AuthLog::EVENT_LOGIN_FAILED, $request, [
                'attempts_so_far' => $user->failed_login_attempts,
            ]);
        }
    }

    /**
     * Record a failed login for an unknown email (no user found).
     */
    public function recordFailedAttemptByEmail(string $email, Request $request): void
    {
        $this->writeLog(null, $email, AuthLog::EVENT_LOGIN_FAILED, $request, [
            'reason' => 'email_not_found',
        ]);
    }

    /**
     * Returns true if the user is currently locked out.
     */
    public function isLocked(User $user): bool
    {
        if (!$user->locked_until) {
            return false;
        }
        if ($user->locked_until->isFuture()) {
            return true;
        }
        // Lock has expired — clear it automatically
        $user->update(['locked_until' => null, 'failed_login_attempts' => 0]);
        return false;
    }

    /**
     * Returns remaining lockout seconds (0 if not locked).
     */
    public function lockoutRemainingSeconds(User $user): int
    {
        if (!$user->locked_until || !$user->locked_until->isFuture()) {
            return 0;
        }
        return (int) now()->diffInSeconds($user->locked_until);
    }

    /**
     * Clear failed attempts and lockout on successful login.
     */
    public function clearFailedAttempts(User $user, Request $request): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'last_login_ip'         => $request->ip(),
        ]);

        $this->writeLog($user->id, $user->email, AuthLog::EVENT_LOGIN_SUCCESS, $request);
    }

    // ── Rate Limiting ─────────────────────────────────────────────────────────

    /**
     * Throttle key for login: per IP + email.
     */
    public function loginThrottleKey(string $email, Request $request): string
    {
        return 'login|' . Str::lower($email) . '|' . $request->ip();
    }

    public function tooManyLoginAttempts(string $email, Request $request): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->loginThrottleKey($email, $request),
            self::RATE_LIMIT_LOGIN
        );
    }

    public function hitLoginRateLimit(string $email, Request $request): void
    {
        RateLimiter::hit($this->loginThrottleKey($email, $request), 60);
    }

    public function loginRateLimitAvailableIn(string $email, Request $request): int
    {
        return RateLimiter::availableIn($this->loginThrottleKey($email, $request));
    }

    public function clearLoginRateLimit(string $email, Request $request): void
    {
        RateLimiter::clear($this->loginThrottleKey($email, $request));
    }

    public function registerThrottleKey(Request $request): string
    {
        return 'register|' . $request->ip();
    }

    public function tooManyRegisterAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->registerThrottleKey($request),
            self::RATE_LIMIT_REGISTER
        );
    }

    public function hitRegisterRateLimit(Request $request): void
    {
        RateLimiter::hit($this->registerThrottleKey($request), 60);
    }

    public function passwordResetThrottleKey(string $email, Request $request): string
    {
        return 'password_reset|' . Str::lower($email) . '|' . $request->ip();
    }

    public function tooManyPasswordResetAttempts(string $email, Request $request): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->passwordResetThrottleKey($email, $request),
            self::RATE_LIMIT_RESET
        );
    }

    public function hitPasswordResetRateLimit(string $email, Request $request): void
    {
        RateLimiter::hit($this->passwordResetThrottleKey($email, $request), 60);
    }

    // ── Session Invalidation on Password Change ───────────────────────────────

    /**
     * Stamp password_changed_at.  All sessions with a session_token_at BEFORE
     * this timestamp are invalidated by SessionInvalidationMiddleware.
     */
    public function stampPasswordChanged(User $user, Request $request): void
    {
        $user->update(['password_changed_at' => now()]);
        $this->writeLog($user->id, $user->email, AuthLog::EVENT_PASSWORD_CHANGED, $request);

        // If the requester is logged in as this user on the same session,
        // we regenerate their session token immediately.
        if (Auth::id() === $user->id) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    // ── Password Validation ───────────────────────────────────────────────────

    /**
     * Returns validation rules array for strong passwords.
     * Minimum 10 chars, upper + lower + digit + special char.
     */
    public static function passwordRules(bool $confirmed = true): array
    {
        $rules = [
            'required',
            'string',
            'min:10',
            'regex:/[A-Z]/',        // At least one uppercase
            'regex:/[a-z]/',        // At least one lowercase
            'regex:/[0-9]/',        // At least one digit
            'regex:/[@$!%*#?&^_\-]/', // At least one special char
        ];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(User $user, Request $request, string $guard = 'web'): void
    {
        $this->writeLog($user->id, $user->email, AuthLog::EVENT_LOGOUT, $request);
        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // ── Internal log writer ───────────────────────────────────────────────────

    public function writeLog(
        mixed   $arg1,
        mixed   $arg2,
        mixed   $arg3,
        mixed   $arg4,
        array   $context = []
    ): void {
        // New signature: writeLog(?int $userId, ?string $email, string $event, Request $request, array $context = [])
        // Legacy signature compatibility: writeLog(string $event, User $user, Request $request, array $context = [])
        if (is_string($arg1) && $arg2 instanceof User && $arg3 instanceof Request) {
            $event = $arg1;
            $user = $arg2;
            $request = $arg3;
            $context = is_array($arg4) ? $arg4 : $context;
            $userId = $user->id;
            $email = $user->email;
        } else {
            $userId = is_int($arg1) || $arg1 === null ? $arg1 : null;
            $email = is_string($arg2) || $arg2 === null ? $arg2 : null;
            $event = (string) $arg3;
            $request = $arg4 instanceof Request ? $arg4 : request();
        }

        AuthLog::create([
            'user_id'    => $userId,
            'email'      => $email,
            'event'      => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'context'    => empty($context) ? null : $context,
        ]);
    }
}
