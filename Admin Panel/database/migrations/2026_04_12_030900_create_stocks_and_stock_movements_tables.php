<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->enum('status', ['in_stock', 'out_of_stock'])->default('in_stock');
            $table->timestamps();

            $table->unique(['product_id', 'vendor_id']);
            $table->index(['vendor_id', 'status']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'reserved', 'released']);
            $table->integer('quantity')->default(0);
            $table->string('reference', 191)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['vendor_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
    }
};
