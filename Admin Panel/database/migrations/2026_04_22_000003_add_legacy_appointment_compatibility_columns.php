<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('experts') && ! Schema::hasColumn('experts', 'consultation_fee')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->decimal('consultation_fee', 10, 2)->nullable()->after('consultation_price');
            });

            DB::table('experts')
                ->whereNull('consultation_fee')
                ->update(['consultation_fee' => DB::raw('consultation_price')]);
        }

        if (Schema::hasTable('expert_availability') && ! Schema::hasColumn('expert_availability', 'day')) {
            Schema::table('expert_availability', function (Blueprint $table) {
                $table->string('day', 16)->nullable()->after('expert_id');
                $table->index(['expert_id', 'day'], 'expert_availability_expert_day_index');
            });

            DB::statement("UPDATE expert_availability SET day = CASE day_of_week
                WHEN 0 THEN 'sunday'
                WHEN 1 THEN 'monday'
                WHEN 2 THEN 'tuesday'
                WHEN 3 THEN 'wednesday'
                WHEN 4 THEN 'thursday'
                WHEN 5 THEN 'friday'
                WHEN 6 THEN 'saturday'
                ELSE day
            END");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expert_availability') && Schema::hasColumn('expert_availability', 'day')) {
            Schema::table('expert_availability', function (Blueprint $table) {
                try {
                    $table->dropIndex('expert_availability_expert_day_index');
                } catch (\Throwable $e) {
                    // Ignore if index does not exist in a specific environment.
                }

                $table->dropColumn('day');
            });
        }

        if (Schema::hasTable('experts') && Schema::hasColumn('experts', 'consultation_fee')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->dropColumn('consultation_fee');
            });
        }
    }
};
