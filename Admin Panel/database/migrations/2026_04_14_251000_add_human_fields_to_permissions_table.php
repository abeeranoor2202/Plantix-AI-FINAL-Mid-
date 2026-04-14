<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'module')) {
                $table->string('module', 100)->nullable()->after('name');
            }

            if (! Schema::hasColumn('permissions', 'action')) {
                $table->string('action', 100)->nullable()->after('module');
            }

            if (! Schema::hasColumn('permissions', 'description')) {
                $table->text('description')->nullable()->after('display_name');
            }

            if (! Schema::hasColumn('permissions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        DB::table('permissions')->orderBy('id')->chunkById(200, function ($permissions): void {
            foreach ($permissions as $permission) {
                $name = (string) $permission->name;
                $parts = explode('.', $name);

                $module = $permission->module;
                if (! $module) {
                    $module = count($parts) > 2 ? $parts[1] : ($parts[0] ?? $name);
                }

                $action = $permission->action;
                if (! $action) {
                    $action = count($parts) > 1 ? $parts[count($parts) - 1] : 'manage';
                }

                DB::table('permissions')->where('id', $permission->id)->update([
                    'module' => $module,
                    'action' => $action,
                    'description' => $permission->description ?: ($permission->display_name ?: str_replace('.', ' ', $name)),
                    'is_active' => $permission->is_active ?? true,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('permissions', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('permissions', 'action')) {
                $table->dropColumn('action');
            }

            if (Schema::hasColumn('permissions', 'module')) {
                $table->dropColumn('module');
            }
        });
    }
};