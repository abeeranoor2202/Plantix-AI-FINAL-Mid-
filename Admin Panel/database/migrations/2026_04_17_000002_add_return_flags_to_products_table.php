<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_returnable')) {
                $table->boolean('is_returnable')->default(true)->after('is_featured');
            }

            if (! Schema::hasColumn('products', 'is_refundable')) {
                $table->boolean('is_refundable')->default(true)->after('is_returnable');
            }

            if (! Schema::hasColumn('products', 'return_window_days')) {
                $table->unsignedSmallInteger('return_window_days')->default(7)->after('is_refundable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'return_window_days')) {
                $table->dropColumn('return_window_days');
            }

            if (Schema::hasColumn('products', 'is_refundable')) {
                $table->dropColumn('is_refundable');
            }

            if (Schema::hasColumn('products', 'is_returnable')) {
                $table->dropColumn('is_returnable');
            }
        });
    }
};
