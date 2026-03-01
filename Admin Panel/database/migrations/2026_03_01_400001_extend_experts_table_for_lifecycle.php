<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the experts table with:
 * - Full lifecycle status column (single source of truth)
 * - Rating / counters
 * - Timestamps for state transitions
 * - Consultation pricing
 * - Renames avatar → profile_image
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            // ── Lifecycle ─────────────────────────────────────────────────
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'suspended',
                'inactive',
            ])->default('pending')->after('user_id');

            // ── Rating & counters ─────────────────────────────────────────
            $table->decimal('rating_avg', 3, 2)->default(0)->after('hourly_rate');
            $table->unsignedInteger('total_appointments')->default(0)->after('rating_avg');
            $table->unsignedInteger('total_completed')->default(0)->after('total_appointments');
            $table->unsignedInteger('total_cancelled')->default(0)->after('total_completed');

            // ── Consultation fields ───────────────────────────────────────
            $table->decimal('consultation_price', 10, 2)->nullable()->after('hourly_rate');
            $table->unsignedSmallInteger('consultation_duration_minutes')->default(60)->after('consultation_price');

            // ── State transition timestamps ───────────────────────────────
            $table->timestamp('verified_at')->nullable()->after('total_cancelled');
            $table->timestamp('suspended_at')->nullable()->after('verified_at');
            $table->text('rejection_reason')->nullable()->after('suspended_at');

            // ── Rename avatar → profile_image ─────────────────────────────
            $table->renameColumn('avatar', 'profile_image');

            // ── Indexes ───────────────────────────────────────────────────
            $table->index('status', 'experts_status_index');
            $table->index(['status', 'is_available'], 'experts_status_available_index');
            $table->index('rating_avg', 'experts_rating_index');
        });
    }

    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropIndex('experts_status_index');
            $table->dropIndex('experts_status_available_index');
            $table->dropIndex('experts_rating_index');

            $table->dropColumn([
                'status',
                'rating_avg',
                'total_appointments',
                'total_completed',
                'total_cancelled',
                'consultation_price',
                'consultation_duration_minutes',
                'verified_at',
                'suspended_at',
                'rejection_reason',
            ]);

            $table->renameColumn('profile_image', 'avatar');
        });
    }
};
