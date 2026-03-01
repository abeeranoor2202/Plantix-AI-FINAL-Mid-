<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Consultation topic (e.g. "Wheat disease", "Soil testing")
            if (! Schema::hasColumn('appointments', 'topic')) {
                $table->string('topic', 255)->nullable()->after('notes');
            }

            // Expert rejection reason
            if (! Schema::hasColumn('appointments', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('admin_notes');
            }

            // Timestamps for key lifecycle events
            if (! Schema::hasColumn('appointments', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable();
            }
            if (! Schema::hasColumn('appointments', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            if (! Schema::hasColumn('appointments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            if (! Schema::hasColumn('appointments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['topic', 'reject_reason', 'accepted_at', 'rejected_at', 'cancelled_at', 'completed_at'] as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
