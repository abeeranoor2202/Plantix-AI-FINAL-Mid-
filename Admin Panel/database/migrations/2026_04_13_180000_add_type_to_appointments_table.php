<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'type')) {
                $table->enum('type', ['online', 'physical'])->default('online')->after('admin_id');
                $table->index('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'type')) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            }
        });
    }
};
