<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_status_history')) {
            return;
        }

        $hasNote = Schema::hasColumn('order_status_history', 'note');
        $hasNotes = Schema::hasColumn('order_status_history', 'notes');

        if ($hasNote && ! $hasNotes) {
            DB::statement('ALTER TABLE order_status_history CHANGE note notes TEXT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_status_history')) {
            return;
        }

        $hasNote = Schema::hasColumn('order_status_history', 'note');
        $hasNotes = Schema::hasColumn('order_status_history', 'notes');

        if (! $hasNote && $hasNotes) {
            DB::statement('ALTER TABLE order_status_history CHANGE notes note TEXT NULL');
        }
    }
};
