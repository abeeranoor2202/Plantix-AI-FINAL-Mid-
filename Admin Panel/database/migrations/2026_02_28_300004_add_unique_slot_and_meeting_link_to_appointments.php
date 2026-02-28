<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Prevent double-booking at the DB level
            // Unique on expert + scheduled time (only active appointments)
            $table->unique(['expert_id', 'scheduled_at'], 'uniq_expert_slot');

            // Add meeting link for video consultations
            $table->string('meeting_link', 500)->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('uniq_expert_slot');
            $table->dropColumn('meeting_link');
        });
    }
};
