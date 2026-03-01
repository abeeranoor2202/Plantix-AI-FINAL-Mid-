<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable audit log for all role and permission changes.
 * Append-only — no updated_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('target_user_id')->index()
                  ->comment('The user whose role/permission was changed');

            $table->unsignedBigInteger('actor_id')->nullable()->index()
                  ->comment('Admin who performed the change; null = system');

            $table->string('action', 50)
                  ->comment('role_assigned|role_removed|permission_added|permission_removed|role_created|role_deleted|super_admin_escalation_blocked');

            $table->string('old_value', 255)->nullable()
                  ->comment('Previous role name or permission slug');

            $table->string('new_value', 255)->nullable()
                  ->comment('New role name or permission slug');

            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();

            $table->timestamp('created_at')->useCurrent();
            // No updated_at — immutable

            $table->index(['target_user_id', 'created_at'], 'role_logs_user_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_logs');
    }
};
