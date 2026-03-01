<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Specific dates on which an expert is NOT available.
 *
 * Overrides the recurring weekly schedule.
 * Used for holidays, vacation, sick leave etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_unavailable_dates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expert_id')
                  ->constrained('experts')
                  ->onDelete('cascade');

            $table->date('unavailable_date');
            $table->string('reason')->nullable();

            // Optional: block only part of the day
            $table->time('block_from')->nullable();
            $table->time('block_until')->nullable();

            $table->timestamps();

            // An expert cannot have duplicate block entries for the same date
            $table->unique(
                ['expert_id', 'unavailable_date'],
                'expert_unavailable_unique'
            );

            $table->index(['expert_id', 'unavailable_date'], 'expert_unavailable_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_unavailable_dates');
    }
};
