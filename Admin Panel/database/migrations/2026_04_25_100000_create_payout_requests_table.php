<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expert payout requests.
 *
 * Flow:
 *   Expert requests payout for a completed appointment
 *   → status: pending
 *   → Admin reviews and approves (triggers Stripe transfer) or rejects
 *   → status: approved | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('payout_id')->nullable()->constrained('payouts')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('expert_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['expert_id', 'status']);
            $table->index('appointment_id');
            // One request per appointment
            $table->unique('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
