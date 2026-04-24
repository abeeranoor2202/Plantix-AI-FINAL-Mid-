<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expand the appointments.payment_status ENUM to include all values
 * used by the application:
 *   - 'unpaid'    original default
 *   - 'pending'   set during initiateBooking (awaiting Stripe confirmation)
 *   - 'paid'      set after payment_intent.succeeded webhook
 *   - 'failed'    set after payment_intent.payment_failed webhook
 *   - 'refunded'  set after a refund is issued
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `appointments`
            MODIFY COLUMN `payment_status`
                ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded')
                NOT NULL DEFAULT 'unpaid'
        ");
    }

    public function down(): void
    {
        // Revert to original 3-value ENUM.
        // WARNING: any rows with 'pending' or 'failed' will be truncated back to ''.
        DB::statement("
            ALTER TABLE `appointments`
            MODIFY COLUMN `payment_status`
                ENUM('unpaid', 'paid', 'refunded')
                NOT NULL DEFAULT 'unpaid'
        ");
    }
};
