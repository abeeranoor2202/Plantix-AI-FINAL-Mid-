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
 *   - A payments row is created for paid/refunded statuses
 *   - Reschedule statuses get an appointment_reschedules row
 *
 * Safe to run multiple times — truncates appointment data first.
 */
class FullAppointmentSeeder extends Seeder
{
    // ── Configuration ─────────────────────────────────────────────────────────

    /** All statuses to seed, in logical lifecycle order */
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

    private const SLOT_START = '09:00';
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

    // ── State ─────────────────────────────────────────────────────────────────

    private Carbon $now;
    private int $adminId;
    private array $customers = [];
    private array $experts   = [];

    // ── Entry point ───────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->now = Carbon::now();

        // ── Load actors ───────────────────────────────────────────────────────
        $this->adminId = (int) (
            DB::table('users')->where('email', 'admin@gmail.com')->value('id')
            ?? DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
            ?? 1
        );

        $this->customers = DB::table('users')
            ->where('role', 'user')
            ->pluck('id')
            ->toArray();

        $this->experts = DB::table('experts')
            ->where('status', 'approved')
            ->get()
            ->toArray();

        if (empty($this->experts)) {
            $this->command->warn('FullAppointmentSeeder: no approved experts found — skipping.');
            return;
        }

        if (empty($this->customers)) {
            $this->command->warn('FullAppointmentSeeder: no customer users found — skipping.');
            return;
        }

        // ── Truncate existing appointment data ────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('appointment_reschedules')->truncate();
        DB::table('appointment_status_histories')->truncate();
        DB::table('appointment_logs')->truncate();
        DB::table('appointment_slots')->truncate();
        // Remove appointment-related payments only
        DB::table('payments')->whereNotNull('appointment_id')->delete();
        DB::table('appointments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Seed ──────────────────────────────────────────────────────────────
        $totalAppointments = 0;
        $totalSlots        = 0;

        foreach ($this->experts as $expertIdx => $expert) {
            foreach ($this->customers as $customerIdx => $customerId) {
                foreach (self::STATUSES as $statusIdx => $status) {
                    // Spread appointments across different days to avoid slot conflicts
                    // Each expert+customer+status combo gets a unique day offset
                    $dayOffset = ($expertIdx * count($this->customers) * count(self::STATUSES))
                        + ($customerIdx * count(self::STATUSES))
                        + $statusIdx;

                    [$appointmentId, $slotCreated] = $this->seedOne(
                        $expert,
                        $customerId,
                        $status,
                        $statusIdx,
                        $dayOffset
                    );

                    $totalAppointments++;
                    if ($slotCreated) {
                        $totalSlots++;
                    }
                }
            }
        }

        $this->command->info(sprintf(
            'FullAppointmentSeeder: seeded %d appointments (%d slots) across %d expert(s) × %d customer(s) × %d statuses.',
            $totalAppointments,
            $totalSlots,
            count($this->experts),
            count($this->customers),
            count(self::STATUSES)
        ));
    }

    // ── Per-appointment logic ─────────────────────────────────────────────────

    /**
     * Seed one appointment with the given status and return [appointmentId, slotCreated].
     */
    private function seedOne(
        object $expert,
        int    $customerId,
        string $status,
        int    $statusIdx,
        int    $dayOffset
    ): array {
        $now = $this->now;

        // ── Timing ────────────────────────────────────────────────────────────
        // Past statuses use past dates; future-facing statuses use future dates.
        $isPastStatus = in_array($status, [
            'draft', 'payment_failed', 'pending_payment',
            'completed', 'rejected', 'cancelled', 'payment_failed',
        ], true);

        // Use a unique hour slot per statusIdx to avoid same-day conflicts
        $hourSlot  = 9 + ($statusIdx % 8); // 09:00 – 16:00
        $baseDate  = $isPastStatus
            ? $now->copy()->subDays(30 + $dayOffset)->setTime($hourSlot, 0, 0)
            : $now->copy()->addDays(7  + $dayOffset)->setTime($hourSlot, 0, 0);

        // Skip Sundays
        while ($baseDate->dayOfWeek === Carbon::SUNDAY) {
            $baseDate->addDay();
        }

        $scheduledAt   = $baseDate->copy();
        $scheduledDate = $scheduledAt->toDateString();
        $startTime     = $scheduledAt->format('H:i:s');
        $endTime       = $scheduledAt->copy()->addMinutes(self::SLOT_DURATION_MINUTES)->format('H:i:s');
        $duration      = (int) ($expert->consultation_duration_minutes ?? self::SLOT_DURATION_MINUTES);
        $fee           = (float) ($expert->consultation_price ?? $expert->consultation_fee ?? $expert->hourly_rate ?? 2000);
        $topic         = self::TOPICS[$statusIdx % count(self::TOPICS)];
        $notes         = self::NOTES[$statusIdx % count(self::NOTES)];
        $createdAt     = $scheduledAt->copy()->subDays(rand(1, 5));

        // ── Payment fields ────────────────────────────────────────────────────
        [$paymentStatus, $stripeStatus, $stripePI, $isRefunded, $refundedAt, $refundAmount] =
            $this->paymentFields($status, $fee, $scheduledAt);

        // ── Lifecycle timestamps ───────────────────────────────────────────────
        $acceptedAt   = in_array($status, ['confirmed', 'completed', 'rescheduled', 'reschedule_requested'])
            ? $scheduledAt->copy()->subHours(rand(2, 48)) : null;
        $rejectedAt   = $status === 'rejected'
            ? $scheduledAt->copy()->subHours(rand(1, 24)) : null;
        $cancelledAt  = $status === 'cancelled'
            ? $scheduledAt->copy()->subHours(rand(1, 12)) : null;
        $completedAt  = $status === 'completed'
            ? $scheduledAt->copy()->addMinutes($duration) : null;
        $rescheduleAt = in_array($status, ['reschedule_requested', 'rescheduled'])
            ? $scheduledAt->copy()->subHours(rand(1, 6)) : null;

        // ── Review (completed only) ───────────────────────────────────────────
        $rating = $status === 'completed' ? rand(3, 5) : null;
        $review = $status === 'completed'
            ? ['Excellent consultation, very helpful!', 'Good advice, will follow up.', 'Highly recommended expert.', 'Detailed and practical guidance.'][$statusIdx % 4]
            : null;

        // ── Meeting link (confirmed / completed / rescheduled) ────────────────
        $meetingLink = in_array($status, ['confirmed', 'completed', 'rescheduled'])
            ? 'https://meet.google.com/plantix-' . substr(md5($expert->id . $customerId . $status), 0, 10)
            : null;

        // ── Insert appointment ────────────────────────────────────────────────
        $appointmentId = DB::table('appointments')->insertGetId([
            'user_id'                  => $customerId,
            'expert_id'                => $expert->id,
            'admin_id'                 => in_array($status, ['confirmed', 'completed', 'cancelled']) ? $this->adminId : null,
            'type'                     => ($statusIdx % 2 === 0) ? 'online' : 'physical',
            'scheduled_at'             => $scheduledAt,
            'scheduled_date'           => $scheduledDate,
            'start_time'               => $startTime,
            'end_time'                 => $endTime,
            'duration_minutes'         => $duration,
            'status'                   => $status,
            'notes'                    => $notes,
            'topic'                    => $topic,
            'admin_notes'              => in_array($status, ['confirmed', 'completed'])
                ? 'Reviewed and confirmed by admin.' : null,
            'expert_response_notes'    => in_array($status, ['rejected', 'cancelled'])
                ? 'Expert unavailable on this date.' : null,
            'fee'                      => $fee,
            'payment_status'           => $paymentStatus,
            'stripe_payment_intent_id' => $stripePI,
            'stripe_payment_status'    => $stripeStatus,
            'is_refunded'              => $isRefunded ? 1 : 0,
            'refunded_at'              => $refundedAt,
            'refund_amount'            => $refundAmount,
            'meeting_link'             => $meetingLink,
            'location'                 => ($statusIdx % 2 !== 0) ? 'Farm Road, ' . ($expert->city ?? 'Lahore') : null,
            'accepted_at'              => $acceptedAt,
            'rejected_at'              => $rejectedAt,
            'cancelled_at'             => $cancelledAt,
            'completed_at'             => $completedAt,
            'reschedule_requested_at'  => $rescheduleAt,
            'reject_reason'            => $status === 'rejected' ? 'Expert unavailable on requested date.' : null,
            'cancellation_reason'      => $status === 'cancelled' ? 'Customer cancelled due to schedule conflict.' : null,
            'customer_rating'          => $rating,
            'customer_review'          => $review,
            'rated_at'                 => $rating ? $scheduledAt->copy()->addDays(1) : null,
            'reminder_sent_at'         => in_array($status, ['confirmed', 'rescheduled'])
                ? $scheduledAt->copy()->subHours(24) : null,
            'notifications_enabled'    => 1,
            'created_at'               => $createdAt,
            'updated_at'               => $now,
        ]);

        // ── Slot ──────────────────────────────────────────────────────────────
        $slotBooked = in_array($status, [
            'pending_expert_approval', 'confirmed', 'completed',
            'reschedule_requested', 'rescheduled', 'pending',
        ], true);

        $slotId = DB::table('appointment_slots')->insertGetId([
            'expert_id'      => $expert->id,
            'date'           => $scheduledDate,
            'start_time'     => $startTime,
            'end_time'       => $endTime,
            'is_booked'      => $slotBooked ? 1 : 0,
            'appointment_id' => $slotBooked ? $appointmentId : null,
            'created_at'     => $createdAt,
            'updated_at'     => $now,
        ]);

        // ── Status history ────────────────────────────────────────────────────
        $this->seedStatusHistory($appointmentId, $customerId, $status, $createdAt, $scheduledAt);

        // ── Audit log ─────────────────────────────────────────────────────────
        $this->seedAuditLog($appointmentId, $customerId, $status, $createdAt);

        // ── Payment record ────────────────────────────────────────────────────
        if ($stripePI) {
            $this->seedPayment($appointmentId, $customerId, $expert, $fee, $paymentStatus, $stripePI, $createdAt);
        }

        // ── Reschedule record ─────────────────────────────────────────────────
        if (in_array($status, ['reschedule_requested', 'rescheduled'], true)) {
            $this->seedReschedule($appointmentId, $customerId, $status, $scheduledAt);
        }

        return [$appointmentId, true];
    }

    // ── Payment fields helper ─────────────────────────────────────────────────

    private function paymentFields(string $status, float $fee, Carbon $scheduledAt): array
    {
        $paymentStatus = match ($status) {
            'completed', 'confirmed', 'rescheduled', 'reschedule_requested', 'pending_expert_approval'
                => 'paid',
            'cancelled'
                => 'refunded',
            'rejected'
                => 'refunded',
            'payment_failed'
                => 'failed',
            'pending_payment'
                => 'pending',
            default => 'unpaid',
        };

        $stripePI     = null;
        $stripeStatus = null;
        $isRefunded   = false;
        $refundedAt   = null;
        $refundAmount = null;

        if (in_array($paymentStatus, ['paid', 'refunded', 'failed'], true)) {
            $stripePI = 'pi_seed_' . strtoupper(substr(md5(uniqid('', true)), 0, 20));
        }

        if ($paymentStatus === 'paid') {
            $stripeStatus = 'succeeded';
        } elseif ($paymentStatus === 'failed') {
            $stripeStatus = 'payment_failed';
        } elseif ($paymentStatus === 'refunded') {
            $stripeStatus  = 'succeeded';
            $isRefunded    = true;
            $refundedAt    = $scheduledAt->copy()->addDays(1);
            $refundAmount  = $fee;
        }

        return [$paymentStatus, $stripeStatus, $stripePI, $isRefunded, $refundedAt, $refundAmount];
    }

    // ── Status history ────────────────────────────────────────────────────────

    private function seedStatusHistory(
        int    $appointmentId,
        int    $customerId,
        string $finalStatus,
        Carbon $createdAt,
        Carbon $scheduledAt
    ): void {
        // Build a realistic trail of transitions leading to the final status
        $trail = $this->buildStatusTrail($finalStatus);

        $ts = $createdAt->copy();
        foreach ($trail as $i => [$from, $to]) {
            DB::table('appointment_status_histories')->insert([
                'appointment_id' => $appointmentId,
                'changed_by'     => $customerId,
                'from_status'    => $from,
                'to_status'      => $to,
                'notes'          => $this->historyNote($to),
                'changed_at'     => $ts->copy()->addMinutes($i * 30),
                'created_at'     => $ts->copy()->addMinutes($i * 30),
                'updated_at'     => $ts->copy()->addMinutes($i * 30),
            ]);
        }
    }

    /**
     * Build a list of [from, to] transitions that lead to $finalStatus.
     */
    private function buildStatusTrail(string $finalStatus): array
    {
        return match ($finalStatus) {
            'draft'
                => [['draft', 'draft']],
            'pending_payment'
                => [['draft', 'pending_payment']],
            'payment_failed'
                => [['draft', 'pending_payment'], ['pending_payment', 'payment_failed']],
            'pending_expert_approval'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval']],
            'confirmed'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed']],
            'rejected'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'rejected']],
            'completed'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'completed']],
            'cancelled'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'cancelled']],
            'reschedule_requested'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'reschedule_requested']],
            'rescheduled'
                => [['draft', 'pending_payment'], ['pending_payment', 'pending_expert_approval'], ['pending_expert_approval', 'confirmed'], ['confirmed', 'reschedule_requested'], ['reschedule_requested', 'rescheduled']],
            'pending'
                => [['pending', 'pending']],
            default
                => [['draft', $finalStatus]],
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

    // ── Audit log ─────────────────────────────────────────────────────────────

    private function seedAuditLog(
        int    $appointmentId,
        int    $customerId,
        string $status,
        Carbon $createdAt
    ): void {
        $action = match ($status) {
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

        DB::table('appointment_logs')->insert([
            'appointment_id' => $appointmentId,
            'user_id'        => $customerId,
            'action'         => $action,
            'from_status'    => 'draft',
            'to_status'      => $status,
            'notes'          => "Seeded via FullAppointmentSeeder — status: {$status}",
            'context'        => json_encode(['seeder' => 'FullAppointmentSeeder']),
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'Seeder/1.0',
            'occurred_at'    => $createdAt,
            'created_at'     => $createdAt,
        ]);
    }

    // ── Payment record ────────────────────────────────────────────────────────

    private function seedPayment(
        int    $appointmentId,
        int    $customerId,
        object $expert,
        float  $fee,
        string $paymentStatus,
        string $stripePI,
        Carbon $createdAt
    ): void {
        $dbStatus = match ($paymentStatus) {
            'paid'     => 'completed',
            'refunded' => 'refunded',
            'failed'   => 'failed',
            default    => 'pending',
        };

        DB::table('payments')->insert([
            'appointment_id'           => $appointmentId,
            'order_id'                 => null,
            'user_id'                  => $customerId,
            'gateway'                  => 'stripe',
            'gateway_transaction_id'   => $stripePI,
            'stripe_payment_intent_id' => $stripePI,
            'stripe_session_id'        => 'cs_seed_' . substr(md5($stripePI), 0, 20),
            'payment_type'             => 'appointment',
            'amount'                   => $fee,
            'currency'                 => strtolower(config('plantix.currency_code', 'pkr')),
            'status'                   => $dbStatus,
            'paid_at'                  => $dbStatus === 'completed' ? $createdAt->copy()->addMinutes(5) : null,
            'metadata'                 => json_encode([
                'appointment_id' => $appointmentId,
                'expert_id'      => $expert->id,
                'seeder'         => 'FullAppointmentSeeder',
            ]),
            'created_at'               => $createdAt,
            'updated_at'               => $createdAt,
        ]);
    }

    // ── Reschedule record ─────────────────────────────────────────────────────

    private function seedReschedule(
        int    $appointmentId,
        int    $customerId,
        string $status,
        Carbon $scheduledAt
    ): void {
        $proposedAt = $scheduledAt->copy()->addDays(3)->setTime(10, 0, 0);
        while ($proposedAt->dayOfWeek === Carbon::SUNDAY) {
            $proposedAt->addDay();
        }

        $rescheduleStatus = $status === 'rescheduled' ? 'accepted' : 'pending';
        $respondedAt      = $status === 'rescheduled' ? $scheduledAt->copy()->addHours(2) : null;

        DB::table('appointment_reschedules')->insert([
            'appointment_id'        => $appointmentId,
            'requested_by'          => $customerId,
            'original_scheduled_at' => $scheduledAt,
            'proposed_scheduled_at' => $proposedAt,
            'reason'                => 'Expert requested a more convenient time slot.',
            'status'                => $rescheduleStatus,
            'responded_at'          => $respondedAt,
            'created_at'            => $scheduledAt->copy()->subHours(4),
            'updated_at'            => $respondedAt ?? $scheduledAt->copy()->subHours(4),
        ]);
    }
}
