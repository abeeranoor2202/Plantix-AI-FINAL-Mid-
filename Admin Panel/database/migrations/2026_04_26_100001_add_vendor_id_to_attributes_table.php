<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add vendor_id to attributes table.
 *
 * NULL  → admin-created (global, visible to all, editable only by admin)
 * set   → vendor-created (visible to all, editable only by that vendor)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->foreignId('vendor_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('vendors')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
