<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('gateway', 50);                          // stripe, cod, wallet
            $table->string('gateway_transaction_id', 200)->nullable(); // Stripe PaymentIntent ID
            $table->string('gateway_refund_id', 200)->nullable();   // Stripe Refund ID
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('PKR');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])
                  ->default('pending');
            $table->json('gateway_response')->nullable();           // raw API response (PII-redacted)
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('gateway_transaction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
