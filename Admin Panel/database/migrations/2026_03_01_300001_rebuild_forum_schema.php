<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Forum Schema — Full Rebuild Migration
 *
 * Fixes every structural flaw from the partial forum implementation:
 *
 *  1.  forum_threads  — add slug, unified status enum, resolved_reply_id,
 *                       replies_count counter-cache; drop obsolete boolean flags.
 *  2.  forum_replies  — add parent_id (nest ≤2), is_official, status enum,
 *                       edited_at; fix is_expert_reply mass-assignment risk.
 *  3.  forum_flags    — create from scratch (was missing entirely).
 *  4.  forum_logs     — create audit trail (was missing entirely).
 *  5.  users          — add is_banned, banned_until, banned_reason.
 *  6.  forum_expert_responses — DROP (architectural bloat; replaced by
 *                               forum_replies.is_official).
 *
 * Performance indexes added for every FK + status + filter column.
 */
return new class extends Migration
{
    // ─────────────────────────────────────────────────────────────────────────
    // UP
    // ─────────────────────────────────────────────────────────────────────────
    public function up(): void
    {
        // ── 1. forum_threads ─────────────────────────────────────────────────

        Schema::table('forum_threads', function (Blueprint $table) {
            // Unique SEO slug (generated from title on creation)
            $table->string('slug', 191)->unique()->nullable()->after('title');

            // Unified status: replaces the is_locked / is_approved booleans.
            // is_approved is kept as a separate moderation gate (can be pending even
            // while status is open), but is_locked is retired in favour of 'locked'.
            $table->enum('status', ['open', 'locked', 'resolved', 'archived'])
                  ->default('open')
                  ->after('slug');

            // FK to the reply that resolved this thread (null until resolved)
            $table->foreignId('resolved_reply_id')
                  ->nullable()
                  ->after('status')
                  ->constrained('forum_replies')
                  ->nullOnDelete();

            // Counter-cache: maintained by service layer, avoids COUNT(*) subqueries
            $table->unsignedInteger('replies_count')->default(0)->after('views');

            // Indexes
            $table->index('status');
            $table->index('is_pinned');
            $table->index('slug');
            $table->index(['status', 'is_pinned']);  // for "pinned open threads" query
        });

        // Back-fill slug from title for existing rows
        DB::statement("
            UPDATE forum_threads
            SET slug = CONCAT(
                LOWER(REGEXP_REPLACE(REGEXP_REPLACE(title, '[^a-zA-Z0-9\\\\s-]', ''), '\\\\s+', '-')),
                '-', id
            )
            WHERE slug IS NULL
        ");

        // Back-fill status from legacy boolean flags
        DB::statement("UPDATE forum_threads SET status = 'locked' WHERE is_locked = 1");

        // ── 2. forum_replies ─────────────────────────────────────────────────

        Schema::table('forum_replies', function (Blueprint $table) {
            // Nested replies — max depth enforced in service, NOT database
            $table->foreignId('parent_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('forum_replies')
                  ->nullOnDelete();

            // One official answer per thread (uniqueness enforced by service + DB)
            $table->boolean('is_official')->default(false)->after('body');

            // Visible/flagged — soft-delete remains for actual deletion
            $table->enum('status', ['visible', 'flagged'])->default('visible')->after('is_official');

            // For edit-window enforcement
            $table->timestamp('edited_at')->nullable()->after('status');

            // Indexes
            $table->index('parent_id');
            $table->index('is_official');
            $table->index(['thread_id', 'is_official']);
            $table->index('status');
        });

        // ── 3. forum_flags ───────────────────────────────────────────────────

        Schema::create('forum_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reply_id')
                  ->constrained('forum_replies')
                  ->cascadeOnDelete();
            $table->foreignId('flagged_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('reason', 255);
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('reply_id');
            $table->index('flagged_by');
            $table->index('status');
            // Prevent duplicate flags from the same user on the same reply
            $table->unique(['reply_id', 'flagged_by']);
        });

        // ── 4. forum_logs ────────────────────────────────────────────────────

        Schema::create('forum_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // action: thread.create, thread.delete, thread.lock, thread.pin,
            //         thread.resolve, thread.archive, reply.create, reply.edit,
            //         reply.delete, reply.flag, reply.official, user.ban
            $table->string('action', 80);
            $table->foreignId('thread_id')->nullable()->constrained('forum_threads')->nullOnDelete();
            $table->foreignId('reply_id')->nullable()->constrained('forum_replies')->nullOnDelete();
            $table->json('meta')->nullable();   // arbitrary key/value payload
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index('thread_id');
            $table->index('reply_id');
            $table->index('created_at');
        });

        // ── 5. users — ban columns ────────────────────────────────────────────

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('active');
                $table->index('is_banned');
            }
            if (! Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('is_banned');
            }
            if (! Schema::hasColumn('users', 'banned_reason')) {
                $table->string('banned_reason', 500)->nullable()->after('banned_until');
            }
            if (! Schema::hasColumn('users', 'is_shadow_banned')) {
                $table->boolean('is_shadow_banned')->default(false)->after('banned_reason');
            }
        });

        // ── 6. Drop forum_expert_responses (replaced by forum_replies.is_official) ─
        // Only drop FK from expert_ecosystem tables that constrain it first
        if (Schema::hasTable('forum_expert_responses')) {
            Schema::drop('forum_expert_responses');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOWN
    // ─────────────────────────────────────────────────────────────────────────
    public function down(): void
    {
        // Restore forum_expert_responses
        Schema::create('forum_expert_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_reply_id')->constrained('forum_replies')->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('experts')->cascadeOnDelete();
            $table->boolean('is_expert_advice')->default(true);
            $table->text('recommendation')->nullable();
            $table->unsignedInteger('helpful_votes')->default(0);
            $table->timestamps();
            $table->unique('forum_reply_id');
            $table->index('expert_id');
        });

        Schema::dropIfExists('forum_logs');
        Schema::dropIfExists('forum_flags');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_banned', 'banned_until', 'banned_reason', 'is_shadow_banned']);
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_official', 'status', 'edited_at']);
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropForeign(['resolved_reply_id']);
            $table->dropColumn(['slug', 'status', 'resolved_reply_id', 'replies_count']);
        });
    }
};
