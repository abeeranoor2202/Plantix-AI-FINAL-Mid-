<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('net_amount', 12, 2);
            $table->enum('status', ['calculated', 'settled', 'void'])->default('calculated');
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamp('settled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
