<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Farm Profiles ─────────────────────────────────────────────────────
        Schema::create('farm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('farm_name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('farm_size_acres', 10, 2)->nullable();
            $table->string('soil_type')->nullable(); // clay, sandy, loamy, silt, peat
            $table->string('water_source')->nullable(); // rain, irrigation, both
            $table->string('climate_zone')->nullable();
            $table->json('previous_crops')->nullable(); // array of crop names
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });

        // ── Soil Tests ────────────────────────────────────────────────────────
        Schema::create('soil_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('farm_profile_id')->nullable()->constrained('farm_profiles')->nullOnDelete();
            $table->decimal('nitrogen', 8, 2)->nullable();         // kg/ha
            $table->decimal('phosphorus', 8, 2)->nullable();       // kg/ha
            $table->decimal('potassium', 8, 2)->nullable();        // kg/ha
            $table->decimal('ph_level', 4, 2)->nullable();         // 0-14
            $table->decimal('organic_matter', 5, 2)->nullable();   // %
            $table->decimal('humidity', 5, 2)->nullable();         // %
            $table->decimal('rainfall_mm', 8, 2)->nullable();      // mm/year
            $table->decimal('temperature', 5, 2)->nullable();      // Celsius
            $table->string('lab_report', 500)->nullable();         // path to uploaded report
            $table->date('tested_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('farm_profile_id');
        });

        // ── Crop Recommendations ──────────────────────────────────────────────
        Schema::create('crop_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('soil_test_id')->nullable()->constrained('soil_tests')->nullOnDelete();
            // Input snapshot (stored for history / ML training)
            $table->decimal('nitrogen', 8, 2)->nullable();
            $table->decimal('phosphorus', 8, 2)->nullable();
            $table->decimal('potassium', 8, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('rainfall_mm', 8, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            // Output
            $table->json('recommended_crops')->nullable();   // [{name, confidence, notes}]
            $table->text('explanation')->nullable();
            $table->string('model_version', 50)->default('rule-based-v1');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('created_at');
        });

        // ── Crop Plans ────────────────────────────────────────────────────────
        Schema::create('crop_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('farm_profile_id')->nullable()->constrained('farm_profiles')->nullOnDelete();
            $table->string('season')->nullable();         // Kharif, Rabi, Zaid
            $table->integer('year')->nullable();
            $table->string('primary_crop')->nullable();
            $table->json('crop_schedule')->nullable();    // [{crop, start_week, end_week, phase, notes}]
            $table->json('water_plan')->nullable();       // [{week, irrigation_mm}]
            $table->decimal('expected_yield_tons', 10, 3)->nullable();
            $table->decimal('estimated_revenue', 12, 2)->nullable();
            $table->text('soil_suitability_notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index(['season', 'year']);
        });

        // ── Seasonal Data (reference table) ──────────────────────────────────
        Schema::create('seasonal_data', function (Blueprint $table) {
            $table->id();
            $table->string('season');                             // Kharif, Rabi, Zaid
            $table->string('region')->nullable();
            $table->string('crop_name');
            $table->string('sowing_months')->nullable();         // e.g. "June-July"
            $table->string('harvesting_months')->nullable();     // e.g. "October-November"
            $table->decimal('water_requirement_mm', 8, 2)->nullable();
            $table->string('soil_type_compatibility')->nullable();
            $table->string('min_temp_celsius')->nullable();
            $table->string('max_temp_celsius')->nullable();
            $table->decimal('avg_yield_tons_per_acre', 8, 3)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['season', 'crop_name']);
        });

        // ── Crop Disease Reports ──────────────────────────────────────────────
        Schema::create('crop_disease_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('crop_name')->nullable();
            $table->string('image_path', 500);                  // stored image
            $table->string('detected_disease')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100%
            $table->json('all_predictions')->nullable();          // [{disease, confidence}]
            $table->string('model_used', 100)->default('plantix-ai-v1');
            $table->enum('status', ['pending', 'processed', 'failed', 'manual_review'])->default('pending');
            $table->text('user_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('detected_disease');
        });

        // ── Disease Suggestions ───────────────────────────────────────────────
        Schema::create('disease_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('crop_disease_reports')->cascadeOnDelete();
            $table->string('disease_name');
            $table->text('description')->nullable();
            $table->text('organic_treatment')->nullable();
            $table->text('chemical_treatment')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->json('recommended_products')->nullable();   // product IDs from store
            $table->boolean('expert_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('report_id');
            $table->index('disease_name');
        });

        // ── Fertilizer Recommendations ────────────────────────────────────────
        Schema::create('fertilizer_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('soil_test_id')->nullable()->constrained('soil_tests')->nullOnDelete();
            $table->string('crop_type');
            $table->string('growth_stage')->nullable();            // seedling, vegetative, flowering, fruiting
            // Input snapshot
            $table->decimal('nitrogen', 8, 2)->nullable();
            $table->decimal('phosphorus', 8, 2)->nullable();
            $table->decimal('potassium', 8, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            // Output
            $table->json('fertilizer_plan')->nullable();          // [{name, type, dose_kg_per_acre, timing, notes}]
            $table->text('application_instructions')->nullable();
            $table->decimal('estimated_cost_pkr', 12, 2)->nullable();
            $table->string('model_version', 50)->default('rule-based-v1');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('crop_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilizer_recommendations');
        Schema::dropIfExists('disease_suggestions');
        Schema::dropIfExists('crop_disease_reports');
        Schema::dropIfExists('seasonal_data');
        Schema::dropIfExists('crop_plans');
        Schema::dropIfExists('crop_recommendations');
        Schema::dropIfExists('soil_tests');
        Schema::dropIfExists('farm_profiles');
    }
};
