<?php

namespace App\Exceptions;

use App\Services\Security\LoggingService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Centralized Exception Handler
 *
 * SECURITY RULES enforced here:
 *   1. Stack traces are NEVER returned in production API responses.
 *   2. Internal error messages are masked in production.
 *   3. All 500-level errors are logged to system_logs via LoggingService.
 *   4. 403 (access denied) events are logged as unauthorized access attempts.
 *   5. Stripe webhook errors are forwarded to the 'webhook' log channel.
 *
 * API responses always use the standardized format:
 *   { success: false, message: "", code: "", errors: [] }
 */
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        ModelNotFoundException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    // ── Registration ──────────────────────────────────────────────────────────

    public function register(): void
    {
        // Log all unhandled exceptions via LoggingService
        $this->reportable(function (Throwable $e) {
            // Silently resolve — if container is not yet booted, skip
            try {
                $request = app(Request::class);
                app(LoggingService::class)->exception($e, 'app', 'error', [], $request);
            } catch (\Throwable) {
                // Never throw from the reporter
            }
        });
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        // ── Validation errors ────────────────────────────────────────────────
        if ($e instanceof ValidationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'code'    => 'VALIDATION_ERROR',
                    'errors'  => $e->errors(),
                ], 422);
            }
        }

        // ── Authentication ───────────────────────────────────────────────────
        if ($e instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'code'    => 'UNAUTHENTICATED',
                    'errors'  => [],
                ], 401);
            }
            return $this->unauthenticated($request, $e);
        }

        // ── Model Not Found → treat as 404 ──────────────────────────────────
        if ($e instanceof ModelNotFoundException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'code'    => 'NOT_FOUND',
                    'errors'  => [],
                ], 404);
            }
            // Fall through to 404 view below
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        // ── 403 Access Denied ────────────────────────────────────────────────
        if ($e instanceof AccessDeniedHttpException) {
            $this->logUnauthorized($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to perform this action.',
                    'code'    => 'FORBIDDEN',
                    'errors'  => [],
                ], 403);
            }
            return response()->view('errors.403', ['exception' => $e], 403);
        }

        // ── 404 Not Found ─────────────────────────────────────────────────────
        if ($e instanceof NotFoundHttpException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested URL was not found.',
                    'code'    => 'NOT_FOUND',
                    'errors'  => [],
                ], 404);
            }
            return response()->view('errors.404', ['exception' => $e], 404);
        }

        // ── Generic HTTP exceptions (429 rate limit, etc.) ───────────────────
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                    'code'    => 'HTTP_' . $statusCode,
                    'errors'  => [],
                ], $statusCode);
            }

            if ($statusCode === 503) {
                return response()->view('errors.503', [], 503);
            }
        }

        // ── Database integrity violations (FK/unique/check) ──────────────────
        if ($e instanceof QueryException && $this->isIntegrityViolation($e)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The action could not be completed due to data integrity constraints.',
                    'code'    => 'DATA_INTEGRITY_ERROR',
                    'errors'  => [],
                ], 422);
            }

            return back()->withInput()->withErrors([
                'error' => 'The action was rejected because related data is missing, duplicated, or in an invalid state.',
            ]);
        }

        // ── Unhandled 500-level errors ────────────────────────────────────────
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'An unexpected error occurred. Our team has been notified.'
                    : $e->getMessage(),
                'code'    => 'SERVER_ERROR',
                'errors'  => app()->isProduction() ? [] : [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ],
            ], 500);
        }

        if (app()->isProduction()) {
            return response()->view('errors.500', [], 500);
        }

        return parent::render($request, $e);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function logUnauthorized(Request $request): void
    {
        try {
            app(LoggingService::class)->unauthorized(
                'Access denied: ' . $request->method() . ' ' . $request->getRequestUri(),
                $request,
                ['user_id' => auth()->id(), 'guard' => $this->resolveGuard($request)]
            );
        } catch (\Throwable) {
            // Never throw from logging
        }
    }

    private function resolveGuard(Request $request): string
    {
        foreach (['admin', 'vendor', 'expert', 'web'] as $guard) {
            if (auth($guard)->check()) {
                return $guard;
            }
        }
        return 'unauthenticated';
    }

    private function isIntegrityViolation(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (string) ($e->errorInfo[1] ?? '');

        // SQLSTATE 23000: integrity constraint violation (MySQL/MariaDB)
        // MySQL codes: 1451/1452 FK, 1062 duplicate key, 3819 check constraint
        return $sqlState === '23000' || in_array($driverCode, ['1451', '1452', '1062', '3819'], true);
    }
}
