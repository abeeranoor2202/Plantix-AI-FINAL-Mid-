<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role', function (Blueprint $table) {
            if (! Schema::hasColumn('role', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('role_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('role', function (Blueprint $table) {
            if (Schema::hasColumn('role', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
