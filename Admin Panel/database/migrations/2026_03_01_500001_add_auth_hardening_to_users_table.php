<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auth hardening columns.
 *
 * failed_login_attempts — incremented on each bad password; reset on success.
 * locked_until          — null = not locked; future timestamp = locked.
 * last_login_at         — updated on every successful login.
 * last_login_ip         — last successful login IP (IPv6 safe: 45 chars).
 * password_changed_at   — stamped every time password is updated; used to
 *                         invalidate sessions created before this timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('failed_login_attempts')
                  ->default(0)
                  ->after('active');

            $table->timestamp('locked_until')
                  ->nullable()
                  ->after('failed_login_attempts');

            $table->timestamp('last_login_at')
                  ->nullable()
                  ->after('locked_until');

            $table->string('last_login_ip', 45)
                  ->nullable()
                  ->after('last_login_at');

            $table->timestamp('password_changed_at')
                  ->nullable()
                  ->after('last_login_ip');

            // Index for lockout queries — checked on every login attempt
            $table->index(['email', 'locked_until'], 'users_email_locked_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_locked_idx');
            $table->dropColumn([
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
            ]);
        });
    }
};
