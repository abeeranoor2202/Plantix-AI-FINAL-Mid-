<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FullAppointmentSeeder
 *
 * Seeds one appointment for every possible status in the state machine,
 * for every approved expert × every customer user combination.
 *
 * Statuses covered (all 11 defined in Appointment model):
 *   draft | pending_payment | payment_failed | pending_expert_approval |
 *   confirmed | rejected | completed | cancelled |
 *   reschedule_requested | rescheduled | pending
 *
 * For each appointment:
 *   - A matching appointment_slot is created and linked (booked where appropriate)
 *   - A full appointment_status_histories trail is written
 *   - An appointment_logs audit entry is written
 *   - A payments row is created for paid/refunded/failed statuses
 *   - Reschedule statuses get an appointment_reschedules row
 *
 * Uses bulk inserts for performance. Safe to re-run — truncates first.
 */
class FullAppointmentSeeder extends Seeder
{
    // ── Configuration ─────────────────────────────────────────────────────────

    private const STATUSES = [
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
        'pending',
    ];

    private const SLOT_DURATION_MINUTES = 60;

    private const TOPICS = [
        'Soil fertility analysis and fertiliser prescription',
        'Wheat disease identification and spray program',
        'Drip irrigation design for vegetable farm',
        'Organic certification process guidance',
        'Mango orchard spray calendar',
        'Cotton bollworm integrated management',
        'Saline soil reclamation plan',
        'Tomato crop nutrition program',
        'Water management for rice in Punjab',
        'Potato late blight control',
        'Sugarcane planting guide',
        'Onion storage and post-harvest losses',
        'Weed management in wheat',
        'Farm profitability analysis',
        'Basmati 1121 best practices',
    ];

    private const NOTES = [
        'My crop has been showing yellow leaves for two weeks.',
        'Need advice on reducing input costs while maintaining yield.',
        'Planning to switch from conventional to organic — need a roadmap.',
        'Irrigation system is inefficient; water is wasted.',
        'Pest pressure is very high this season.',
        'Soil test results attached — please review.',
        'First-time farmer, need complete guidance.',
        'Looking for export-quality production advice.',
    ];

    // ── Entry point ───────────────────────────────────────────────────────────

    public function run(): void
    {
        $now = Carbon::now();

        $adminId = (int) (
            DB::table('users')->where('email', 'admin@gmail.com')->value('id')
            ?? DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
            ?? 1
        );

        $customers = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $experts   = DB::table('experts')->where('status', 'approved')->get()->toArray();

        if (empty($experts)) {
            $this->command->warn('FullAppointmentSeeder: no approved experts found — skipping.');
            return;
        }
        if (empty($customers)) {
            $this->command->warn('FullAppointmentSeeder: no customer users found — skipping.');
            return;
        }

        // ── Truncate ──────────────────────────────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['appointment_reschedules', 'appointment_status_histories', 'appointment_logs', 'appointment_slots'] as $t) {
            DB::table($t)->truncate();
        }
        DB::table('payments')->whereNotNull('appointment_id')->delete();
        DB::table('appointments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Collect all rows ──────────────────────────────────────────────────
        $appointments = [];
        $slots        = [];
        $histories    = [];
        $logs         = [];
        $payments     = [];
        $reschedules  = [];

        $statusCount  = count(self::STATUSES);
        $customerCount = count($customers);

        foreach ($experts as $expertIdx => $expert) {
            $fee      = (float) ($expert->consultation_price ?? $expert->consultation_fee ?? $expert->hourly_rate ?? 2000);
            $duration = (int) ($expert->consultation_duration_minutes ?? self::SLOT_DURATION_MINUTES);

            foreach ($customers as $customerIdx => $customerId) {
                foreach (self::STATUSES as $statusIdx => $status) {

                    // ── Unique day per combination ────────────────────────────
                    // Each expert gets its own 1000-day block; within that,
                    // each customer gets an 11-day block (one day per status).
                    $dayOffset = ($expertIdx * $customerCount * $statusCount)
                        + ($customerIdx * $statusCount)
                        + $statusIdx;

                    $hourSlot = 9 + ($statusIdx % 8); // 09:00–16:00

                    $isPast = in_array($status, [
                        'draft', 'pending_payment', 'payment_failed',
                        'completed', 'rejected', 'cancelled',
                    ], true);

                    $baseDate = $isPast
                        ? $now->copy()->subDays(60 + $dayOffset)->setTime($hourSlot, 0, 0)
                        : $now->copy()->addDays(7  + $dayOffset)->setTime($hourSlot, 0, 0);

                    // Skip Sundays
                    while ($baseDate->dayOfWeek === Carbon::SUNDAY) {
                        $baseDate->addDay();
                    }

                    $scheduledAt   = $baseDate->copy();
                    $scheduledDate = $scheduledAt->toDateString();
                    $startTime     = $scheduledAt->format('H:i:s');
                    $endTime       = $scheduledAt->copy()->addMinutes($duration)->format('H:i:s');
                    $createdAt     = $scheduledAt->copy()->subDays(3);
                    $topic         = self::TOPICS[$statusIdx % count(self::TOPICS)];
                    $notes         = self::NOTES[$statusIdx % count(self::NOTES)];
                    $type          = ($statusIdx % 2 === 0) ? 'online' : 'physical';

                    // ── Payment ───────────────────────────────────────────────
                    $paymentStatus = match ($status) {
                        'completed', 'confirmed', 'rescheduled',
                        'reschedule_requested', 'pending_expert_approval' => 'paid',
                        'cancelled', 'rejected'                           => 'refunded',
                        'payment_failed'                                  => 'failed',
                        'pending_payment'                                 => 'pending',
                        default                                           => 'unpaid',
                    };

                    $needsStripe  = in_array($paymentStatus, ['paid', 'refunded', 'failed'], true);
                    $stripePI     = $needsStripe
                        ? 'pi_seed_' . strtoupper(substr(md5($expertIdx . $customerIdx . $statusIdx . $status), 0, 20))
                        : null;
                    $stripeStatus = match ($paymentStatus) {
                        'paid'     => 'succeeded',
                        'failed'   => 'payment_failed',
                        'refunded' => 'succeeded',
                        default    => null,
                    };
                    $isRefunded   = $paymentStatus === 'refunded';
                    $refundedAt   = $isRefunded ? $scheduledAt->copy()->addDays(1) : null;
                    $refundAmount = $isRefunded ? $fee : null;

                    // ── Lifecycle timestamps ──────────────────────────────────
                    $acceptedAt  = in_array($status, ['confirmed', 'completed', 'rescheduled', 'reschedule_requested'])
                        ? $scheduledAt->copy()->subHours(6) : null;
                    $rejectedAt  = $status === 'rejected'  ? $scheduledAt->copy()->subHours(3) : null;
                    $cancelledAt = $status === 'cancelled' ? $scheduledAt->copy()->subHours(2) : null;
                    $completedAt = $status === 'completed' ? $scheduledAt->copy()->addMinutes($duration) : null;
                    $reschedAt   = in_array($status, ['reschedule_requested', 'rescheduled'])
                        ? $scheduledAt->copy()->subHours(1) : null;

                    $rating = $status === 'completed' ? (($statusIdx % 3) + 3) : null; // 3,4,5
                    $review = $status === 'completed'
                        ? ['Excellent consultation!', 'Very helpful advice.', 'Highly recommended.', 'Detailed guidance.'][$statusIdx % 4]
                        : null;

                    $meetingLink = in_array($status, ['confirmed', 'completed', 'rescheduled'])
                        ? 'https://meet.google.com/plantix-' . substr(md5($expertIdx . $customerIdx . $status), 0, 10)
                        : null;

                    // ── Appointment row ───────────────────────────────────────
                    $appointments[] = [
                        'user_id'                  => $customerId,
                        'expert_id'                => $expert->id,
                        'admin_id'                 => in_array($status, ['confirmed', 'completed', 'cancelled']) ? $adminId : null,
                        'type'                     => $type,
                        'scheduled_at'             => $scheduledAt->toDateTimeString(),
                        'scheduled_date'           => $scheduledDate,
                        'start_time'               => $startTime,
                        'end_time'                 => $endTime,
                        'duration_minutes'         => $duration,
                        'status'                   => $status,
                        'notes'                    => $notes,
                        'topic'                    => $topic,
                        'admin_notes'              => in_array($status, ['confirmed', 'completed']) ? 'Reviewed and confirmed.' : null,
                        'expert_response_notes'    => in_array($status, ['rejected', 'cancelled']) ? 'Expert unavailable.' : null,
                        'fee'                      => $fee,
                        'payment_status'           => $paymentStatus,
                        'stripe_payment_intent_id' => $stripePI,
                        'stripe_payment_status'    => $stripeStatus,
                        'is_refunded'              => $isRefunded ? 1 : 0,
                        'refunded_at'              => $refundedAt?->toDateTimeString(),
                        'refund_amount'            => $refundAmount,
                        'meeting_link'             => $meetingLink,
                        'location'                 => $type === 'physical' ? 'Farm Road, Lahore' : null,
                        'accepted_at'              => $acceptedAt?->toDateTimeString(),
                        'rejected_at'              => $rejectedAt?->toDateTimeString(),
                        'cancelled_at'             => $cancelledAt?->toDateTimeString(),
                        'completed_at'             => $completedAt?->toDateTimeString(),
                        'reschedule_requested_at'  => $reschedAt?->toDateTimeString(),
                        'reject_reason'            => $status === 'rejected' ? 'Expert unavailable on requested date.' : null,
                        'cancellation_reason'      => $status === 'cancelled' ? 'Customer cancelled due to schedule conflict.' : null,
                        'customer_rating'          => $rating,
                        'customer_review'          => $review,
                        'rated_at'                 => $rating ? $scheduledAt->copy()->addDays(1)->toDateTimeString() : null,
                        'reminder_sent_at'         => in_array($status, ['confirmed', 'rescheduled'])
                            ? $scheduledAt->copy()->subHours(24)->toDateTimeString() : null,
                        'notifications_enabled'    => 1,
                        'created_at'               => $createdAt->toDateTimeString(),
                        'updated_at'               => $now->toDateTimeString(),
                    ];
                }
            }
        }

        // ── Bulk insert appointments ──────────────────────────────────────────
        $this->command->info('Inserting ' . count($appointments) . ' appointments...');
        foreach (array_chunk($appointments, 200) as $chunk) {
            DB::table('appointments')->insert($chunk);
        }

        // ── Reload inserted IDs in order ──────────────────────────────────────
        // We need the auto-increment IDs to build related rows.
        // Fetch them ordered by created_at + expert_id + user_id + status to match insertion order.
        $insertedIds = DB::table('appointments')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $this->command->info('Building related rows for ' . count($insertedIds) . ' appointments...');

        // Rebuild the same loop to generate related rows using the real IDs
        $idx = 0;
        foreach ($experts as $expertIdx => $expert) {
            $fee      = (float) ($expert->consultation_price ?? $expert->consultation_fee ?? $expert->hourly_rate ?? 2000);
            $duration = (int) ($expert->consultation_duration_minutes ?? self::SLOT_DURATION_MINUTES);

            foreach ($customers as $customerIdx => $customerId) {
                foreach (self::STATUSES as $statusIdx => $status) {
                    $appointmentId = $insertedIds[$idx++];

                    $dayOffset = ($expertIdx * $customerCount * $statusCount)
                        + ($customerIdx * $statusCount)
                        + $statusIdx;
                    $hourSlot = 9 + ($statusIdx % 8);

                    $isPast = in_array($status, [
                        'draft', 'pending_payment', 'payment_failed',
                        'completed', 'rejected', 'cancelled',
                    ], true);

                    $baseDate = $isPast
                        ? $now->copy()->subDays(60 + $dayOffset)->setTime($hourSlot, 0, 0)
                        : $now->copy()->addDays(7  + $dayOffset)->setTime($hourSlot, 0, 0);

                    while ($baseDate->dayOfWeek === Carbon::SUNDAY) {
                        $baseDate->addDay();
                    }

                    $scheduledAt   = $baseDate->copy();
                    $scheduledDate = $scheduledAt->toDateString();
                    $startTime     = $scheduledAt->format('H:i:s');
                    $endTime       = $scheduledAt->copy()->addMinutes($duration)->format('H:i:s');
                    $createdAt     = $scheduledAt->copy()->subDays(3);

                    // ── Slot ──────────────────────────────────────────────────
                    $slotBooked = in_array($status, [
                        'pending_expert_approval', 'confirmed', 'completed',
                        'reschedule_requested', 'rescheduled', 'pending',
                    ], true);

                    $slots[] = [
                        'expert_id'      => $expert->id,
                        'date'           => $scheduledDate,
                        'start_time'     => $startTime,
                        'end_time'       => $endTime,
                        'is_booked'      => $slotBooked ? 1 : 0,
                        'appointment_id' => $slotBooked ? $appointmentId : null,
                        'created_at'     => $createdAt->toDateTimeString(),
                        'updated_at'     => $now->toDateTimeString(),
                    ];

                    // ── Status history ────────────────────────────────────────
                    $trail = $this->buildStatusTrail($status);
                    $ts    = $createdAt->copy();
                    foreach ($trail as $ti => [$from, $to]) {
                        $histories[] = [
                            'appointment_id' => $appointmentId,
                            'changed_by'     => $customerId,
                            'from_status'    => $from,
                            'to_status'      => $to,
                            'notes'          => $this->historyNote($to),
                            'changed_at'     => $ts->copy()->addMinutes($ti * 30)->toDateTimeString(),
                            'created_at'     => $ts->copy()->addMinutes($ti * 30)->toDateTimeString(),
                            'updated_at'     => $ts->copy()->addMinutes($ti * 30)->toDateTimeString(),
                        ];
                    }

                    // ── Audit log ─────────────────────────────────────────────
                    $logs[] = [
                        'appointment_id' => $appointmentId,
                        'user_id'        => $customerId,
                        'action'         => $this->auditAction($status),
                        'from_status'    => 'draft',
                        'to_status'      => $status,
                        'notes'          => "Seeded — status: {$status}",
                        'context'        => json_encode(['seeder' => 'FullAppointmentSeeder']),
                        'ip_address'     => '127.0.0.1',
                        'user_agent'     => 'Seeder/1.0',
                        'occurred_at'    => $createdAt->toDateTimeString(),
                        'created_at'     => $createdAt->toDateTimeString(),
                    ];

                    // ── Payment ───────────────────────────────────────────────
                    $paymentStatus = match ($status) {
                        'completed', 'confirmed', 'rescheduled',
                        'reschedule_requested', 'pending_expert_approval' => 'paid',
                        'cancelled', 'rejected'                           => 'refunded',
                        'payment_failed'                                  => 'failed',
                        'pending_payment'                                 => 'pending',
                        default                                           => 'unpaid',
                    };

                    if (in_array($paymentStatus, ['paid', 'refunded', 'failed'], true)) {
                        $stripePI = 'pi_seed_' . strtoupper(substr(md5($expertIdx . $customerIdx . $statusIdx . $status), 0, 20));
                        $dbStatus = match ($paymentStatus) {
                            'paid'     => 'completed',
                            'refunded' => 'refunded',
                            default    => 'failed',
                        };
                        $payments[] = [
                            'appointment_id'           => $appointmentId,
                            'order_id'                 => null,
                            'user_id'                  => $customerId,
                            'gateway'                  => 'stripe',
                            'gateway_transaction_id'   => $stripePI,
                            'stripe_payment_intent_id' => $stripePI,
                            'stripe_session_id'        => 'cs_seed_' . substr(md5($stripePI), 0, 20),
                            'payment_type'             => 'appointment',
                            'amount'                   => $fee,
                            'currency'                 => 'pkr',
                            'status'                   => $dbStatus,
                            'paid_at'                  => $dbStatus === 'completed' ? $createdAt->copy()->addMinutes(5)->toDateTimeString() : null,
                            'metadata'                 => json_encode(['appointment_id' => $appointmentId, 'seeder' => true]),
                            'created_at'               => $createdAt->toDateTimeString(),
                            'updated_at'               => $createdAt->toDateTimeString(),
                        ];
                    }

                    // ── Reschedule ────────────────────────────────────────────
                    if (in_array($status, ['reschedule_requested', 'rescheduled'], true)) {
                        $proposedAt = $scheduledAt->copy()->addDays(3)->setTime(10, 0, 0);
                        while ($proposedAt->dayOfWeek === Carbon::SUNDAY) {
                            $proposedAt->addDay();
                        }
                        $reschedules[] = [
                            'appointment_id'        => $appointmentId,
                            'requested_by'          => $customerId,
                            'original_scheduled_at' => $scheduledAt->toDateTimeString(),
                            'proposed_scheduled_at' => $proposedAt->toDateTimeString(),
                            'reason'                => 'Expert requested a more convenient time slot.',
                            'status'                => $status === 'rescheduled' ? 'accepted' : 'pending',
                            'responded_at'          => $status === 'rescheduled' ? $scheduledAt->copy()->addHours(2)->toDateTimeString() : null,
                            'created_at'            => $scheduledAt->copy()->subHours(4)->toDateTimeString(),
                            'updated_at'            => $now->toDateTimeString(),
                        ];
                    }
                }
            }
        }

        // ── Bulk insert related rows ──────────────────────────────────────────
        $this->command->info('Inserting slots, histories, logs, payments, reschedules...');

        foreach (array_chunk($slots, 500) as $chunk) {
            DB::table('appointment_slots')->insertOrIgnore($chunk);
        }
        foreach (array_chunk($histories, 500) as $chunk) {
            DB::table('appointment_status_histories')->insert($chunk);
        }
        foreach (array_chunk($logs, 500) as $chunk) {
            DB::table('appointment_logs')->insert($chunk);
        }
        foreach (array_chunk($payments, 500) as $chunk) {
            DB::table('payments')->insert($chunk);
        }
        foreach (array_chunk($reschedules, 500) as $chunk) {
            DB::table('appointment_reschedules')->insert($chunk);
        }

        $this->command->info(sprintf(
            'FullAppointmentSeeder done: %d appointments | %d slots | %d history rows | %d logs | %d payments | %d reschedules',
            count($appointments),
            count($slots),
            count($histories),
            count($logs),
            count($payments),
            count($reschedules)
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildStatusTrail(string $finalStatus): array
    {
        return match ($finalStatus) {
            'draft'                   => [['draft', 'draft']],
            'pending_payment'         => [['draft', 'pending_payment']],
            'payment_failed'          => [['draft', 'pending_payment'], ['pending_payment', 'payment_failed']],
            'pending_expert_approval' => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval']],
            'confirmed'               => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed']],
            'rejected'                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'rejected']],
            'completed'               => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'completed']],
            'cancelled'               => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'cancelled']],
            'reschedule_requested'    => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'reschedule_requested']],
            'rescheduled'             => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'reschedule_requested'], ['reschedule_requested', 'rescheduled']],
            'pending'                 => [['pending', 'pending']],
            default                   => [['draft', $finalStatus]],
        };
    }

    private function historyNote(string $toStatus): string
    {
        return match ($toStatus) {
            'pending_payment'         => 'Booking initiated. Awaiting payment.',
            'payment_failed'          => 'Stripe payment failed.',
            'pending_expert_approval' => 'Payment confirmed. Awaiting expert acceptance.',
            'confirmed'               => 'Expert accepted the appointment.',
            'rejected'                => 'Expert rejected the appointment.',
            'completed'               => 'Appointment session completed.',
            'cancelled'               => 'Appointment cancelled.',
            'reschedule_requested'    => 'Reschedule proposed by expert.',
            'rescheduled'             => 'Customer accepted the reschedule.',
            default                   => 'Status updated.',
        };
    }

    private function auditAction(string $status): string
    {
        return match ($status) {
            'draft'                   => 'draft_created',
            'pending_payment'         => 'payment_intent_created',
            'payment_failed'          => 'payment_failed',
            'pending_expert_approval' => 'payment_confirmed',
            'confirmed'               => 'confirmed',
            'rejected'                => 'rejected',
            'completed'               => 'completed',
            'cancelled'               => 'customer_cancelled',
            'reschedule_requested'    => 'reschedule_requested',
            'rescheduled'             => 'reschedule_accepted',
            'pending'                 => 'booking_created',
            default                   => 'status_updated',
        };
    }
}
