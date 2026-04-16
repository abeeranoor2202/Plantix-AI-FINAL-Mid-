<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropUnique('products_sku_unique');
            } catch (\Throwable) {
                // Index may already be absent in some environments.
            }

            try {
                $table->dropIndex('products_sku_index');
            } catch (\Throwable) {
                // Index may be absent or named differently.
            }

            $table->unique(['vendor_id', 'sku'], 'products_vendor_id_sku_unique');
            $table->index('sku', 'products_sku_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropUnique('products_vendor_id_sku_unique');
            } catch (\Throwable) {
                // Index may already be absent.
            }

            try {
                $table->dropIndex('products_sku_index');
            } catch (\Throwable) {
                // Index may already be absent.
            }

            $table->unique('sku', 'products_sku_unique');
            $table->index('sku', 'products_sku_index');
        });
    }
};
