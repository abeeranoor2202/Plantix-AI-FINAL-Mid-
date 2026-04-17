<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_disputes')) {
            Schema::create('order_disputes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->enum('status', ['pending', 'vendor_responded', 'escalated', 'resolved', 'rejected', 'refunded', 'cancelled'])->default('pending');
                $table->text('reason');
                $table->text('escalation_reason')->nullable();
                $table->text('vendor_response')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('escalated_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamp('refund_escalated_at')->nullable();
                $table->string('refund_reference', 120)->nullable();
                $table->timestamps();

                $table->unique('order_id');
                $table->index('status');
                $table->index('escalated_at');
                $table->index('resolved_at');
            });

            return;
        }

        Schema::table('order_disputes', function (Blueprint $table) {
            if (! Schema::hasColumn('order_disputes', 'escalation_reason')) {
                $table->text('escalation_reason')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('order_disputes', 'refund_escalated_at')) {
                $table->timestamp('refund_escalated_at')->nullable()->after('escalated_at');
            }

            if (! Schema::hasColumn('order_disputes', 'refund_reference')) {
                $table->string('refund_reference', 120)->nullable()->after('refund_escalated_at');
            }

            $table->index('status');
            $table->index('escalated_at');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_disputes')) {
            return;
        }

        Schema::table('order_disputes', function (Blueprint $table) {
            try {
                $table->dropIndex(['status']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex(['escalated_at']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex(['resolved_at']);
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('order_disputes', 'refund_reference')) {
                $table->dropColumn('refund_reference');
            }

            if (Schema::hasColumn('order_disputes', 'refund_escalated_at')) {
                $table->dropColumn('refund_escalated_at');
            }

            if (Schema::hasColumn('order_disputes', 'escalation_reason')) {
                $table->dropColumn('escalation_reason');
            }
        });
    }
};
