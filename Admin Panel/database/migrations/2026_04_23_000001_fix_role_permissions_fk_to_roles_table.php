<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix role_permissions FK: role_permissions.role_id references 'role'
 * but the table was renamed to 'roles' by migration 2026_03_01_500006.
 *
 * Also fixes user_roles.role_id which has the same stale reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── role_permissions ──────────────────────────────────────────────────
        Schema::table('role_permissions', function (Blueprint $table) {
            // Drop stale FK (may be named differently across environments)
            try {
                $table->dropForeign('role_permissions_role_id_foreign');
            } catch (\Throwable) {
                // already gone or named differently
            }

            // Re-add pointing at the correct table name 'roles'
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();
        });

        // ── user_roles ────────────────────────────────────────────────────────
        if (Schema::hasTable('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
                try {
                    $table->dropForeign('user_roles_role_id_foreign');
                } catch (\Throwable) {}

                $table->foreign('role_id')
                      ->references('id')
                      ->on('roles')
                      ->cascadeOnDelete();
            });
        }

        // ── users.role_id ─────────────────────────────────────────────────────
        // Rebuild FK on users.role_id → roles.id if it points at wrong table
        try {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropForeign('users_role_id_foreign');
                } catch (\Throwable) {}

                $table->foreign('role_id')
                      ->references('id')
                      ->on('roles')
                      ->nullOnDelete();
            });
        } catch (\Throwable) {
            // If users.role_id column doesn't exist, skip
        }
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->foreign('role_id')->references('id')->on('role')->cascadeOnDelete();
        });
    }
};
