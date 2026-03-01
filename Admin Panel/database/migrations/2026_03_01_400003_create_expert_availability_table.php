<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expert recurring weekly availability schedule.
 *
 * Each row represents a block of time on a specific day of the week
 * during which the expert is available for appointments.
 *
 * day_of_week: 0 = Sunday … 6 = Saturday (consistent with Carbon::dayOfWeek)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_availability', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expert_id')
                  ->constrained('experts')
                  ->onDelete('cascade');

            $table->tinyInteger('day_of_week')->unsigned();  // 0-6
            $table->time('start_time');
            $table->time('end_time');

            // Optional metadata
            $table->string('label')->nullable();   // e.g. "Morning shift"
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // ── Constraints ───────────────────────────────────────────────
            // An expert cannot have duplicate slots for same day+time
            $table->unique(
                ['expert_id', 'day_of_week', 'start_time'],
                'expert_availability_unique'
            );

            $table->index(['expert_id', 'day_of_week'], 'expert_availability_day_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_availability');
    }
};
