<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expert_availability') && ! Schema::hasColumn('expert_availability', 'slot_duration')) {
            Schema::table('expert_availability', function (Blueprint $table) {
                $table->unsignedSmallInteger('slot_duration')->default(60)->after('end_time');
            });

            if (Schema::hasColumn('expert_availability', 'start_time') && Schema::hasColumn('expert_availability', 'end_time')) {
                DB::statement('UPDATE expert_availability SET slot_duration = GREATEST(1, TIMESTAMPDIFF(MINUTE, start_time, end_time))');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expert_availability') && Schema::hasColumn('expert_availability', 'slot_duration')) {
            Schema::table('expert_availability', function (Blueprint $table) {
                $table->dropColumn('slot_duration');
            });
        }
    }
};
