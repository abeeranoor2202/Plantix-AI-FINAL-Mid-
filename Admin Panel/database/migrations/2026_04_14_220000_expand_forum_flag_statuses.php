<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE forum_flags MODIFY COLUMN status ENUM('pending','reviewed','dismissed','resolved','ignored') NOT NULL DEFAULT 'pending'");

        // Normalize legacy states to the new moderation vocabulary.
        DB::table('forum_flags')->where('status', 'reviewed')->update(['status' => 'resolved']);
        DB::table('forum_flags')->where('status', 'dismissed')->update(['status' => 'ignored']);
    }

    public function down(): void
    {
        // Map back before shrinking enum set.
        DB::table('forum_flags')->where('status', 'resolved')->update(['status' => 'reviewed']);
        DB::table('forum_flags')->where('status', 'ignored')->update(['status' => 'dismissed']);

        DB::statement("ALTER TABLE forum_flags MODIFY COLUMN status ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending'");
    }
};
