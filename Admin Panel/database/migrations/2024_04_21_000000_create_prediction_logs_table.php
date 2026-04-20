<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prediction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('crop_recommendation_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Input parameters
            $table->decimal('nitrogen', 8, 2);
            $table->decimal('phosphorus', 8, 2);
            $table->decimal('potassium', 8, 2);
            $table->decimal('temperature', 8, 2);
            $table->decimal('humidity', 8, 2);
            $table->decimal('ph_level', 8, 2);
            $table->decimal('rainfall_mm', 8, 2);
            
            // Prediction results
            $table->string('predicted_crop');
            $table->decimal('confidence_score', 8, 6);
            $table->integer('confidence_percent');
            
            // API tracking
            $table->uuid('request_id')->unique()->nullable();
            $table->uuid('record_id')->unique()->nullable();
            $table->string('model_version', 50);
            $table->string('model_name', 100);
            
            // Status and timing
            $table->string('status', 50)->default('completed');
            $table->json('error_details')->nullable();
            $table->timestamp('predicted_at')->useCurrent();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('user_id');
            $table->index('predicted_crop');
            $table->index('status');
            $table->index('predicted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_logs');
    }
};
