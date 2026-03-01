<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extend expert_profiles.approval_status enum to add 'under_review' and 'inactive'.
 *
 * MySQL requires a full column re-definition to change ENUM values.
 * We use a raw ALTER TABLE to avoid Doctrine DBAL limitations with ENUMs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Using raw SQL because Laravel's ->change() on ENUM can be unreliable
        // across drivers. NULL and DEFAULT are preserved.
        DB::statement("
            ALTER TABLE `expert_profiles`
            MODIFY COLUMN `approval_status`
                ENUM('pending','under_review','approved','rejected','suspended','inactive')
                NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Revert — rows with 'under_review' or 'inactive' will be coerced to ''
        // (empty string) by MySQL strict mode; safe to do in rollback-only context.
        DB::statement("
            ALTER TABLE `expert_profiles`
            MODIFY COLUMN `approval_status`
                ENUM('pending','approved','suspended','rejected')
                NOT NULL DEFAULT 'pending'
        ");
    }
};
