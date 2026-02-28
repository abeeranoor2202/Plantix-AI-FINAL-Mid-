<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── User Locations ─────────────────────────────────────────────────────
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_primary']);
        });

        // ── Weather Alert Logs ─────────────────────────────────────────────────
        Schema::create('weather_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('alert_type', 50);         // heat_stress, frost_alert, heavy_rain, etc.
            $table->string('severity', 20);           // low, medium, high, extreme
            $table->text('message');
            $table->string('city', 100)->nullable();
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_alert_logs');
        Schema::dropIfExists('user_locations');
    }
};
