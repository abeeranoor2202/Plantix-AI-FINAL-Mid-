<?php

namespace App\Services\Security;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * LoggingService
 *
 * Centralized structured logging to the system_logs table and Laravel log.
 *
 * SECURITY CONTRACT:
 *   - Never log raw passwords, tokens, credit card numbers, or PII.
 *   - All context arrays pass through sanitize() before storage.
 *   - Stack traces are NEVER stored or returned in production.
 *
 * Usage:
 *   app(LoggingService::class)->payment('Payment failed', ['order_id' => 5]);
 *   app(LoggingService::class)->unauthorized('Access denied', $request);
 *   app(LoggingService::class)->critical('DB transaction rollback', $request);
 */
class LoggingService
{
    /** Keys that will be masked with '***' before any log storage */
    const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password',
        'token', 'secret', 'api_key', 'private_key',
        'card_number', 'cvv', 'card_cvc',
        'stripe_secret', 'client_secret',
        'remember_token', 'access_token', 'refresh_token',
        'authorization',
    ];

    // ── Channel shortcuts ─────────────────────────────────────────────────────

    public function auth(string $message, array $context = [], string $level = SystemLog::LEVEL_INFO, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_AUTH, $level, $message, $context, $request);
    }

    public function payment(string $message, array $context = [], string $level = SystemLog::LEVEL_ERROR, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_PAYMENT, $level, $message, $context, $request);
    }

    public function rbac(string $message, array $context = [], string $level = SystemLog::LEVEL_WARNING, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_RBAC, $level, $message, $context, $request);
    }

    public function file(string $message, array $context = [], string $level = SystemLog::LEVEL_WARNING, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_FILE, $level, $message, $context, $request);
    }

    public function queue(string $message, array $context = [], string $level = SystemLog::LEVEL_ERROR, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_QUEUE, $level, $message, $context, $request);
    }

    public function webhook(string $message, array $context = [], string $level = SystemLog::LEVEL_ERROR, ?Request $request = null): void
    {
        $this->write(SystemLog::CHANNEL_WEBHOOK, $level, $message, $context, $request);
    }

    public function unauthorized(string $message, ?Request $request = null, array $context = []): void
    {
        $this->write(SystemLog::CHANNEL_API, SystemLog::LEVEL_WARNING, $message, $context, $request);
    }

    public function suspicious(string $message, ?Request $request = null, array $context = []): void
    {
        $this->write(SystemLog::CHANNEL_AUTH, SystemLog::LEVEL_ALERT, '[SUSPICIOUS] ' . $message, $context, $request);
    }

    public function critical(string $message, ?Request $request = null, array $context = []): void
    {
        $this->write(SystemLog::CHANNEL_APP, SystemLog::LEVEL_CRITICAL, $message, $context, $request);
    }

    // ── Generic write ─────────────────────────────────────────────────────────

    public function write(
        string   $channel,
        string   $level,
        string   $message,
        array    $context   = [],
        ?Request $request   = null,
        ?string  $traceId   = null
    ): void {
        $sanitizedContext = $this->sanitize($context);

        // Always write to Laravel log file as well (daily stack)
        logger()->{$level}("[{$channel}] {$message}", $sanitizedContext);

        // Write to DB — non-blocking insert; if DB is down we at least have the file log
        try {
            SystemLog::create([
                'level'      => $level,
                'channel'    => $channel,
                'message'    => $message,
                'context'    => empty($sanitizedContext) ? null : $sanitizedContext,
                'user_id'    => Auth::id() ?? ($request ? null : null),
                'ip_address' => $request?->ip(),
                'trace_id'   => $traceId ?? ($request ? $request->header('X-Trace-Id') : null),
            ]);
        } catch (\Throwable $e) {
            // Silently skip DB write if unavailable — file log is the safety net
            logger()->emergency('LoggingService DB write failed: ' . $e->getMessage());
        }
    }

    // ── Throwable helper ──────────────────────────────────────────────────────

    /**
     * Log a Throwable with safe context (no stack trace in production).
     */
    public function exception(
        \Throwable $e,
        string     $channel  = SystemLog::CHANNEL_APP,
        string     $level    = SystemLog::LEVEL_ERROR,
        array      $extra    = [],
        ?Request   $request  = null
    ): void {
        $context = array_merge($extra, [
            'exception_class' => get_class($e),
            'message'         => $e->getMessage(),
            'code'            => $e->getCode(),
            'file'            => app()->isProduction() ? 'hidden' : $e->getFile(),
            'line'            => app()->isProduction() ? 'hidden' : $e->getLine(),
            // Stack trace NEVER stored in production
            'trace'           => app()->isProduction() ? 'hidden' : array_slice(explode("\n", $e->getTraceAsString()), 0, 8),
        ]);

        $this->write($channel, $level, $e->getMessage(), $context, $request);
    }

    // ── Masking ───────────────────────────────────────────────────────────────

    /**
     * Recursively mask sensitive keys in a context array.
     */
    public function sanitize(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $result[$key] = '***';
            } elseif (is_array($value)) {
                $result[$key] = $this->sanitize($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
