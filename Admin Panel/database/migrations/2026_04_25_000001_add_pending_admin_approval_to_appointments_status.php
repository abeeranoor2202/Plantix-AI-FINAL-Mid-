<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add 'pending_admin_approval' to appointments.status ENUM.
 *
 * New flow:
 *   pending_payment → pending_admin_approval (payment received, awaiting admin review)
 *                   → pending_expert_approval (admin approved, forwarded to expert)
 *
 * This inserts the admin approval gate between payment and expert notification.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `appointments`
            MODIFY COLUMN `status` ENUM(
                'draft',
                'pending_payment',
                'payment_failed',
                'pending_admin_approval',
                'pending_expert_approval',
                'confirmed',
                'rejected',
                'completed',
                'cancelled',
                'reschedule_requested',
                'rescheduled',
                'pending'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Revert rows first to avoid ENUM truncation errors
        DB::table('appointments')
            ->where('status', 'pending_admin_approval')
            ->update(['status' => 'pending_payment']);

        DB::statement("
            ALTER TABLE `appointments`
            MODIFY COLUMN `status` ENUM(
                'draft',
                'pending_payment',
                'payment_failed',
                'pending_expert_approval',
                'confirmed',
                'rejected',
                'completed',
                'cancelled',
                'reschedule_requested',
                'rescheduled',
                'pending'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
