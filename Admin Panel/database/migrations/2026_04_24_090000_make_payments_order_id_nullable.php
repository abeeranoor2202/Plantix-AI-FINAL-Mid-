<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The payments table was originally created with order_id as a non-nullable
 * foreign key. Appointment payments have no order_id, so inserts fail with
 * "Field 'order_id' doesn't have a default value".
 *
 * This migration:
 *  1. Drops the existing non-nullable FK constraint on order_id
 *  2. Alters the column to be nullable
 *  3. Re-adds the FK constraint (nullable FK is valid in MySQL)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key first (required before modifying the column)
        Schema::table('payments', function (Blueprint $table) {
            // Ignore if the FK doesn't exist (already dropped or never created)
            try {
                $table->dropForeign(['order_id']);
            } catch (\Throwable) {
                // FK may not exist — safe to continue
            }
        });

        // Make the column nullable
        DB::statement('ALTER TABLE `payments` MODIFY COLUMN `order_id` BIGINT UNSIGNED NULL DEFAULT NULL');

        // Re-add the FK as nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse: drop nullable FK, set back to NOT NULL, re-add strict FK
        // WARNING: will fail if any rows have order_id = NULL
        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->dropForeign(['order_id']);
            } catch (\Throwable) {}
        });

        DB::statement('ALTER TABLE `payments` MODIFY COLUMN `order_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->cascadeOnDelete();
        });
    }
};
