<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expert audit log.
 *
 * Immutable event log — no updates, no deletes.
 * Every status transition, profile edit, and admin action writes a row here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expert_id')
                  ->constrained('experts')
                  ->onDelete('cascade');

            // Who performed the action (admin, system, or expert themselves)
            $table->foreignId('actor_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Action taken (e.g. 'approved', 'suspended', 'profile_updated', 'rating_updated')
            $table->string('action', 64);

            // State machine transitions
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();

            // Free-text admin note / reason
            $table->text('notes')->nullable();

            // Security / audit context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Metadata payload (JSON — stores extra context like old→new field diffs)
            $table->json('metadata')->nullable();

            // Only created_at — this log is append-only, never updated
            $table->timestamp('created_at')->useCurrent();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index(['expert_id', 'created_at'], 'expert_logs_expert_date_index');
            $table->index('action',     'expert_logs_action_index');
            $table->index('actor_id',   'expert_logs_actor_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_logs');
    }
};
