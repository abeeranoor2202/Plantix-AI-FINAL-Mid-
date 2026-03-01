<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable authentication event log.
 *
 * Records every login attempt (success + failure), logouts, password changes,
 * and email verification events.  Append-only (no updated_at).
 *
 * Indexed for:
 *   - Per-user audit queries   (user_id)
 *   - Security dashboards      (event, created_at)
 *   - IP-based threat analysis (ip_address)
 *   - Brute-force detection    (email, event, created_at)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Nullable — failed attempts may not map to a valid user
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('email', 255)->nullable();

            $table->string('event', 40)
                  ->comment('login_success|login_failed|logout|password_changed|password_reset|email_verified|account_locked|session_invalidated');

            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();

            // Contextual payload — guard name, fail reason, etc.
            $table->json('context')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();
            // No updated_at — append-only

            // Composite index for brute-force detection queries:
            // WHERE email = ? AND event = 'login_failed' AND created_at > ?
            $table->index(['email', 'event', 'created_at'], 'auth_logs_brute_force_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_logs');
    }
};
