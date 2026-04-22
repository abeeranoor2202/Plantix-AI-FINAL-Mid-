<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_threads', 'tags')) {
                $table->json('tags')->nullable()->after('body');
            }
        });

        if (! Schema::hasTable('forum_thread_expert_map')) {
            Schema::create('forum_thread_expert_map', function (Blueprint $table) {
                $table->id();
                $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
                $table->foreignId('expert_id')->constrained('experts')->cascadeOnDelete();
                $table->string('match_reason', 120)->nullable();
                $table->timestamps();

                $table->unique(['forum_thread_id', 'expert_id'], 'forum_thread_expert_unique');
                $table->index(['expert_id', 'created_at'], 'forum_thread_expert_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_thread_expert_map');

        Schema::table('forum_threads', function (Blueprint $table) {
            if (Schema::hasColumn('forum_threads', 'tags')) {
                $table->dropColumn('tags');
            }
        });
    }
};

