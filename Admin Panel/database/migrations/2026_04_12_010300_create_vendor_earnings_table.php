<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2)->default(0);
            $table->enum('settlement_status', ['pending', 'partial', 'settled', 'failed'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['vendor_id', 'settlement_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_earnings');
    }
};
