<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── User Locations ────────────────────────────────────────────────────
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label')->default('default');          // home, farm, etc.
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->default('PK');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['latitude', 'longitude']);
        });

        // ── Weather Logs ──────────────────────────────────────────────────────
        Schema::create('weather_logs', function (Blueprint $table) {
            $table->id();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->decimal('feels_like_c', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();          // %
            $table->decimal('wind_speed_kmh', 7, 2)->nullable();
            $table->string('wind_direction')->nullable();
            $table->decimal('rainfall_mm', 7, 2)->nullable();
            $table->decimal('uv_index', 4, 1)->nullable();
            $table->string('condition')->nullable();                 // sunny, cloudy, rain, etc.
            $table->string('icon_code', 20)->nullable();             // OWM icon code
            $table->json('hourly_forecast')->nullable();             // [{hour, temp, condition}]
            $table->json('daily_forecast')->nullable();              // [{date, min, max, condition}]
            $table->json('raw_response')->nullable();                // full API response
            $table->boolean('has_alert')->default(false);
            $table->text('alert_message')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->index('city');
            $table->index('fetched_at');
            $table->index(['latitude', 'longitude']);
        });

        // ── Weather Alert Logs ────────────────────────────────────────────────
        Schema::create('weather_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('city')->nullable();
            $table->string('alert_type')->nullable();               // rain_alert, frost_alert, storm_alert
            $table->string('severity')->default('low');             // low, moderate, high, extreme
            $table->text('message');
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('alert_type');
            $table->index('notification_sent');
        });

        // ── AI Chat Sessions ──────────────────────────────────────────────────
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_key', 100)->unique();          // random UUID for guest chats
            $table->string('context_type')->default('general');    // general, crop_help, disease, fertilizer, order
            $table->json('context_data')->nullable();              // crop name, location, etc.
            $table->integer('message_count')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_key');
            $table->index('context_type');
        });

        // ── AI Chat Messages ──────────────────────────────────────────────────
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->json('metadata')->nullable();                   // tokens, model, latency_ms
            $table->string('model_used', 100)->nullable();          // gpt-4, rule-based, etc.
            $table->decimal('tokens_used', 10, 0)->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('role');
            $table->index('created_at');
        });

        // ── Forum AI Assistance Flags ─────────────────────────────────────────
        // (extends existing forum without altering the forum_posts table itself)
        Schema::create('forum_ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->text('ai_summary')->nullable();
            $table->json('related_topics')->nullable();
            $table->text('expert_prompt_hint')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->index('forum_thread_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_ai_suggestions');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('weather_alert_logs');
        Schema::dropIfExists('weather_logs');
        Schema::dropIfExists('user_locations');
    }
};
