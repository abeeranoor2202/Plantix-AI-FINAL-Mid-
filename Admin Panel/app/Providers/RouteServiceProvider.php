<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Panel HOME paths — used by RedirectIfAuthenticated middleware
    |--------------------------------------------------------------------------
    | Each guard redirects to its own dashboard after a successful login.
    | The "web" guard HOME is used as the fallback for the base Laravel
    | scaffolding (e.g. password-reset redirects).
    */

    /** @var string  Fallback for web/customer guard */
    public const HOME = '/';

    /** @var string  Admin panel home */
    public const ADMIN_HOME   = '/admin/dashboard';

    /** @var string  Vendor/Store panel home */
    public const VENDOR_HOME  = '/vendor/dashboard';

    /** @var string  Expert panel home */
    public const EXPERT_HOME  = '/expert/dashboard';

    /**
     * Define your route model bindings, pattern filters, and rate limits.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // ── REST API ────────────────────────────────────────────────────
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // ── Web (all panels) — delegated to routes/panels/*.php ─────────
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     * All limits set to very high values for development/testing.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('api-v1', function (Request $request) {
            return Limit::perMinute(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('api-v1-auth', function (Request $request) {
            return Limit::perMinute(10000)->by($request->ip());
        });

        RateLimiter::for('admin-api', function (Request $request) {
            return Limit::perMinute(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('expert-api', function (Request $request) {
            return Limit::perMinute(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('ai-chat', function (Request $request) {
            return Limit::perMinute(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('disease-detect', function (Request $request) {
            return Limit::perHour(10000)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10000)->by($request->ip());
        });
    }
}
