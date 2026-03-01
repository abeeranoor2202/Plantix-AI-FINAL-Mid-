<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Patch: recreates `role`, `permissions`, and `role_permissions` tables
 * if they were accidentally dropped.  Safe to run multiple times.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role')) {
            Schema::create('role', function (Blueprint $table) {
                $table->id();
                $table->string('role_name')->unique();
                $table->string('guard', 50)->default('web');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('role', function (Blueprint $table) {
                if (! Schema::hasColumn('role', 'guard')) {
                    $table->string('guard', 50)->default('web')->after('role_name');
                }
                if (! Schema::hasColumn('role', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('guard');
                }
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('group');
                $table->string('display_name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('role')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role');
    }
};
