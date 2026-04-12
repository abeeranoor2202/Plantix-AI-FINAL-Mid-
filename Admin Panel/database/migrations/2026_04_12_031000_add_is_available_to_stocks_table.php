<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        if (! Schema::hasColumn('stocks', 'is_available')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->boolean('is_available')->default(true)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stocks') && Schema::hasColumn('stocks', 'is_available')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('is_available');
            });
        }
    }
};