<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Processes queued email jobs automatically after every HTTP response.
 *
 * Because this implements TerminableMiddleware, Laravel calls terminate()
 * AFTER the response has already been sent to the browser — so the end-user
 * sees zero delay. No cron, no scheduler, no manual artisan command required.
 *
 * Processes up to 10 jobs per request cycle (priority: emails-critical → emails → default).
 */
class ProcessEmailQueue
{
    public function __construct(
        private readonly Worker $worker,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Only process when the database queue has pending jobs — skip
        // static assets, health checks, and AJAX-heavy polling routes.
        if ($this->shouldSkip($request)) {
            return;
        }

        try {
            $options = new WorkerOptions(
                name:          'inline',
                backoff:       60,
                memory:        128,
                timeout:       30,
                sleep:         0,
                maxTries:      3,
                force:         false,
                stopWhenEmpty: true,  // exit as soon as queue drains
                maxJobs:       10,    // process at most 10 jobs per page load
                maxTime:       10,    // hard cap: 10 seconds total
            );

            // Priority order: payment/admin alerts first, then regular emails
            foreach (['emails-critical', 'emails', 'default'] as $queue) {
                $this->worker->daemon('database', $queue, $options);
            }
        } catch (\Throwable $e) {
            Log::warning('[ProcessEmailQueue] Failed: ' . $e->getMessage());
        }
    }

    private function shouldSkip(Request $request): bool
    {
        // Skip API calls, non-GET asset-like paths, and queue-admin routes
        // to avoid nesting queue processing inside queue processing.
        $path = $request->path();

        return str_starts_with($path, 'api/')
            || str_ends_with($path, '.js')
            || str_ends_with($path, '.css')
            || str_ends_with($path, '.png')
            || str_ends_with($path, '.ico')
            || str_starts_with($path, 'horizon')
            || str_starts_with($path, 'telescope');
    }
}
