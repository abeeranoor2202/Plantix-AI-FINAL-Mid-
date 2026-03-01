<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Centralized system log table.
 *
 * Written by LoggingService for payment failures, unauthorized access,
 * Stripe webhook errors, queue failures, DB transaction failures, and
 * any suspicious activity flag.
 *
 * Sensitive values (tokens, passwords) MUST be masked before insertion.
 * Append-only — no updated_at.
 *
 * Retention policy enforced by CleanupOldSystemLogsJob (scheduled weekly).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('level', 20)
                  ->comment('debug|info|notice|warning|error|critical|alert|emergency')
                  ->index();

            $table->string('channel', 40)->default('app')
                  ->comment('auth|payment|rbac|file|queue|webhook|api');

            $table->text('message');

            $table->json('context')->nullable()
                  ->comment('Sanitized — never contains raw passwords or tokens');

            // Who was logged in when this happened (nullable for unauthenticated events)
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('ip_address', 45)->nullable();

            // For grouping related events (e.g. a single Stripe webhook replay)
            $table->string('trace_id', 64)->nullable()->index();

            $table->timestamp('created_at')->useCurrent()->index();
            // No updated_at

            $table->index(['channel', 'level', 'created_at'], 'syslog_channel_level_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
