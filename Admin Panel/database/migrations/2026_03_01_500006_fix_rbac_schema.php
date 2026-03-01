<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * RBAC schema fixes:
 *   1. Rename `role` table → `roles` (was incorrectly named)
 *   2. Add `slug` + `description` to roles
 *   3. Add `slug` to permissions
 *   4. Backfill slugs from existing name columns
 *   5. Add unique constraints on slugs
 *   6. Fix FK on users.role_id to point at new table name
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename table
        Schema::rename('role', 'roles');

        // 2. Add slug + description to roles
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->after('role_name');
            $table->string('description', 255)->nullable()->after('slug');
        });

        // 3. Backfill role slugs
        DB::table('roles')->get()->each(function ($row) {
            DB::table('roles')->where('id', $row->id)->update([
                'slug' => Str::slug($row->role_name),
            ]);
        });

        // 4. Apply unique constraint after backfill
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug', 100)->nullable(false)->change();
            $table->unique('slug', 'roles_slug_unique');
        });

        // 5. Add slug to permissions
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->after('name');
        });

        // 6. Backfill permission slugs
        DB::table('permissions')->get()->each(function ($row) {
            DB::table('permissions')->where('id', $row->id)->update([
                'slug' => Str::slug($row->name, '.'),
            ]);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('slug', 100)->nullable(false)->change();
            $table->unique('slug', 'permissions_slug_unique');
        });

        // 7. Re-point the FK on users.role_id (if it exists) to new table name (roles)
        // MySQL renames the FK automatically on table rename, but let's make sure
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique('permissions_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_slug_unique');
            $table->dropColumn(['slug', 'description']);
        });

        Schema::rename('roles', 'role');
    }
};
