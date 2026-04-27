<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            // Stores the full structured JSON response from OpenRouter LLM
            $table->json('ai_plan_data')->nullable()->after('recommendations');
            // Track which AI model generated this plan
            $table->string('ai_model', 100)->nullable()->after('ai_plan_data');
        });
    }

    public function down(): void
    {
        Schema::table('crop_plans', function (Blueprint $table) {
            $table->dropColumn(['ai_plan_data', 'ai_model']);
        });
    }
};
