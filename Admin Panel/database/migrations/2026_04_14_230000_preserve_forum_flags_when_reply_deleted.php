<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE forum_flags DROP FOREIGN KEY forum_flags_reply_id_foreign');
        DB::statement('ALTER TABLE forum_flags MODIFY reply_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE forum_flags ADD CONSTRAINT forum_flags_reply_id_foreign FOREIGN KEY (reply_id) REFERENCES forum_replies(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::table('forum_flags')->whereNull('reply_id')->delete();

        DB::statement('ALTER TABLE forum_flags DROP FOREIGN KEY forum_flags_reply_id_foreign');
        DB::statement('ALTER TABLE forum_flags MODIFY reply_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE forum_flags ADD CONSTRAINT forum_flags_reply_id_foreign FOREIGN KEY (reply_id) REFERENCES forum_replies(id) ON DELETE CASCADE');
    }
};
