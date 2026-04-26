<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove environmental columns that are no longer used by the fertilizer
     * recommendation module. The model now relies solely on N, P, K values.
     */
    public function up(): void
    {
        Schema::table('fertilizer_recommendations', function (Blueprint $table) {
            $table->dropColumn(['growth_stage', 'ph_level', 'temperature', 'humidity']);
        });
    }

    public function down(): void
    {
        Schema::table('fertilizer_recommendations', function (Blueprint $table) {
            $table->string('growth_stage')->nullable()->after('crop_type');
            $table->decimal('ph_level', 4, 2)->nullable()->after('potassium');
            $table->decimal('temperature', 5, 2)->nullable()->after('ph_level');
            $table->decimal('humidity', 5, 2)->nullable()->after('temperature');
        });
    }
};
