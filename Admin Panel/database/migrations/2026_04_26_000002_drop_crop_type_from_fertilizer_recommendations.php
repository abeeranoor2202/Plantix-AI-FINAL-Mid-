<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove crop_type column — the fertilizer model predicts solely from N, P, K
     * and does not use crop type as a feature.
     */
    public function up(): void
    {
        Schema::table('fertilizer_recommendations', function (Blueprint $table) {
            $table->dropColumn('crop_type');
        });
    }

    public function down(): void
    {
        Schema::table('fertilizer_recommendations', function (Blueprint $table) {
            $table->string('crop_type')->nullable()->after('soil_test_id');
        });
    }
};
