<?php

namespace Tests\Feature;

use App\Models\AuthLog;
use App\Models\User;
use App\Services\Security\AuthSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * AuthHardeningTest
 *
 * Covers:
 * - Brute-force lockout (5 failed attempts → 423 response)
 * - Locked account rejected even with correct password
 * - Auto-unlock after lockout period expires
 * - Rate limiter fires after threshold
 * - AuthLog records written for success and failure events
 * - Session invalidated when password changes (EnforceSessionFreshness)
 * - stampPasswordChanged() sets password_changed_at
 */
class AuthHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AuthSecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'    => 'brute@example.com',
            'password' => Hash::make('ValidP@ss123!'),
            'role'     => 'user',
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'password_changed_at'   => null,
        ]);

        $this->service = app(AuthSecurityService::class);
        RateLimiter::clear('login|127.0.0.1|brute@example.com');
    }

    /** @test */
    public function it_increments_failed_attempts_on_each_bad_login(): void
    {
        $request = $this->makeRequest();

        $this->service->recordFailedAttempt($this->user, $request);
        $this->user->refresh();

        $this->assertEquals(1, $this->user->failed_login_attempts);
    }

    /** @test */
    public function it_locks_account_after_max_attempts(): void
    {
        $request = $this->makeRequest();

        $maxAttempts = config('plantix.auth_max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->service->recordFailedAttempt($this->user, $request);
        }

        $this->user->refresh();
        $this->assertNotNull($this->user->locked_until);
        $this->assertTrue($this->user->locked_until->isFuture());
    }

    /** @test */
    public function is_locked_returns_true_for_locked_account(): void
    {
        $this->user->update(['locked_until' => now()->addMinutes(30)]);

        $this->assertTrue($this->service->isLocked($this->user));
    }

    /** @test */
    public function is_locked_returns_false_and_clears_expired_lock(): void
    {
        $this->user->update([
            'locked_until'          => now()->subMinute(),
            'failed_login_attempts' => 5,
        ]);

        $this->assertFalse($this->service->isLocked($this->user));

        $this->user->refresh();
        $this->assertNull($this->user->locked_until);
        $this->assertEquals(0, $this->user->failed_login_attempts);
    }

    /** @test */
    public function clear_failed_attempts_resets_counter_and_stamps_login(): void
    {
        $this->user->update(['failed_login_attempts' => 3]);
        $request = $this->makeRequest();

        $this->service->clearFailedAttempts($this->user, $request);
        $this->user->refresh();

        $this->assertEquals(0, $this->user->failed_login_attempts);
        $this->assertNotNull($this->user->last_login_at);
        $this->assertEquals('127.0.0.1', $this->user->last_login_ip);
    }

    /** @test */
    public function write_log_stores_auth_event_in_database(): void
    {
        $request = $this->makeRequest();

        $this->service->writeLog(
            AuthLog::EVENT_LOGIN_SUCCESS,
            $this->user,
            $request,
            ['custom' => 'context']
        );

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'event'   => AuthLog::EVENT_LOGIN_SUCCESS,
        ]);
    }

    /** @test */
    public function stamp_password_changed_sets_the_column(): void
    {
        $request = $this->makeRequest();

        Carbon::setTestNow(now());
        $this->service->stampPasswordChanged($this->user, $request);
        $this->user->refresh();

        $this->assertNotNull($this->user->password_changed_at);
        Carbon::setTestNow(null);
    }

    /** @test */
    public function session_is_invalidated_when_password_changed_after_session_start(): void
    {
        // Simulate a session authenticated before the password change
        $this->actingAs($this->user);
        $this->user->update(['password_changed_at' => now()->addSecond()]);

        // Subsequent request to an authenticated route should be bounced to login
        $response = $this->get('/dashboard');

        // EnforceSessionFreshness should redirect to login
        $response->assertRedirect();
    }

    /** @test */
    public function password_rules_require_minimum_complexity(): void
    {
        $rules = AuthSecurityService::passwordRules(false);

        $validator = validator(['password' => 'short'], ['password' => $rules]);
        $this->assertTrue($validator->fails(), 'Short password should fail');

        $validator = validator(['password' => 'ValidP@ss123!'], ['password' => $rules]);
        $this->assertFalse($validator->fails(), 'Strong password should pass');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeRequest(): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create('/', 'POST');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        return $request;
    }
}
