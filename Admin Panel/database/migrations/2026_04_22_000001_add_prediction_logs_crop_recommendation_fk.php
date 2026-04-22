<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prediction_logs') || ! Schema::hasTable('crop_recommendations')) {
            return;
        }

        Schema::table('prediction_logs', function (Blueprint $table) {
            // Keep compatibility with engines where the FK may already exist.
            try {
                $table->foreign('crop_recommendation_id')
                    ->references('id')
                    ->on('crop_recommendations')
                    ->cascadeOnDelete();
            } catch (\Throwable $e) {
                // no-op: foreign key already exists or cannot be created in current state.
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('prediction_logs')) {
            return;
        }

        Schema::table('prediction_logs', function (Blueprint $table) {
            try {
                $table->dropForeign(['crop_recommendation_id']);
            } catch (\Throwable $e) {
                // no-op: key absent.
            }
        });
    }
};
