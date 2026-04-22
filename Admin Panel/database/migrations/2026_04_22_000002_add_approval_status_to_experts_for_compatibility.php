<?php

use App\Models\Expert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('experts')) {
            return;
        }

        if (! Schema::hasColumn('experts', 'approval_status')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->string('approval_status', 32)
                    ->default(Expert::STATUS_PENDING)
                    ->after('status');
                $table->index('approval_status', 'experts_approval_status_index');
            });
        }

        // Backfill for existing records.
        DB::table('experts')->whereNull('approval_status')->update([
            'approval_status' => DB::raw('status'),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('experts') || ! Schema::hasColumn('experts', 'approval_status')) {
            return;
        }

        Schema::table('experts', function (Blueprint $table) {
            try {
                $table->dropIndex('experts_approval_status_index');
            } catch (\Throwable $e) {
                // no-op
            }
            $table->dropColumn('approval_status');
        });
    }
};
