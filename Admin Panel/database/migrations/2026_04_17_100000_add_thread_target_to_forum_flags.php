<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_flags', function (Blueprint $table) {
            if (! Schema::hasColumn('forum_flags', 'thread_id')) {
                $table->foreignId('thread_id')
                    ->nullable()
                    ->after('reply_id')
                    ->constrained('forum_threads')
                    ->cascadeOnDelete();
            }
        });

        // Keep legacy data valid: every existing reply flag should reference its thread.
        DB::statement('UPDATE forum_flags f JOIN forum_replies r ON r.id = f.reply_id SET f.thread_id = r.thread_id WHERE f.reply_id IS NOT NULL AND f.thread_id IS NULL');

        Schema::table('forum_flags', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_id')->nullable()->change();
        });

        // Replace single-target unique key with dual target keys (reply/thread).
        try {
            Schema::table('forum_flags', function (Blueprint $table) {
                $table->dropUnique('forum_flags_reply_id_flagged_by_unique');
            });
        } catch (\Throwable $e) {
            // Ignore if index name differs in this environment.
        }

        Schema::table('forum_flags', function (Blueprint $table) {
            $table->unique(['reply_id', 'flagged_by'], 'forum_flags_reply_user_unique');
            $table->unique(['thread_id', 'flagged_by'], 'forum_flags_thread_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('forum_flags', function (Blueprint $table) {
            try {
                $table->dropUnique('forum_flags_reply_user_unique');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropUnique('forum_flags_thread_user_unique');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('forum_flags', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_id')->nullable(false)->change();

            if (Schema::hasColumn('forum_flags', 'thread_id')) {
                $table->dropForeign(['thread_id']);
                $table->dropColumn('thread_id');
            }

            $table->unique(['reply_id', 'flagged_by']);
        });
    }
};
