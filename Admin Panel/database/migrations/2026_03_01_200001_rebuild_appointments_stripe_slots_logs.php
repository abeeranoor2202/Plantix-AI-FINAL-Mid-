<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * CRITICAL MIGRATION — Appointment System Overhaul
 *
 * Fixes:
 *  1. Status ENUM was only 4 values; model + service used 8 (causing silent DB errors)
 *  2. Missing Stripe columns on appointments table
 *  3. Missing admin_id, cancellation_reason, expert_response_notes
 *  4. No appointment_slots table (double-booking impossible to prevent properly)
 *  5. No appointment_logs audit trail table
 *  6. Dropped duplicate appointment_status_history table (wrong name vs model reference)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Fix appointments.status ENUM ──────────────────────────────────
        // MySQL requires full ENUM redefinition; we use a raw ALTER.
        // New status set matches the strict state machine specification:
        //   draft → pending_payment → payment_failed
        //                          → pending_expert_approval → confirmed → completed
        //                                                   → rejected
        //                              confirmed → cancelled / reschedule_requested
        //                              reschedule_requested → confirmed
        //   ANY → cancelled (admin only)
        DB::statement("
            ALTER TABLE appointments
            MODIFY COLUMN status ENUM(
                'draft',
                'pending_payment',
                'payment_failed',
                'pending_expert_approval',
                'confirmed',
                'rejected',
                'completed',
                'cancelled',
                'reschedule_requested',
                'requested',
                'pending',
                'accepted',
                'rescheduled'
            ) NOT NULL DEFAULT 'draft'
        ");

        // ── 2. Add missing Stripe + admin columns to appointments ─────────────
        Schema::table('appointments', function (Blueprint $table) {
            // Admin who last touched the appointment
            if (! Schema::hasColumn('appointments', 'admin_id')) {
                $table->foreignId('admin_id')
                      ->nullable()
                      ->after('expert_id')
                      ->constrained('users')
                      ->nullOnDelete();
            }

            // Stripe PaymentIntent ID (set when PI is created; updated by webhook)
            if (! Schema::hasColumn('appointments', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id', 255)
                      ->nullable()
                      ->unique()
                      ->after('fee');
            }

            // Stripe's own status for the PI (mirrors payment_intent.status from webhook)
            if (! Schema::hasColumn('appointments', 'stripe_payment_status')) {
                $table->string('stripe_payment_status', 50)
                      ->nullable()
                      ->after('stripe_payment_intent_id');
            }

            // Refund tracking
            if (! Schema::hasColumn('appointments', 'is_refunded')) {
                $table->boolean('is_refunded')->default(false)->after('stripe_payment_status');
            }
            if (! Schema::hasColumn('appointments', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('is_refunded');
            }
            if (! Schema::hasColumn('appointments', 'stripe_refund_id')) {
                $table->string('stripe_refund_id', 255)->nullable()->after('refunded_at');
            }
            if (! Schema::hasColumn('appointments', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->nullable()->after('stripe_refund_id');
            }

            // Separate cancellation reason from reject_reason / admin_notes
            if (! Schema::hasColumn('appointments', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('reject_reason');
            }

            // Expert's response notes (shown to customer + admin)
            if (! Schema::hasColumn('appointments', 'expert_response_notes')) {
                $table->text('expert_response_notes')->nullable()->after('cancellation_reason');
            }

            // Slot-based scheduling: separate date + start/end instead of just scheduled_at
            if (! Schema::hasColumn('appointments', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('scheduled_at');
            }
            if (! Schema::hasColumn('appointments', 'start_time')) {
                $table->time('start_time')->nullable()->after('scheduled_date');
            }
            if (! Schema::hasColumn('appointments', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }

            // Reminder sent flag (prevent duplicate reminder emails)
            if (! Schema::hasColumn('appointments', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable();
            }

            // Idempotency key to prevent duplicate payment attempts
            if (! Schema::hasColumn('appointments', 'payment_idempotency_key')) {
                $table->string('payment_idempotency_key', 100)->nullable()->unique();
            }
        });

        // ── 3. Drop the wrongly-named duplicate history table ─────────────────
        // 2026_02_27_200001 created `appointment_status_history` (no trailing 's').
        // 2026_03_01_000002 created `appointment_status_histories` (correct name).
        // Model references `appointment_status_histories`.  Drop the orphan.
        if (Schema::hasTable('appointment_status_history')) {
            Schema::drop('appointment_status_history');
        }

        // ── 4. Create appointment_slots table ─────────────────────────────────
        // Experts register their available time slots here.
        // Bookings lock a slot via is_booked = true inside a DB transaction.
        if (! Schema::hasTable('appointment_slots')) {
            Schema::create('appointment_slots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expert_id')->constrained('experts')->cascadeOnDelete();
                $table->date('date');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_booked')->default(false);
                $table->foreignId('appointment_id')
                      ->nullable()
                      ->constrained('appointments')
                      ->nullOnDelete();
                $table->timestamps();

                // DB-level uniqueness: one slot per expert per (date + start_time)
                $table->unique(['expert_id', 'date', 'start_time'], 'uq_expert_slot');

                $table->index('expert_id');
                $table->index(['date', 'start_time']);
                $table->index('is_booked');
            });
        }

        // ── 5. Create appointment_logs (audit trail) ──────────────────────────
        // Immutable audit log.  Never deleted.  Separate from status history.
        if (! Schema::hasTable('appointment_logs')) {
            Schema::create('appointment_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 100);          // e.g. created, status_changed, refunded
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->json('context')->nullable();    // {ip, user_agent, extra_data}
                $table->text('notes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('occurred_at')->useCurrent();
                $table->timestamps();

                $table->index('appointment_id');
                $table->index('user_id');
                $table->index('action');
                $table->index('occurred_at');
            });
        }

        // ── 6. Add composite indexes for query performance ────────────────────
        // Prevents N+1 when filtering dashboard by expert + status + date
        try {
            DB::statement('ALTER TABLE appointments ADD INDEX idx_appt_expert_status (expert_id, status)');
        } catch (\Throwable) { /* index may already exist */ }

        try {
            DB::statement('ALTER TABLE appointments ADD INDEX idx_appt_user_status (user_id, status)');
        } catch (\Throwable) { /* index may already exist */ }
    }

    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('appointment_logs');
        Schema::dropIfExists('appointment_slots');

        // Remove added columns
        Schema::table('appointments', function (Blueprint $table) {
            $columnsToDrop = [
                'admin_id', 'stripe_payment_intent_id', 'stripe_payment_status',
                'is_refunded', 'refunded_at', 'stripe_refund_id', 'refund_amount',
                'cancellation_reason', 'expert_response_notes',
                'scheduled_date', 'start_time', 'end_time',
                'reminder_sent_at', 'payment_idempotency_key',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Revert status ENUM to original 4 values
        DB::statement("
            ALTER TABLE appointments
            MODIFY COLUMN status ENUM('pending','confirmed','completed','cancelled')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
