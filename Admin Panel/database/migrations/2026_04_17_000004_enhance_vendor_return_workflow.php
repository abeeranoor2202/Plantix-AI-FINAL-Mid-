<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_reasons', function (Blueprint $table) {
            if (! Schema::hasColumn('return_reasons', 'description')) {
                $table->text('description')->nullable()->after('reason');
            }
        });

        Schema::table('returns', function (Blueprint $table) {
            // Keep status flexible for workflow variants and backward-compatible rows.
            $table->string('status', 40)->default('pending')->change();

            if (! Schema::hasColumn('returns', 'requested_at')) {
                $table->timestamp('requested_at')->nullable();
            }

            if (! Schema::hasColumn('returns', 'processed_at')) {
                $table->timestamp('processed_at')->nullable();
            }

            if (! Schema::hasColumn('returns', 'resolution_type')) {
                $table->string('resolution_type', 30)->nullable();
            }

            if (! Schema::hasColumn('returns', 'vendor_response_notes')) {
                $table->text('vendor_response_notes')->nullable();
            }

            if (! Schema::hasColumn('returns', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }

            if (! Schema::hasColumn('returns', 'vendor_responded_at')) {
                $table->timestamp('vendor_responded_at')->nullable();
            }

            if (! Schema::hasColumn('returns', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }

            // Keep migration idempotent across environments where these indexes
            // may already exist from prior schema evolution.
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            if (Schema::hasColumn('returns', 'resolution_type')) {
                $table->dropColumn('resolution_type');
            }

            if (Schema::hasColumn('returns', 'vendor_response_notes')) {
                $table->dropColumn('vendor_response_notes');
            }

            if (Schema::hasColumn('returns', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('returns', 'vendor_responded_at')) {
                $table->dropColumn('vendor_responded_at');
            }

            if (Schema::hasColumn('returns', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        Schema::table('return_reasons', function (Blueprint $table) {
            if (Schema::hasColumn('return_reasons', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
