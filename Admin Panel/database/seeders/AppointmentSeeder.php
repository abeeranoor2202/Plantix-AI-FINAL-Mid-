<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AppointmentSeeder — 30 appointments across approved experts.
 *
 * Fixes applied vs old version:
 *  - Admin lookup now uses 'admin@gmail.com' (actual super-admin email from AdminSuperUserSeeder)
 *  - Status values restricted to valid ENUM values defined in migration
 *    2026_03_01_200001 (draft|pending_payment|payment_failed|pending_expert_approval|
 *    confirmed|rejected|completed|cancelled|reschedule_requested|requested|pending|
 *    accepted|rescheduled)
 *  - Inserts into appointment_status_histories (correct table; singular version
 *    appointment_status_history was DROPPED by migration 2026_03_01_200001)
 *  - Added stripe_payment_intent_id where payment is 'paid'
 *  - Added scheduled_date, start_time, end_time (columns added by rebuild migration)
 */
class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $now       = Carbon::now();
        $experts   = DB::table('experts')->where('status', 'approved')->get();
        $customers = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $adminId   = DB::table('users')->where('email', 'admin@gmail.com')->value('id')
            ?? DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
            ?? 1;

        if ($experts->isEmpty() || empty($customers)) {
            return;
        }

        $statusPlan = array_merge(
            array_fill(0, 12, 'completed'),
            array_fill(0,  8, 'confirmed'),
            array_fill(0,  5, 'pending'),
            array_fill(0,  3, 'cancelled'),
            array_fill(0,  2, 'rejected')
        );

        $topics = [
            'Soil fertility analysis and fertiliser prescription',
            'Wheat disease identification and spray program',
            'Drip irrigation design for my vegetable farm',
            'Organic certification process guidance',
            'Mango orchard spray calendar',
            'Cotton bollworm integrated management',
            'Saline soil reclamation plan',
            'Tomato crop nutrition program',
            'Water management for rice in Punjab',
            'Basmati 1121 best practices',
            'Potato late blight control',
            'Sugarcane planting guide for first-time growers',
            'Onion storage and post-harvest losses',
            'Weed management in wheat',
            'Farm profitability analysis',
        ];

        foreach ($statusPlan as $idx => $status) {
            $expert   = $experts[$idx % count($experts)];
            $userId   = $customers[$idx % count($customers)];

            // For completed/confirmed: past dates. For pending/cancelled/rejected: mix.
            $daysOffset = match ($status) {
                'completed'  => rand(10, 120),
                'confirmed'  => -rand(1, 30),  // future
                'pending'    => -rand(1, 20),  // future
                'cancelled'  => rand(5, 60),   // past
                'rejected'   => rand(3, 45),   // past
                default      => rand(1, 30),
            };
            $hourOffset = [0, 30][rand(0, 1)];
            $scheduled  = $now->copy()->subDays($daysOffset)->setTime(rand(9, 16), $hourOffset, 0);

            $fee       = $expert->consultation_price ?? 2000;
            $payStatus = match ($status) {
                'completed'  => 'paid',
                'confirmed'  => rand(0, 1) ? 'paid' : 'unpaid',
                'cancelled'  => rand(0, 1) ? 'refunded' : 'unpaid',
                'rejected'   => 'refunded',
                default      => 'unpaid',
            };

            $stripePaymentIntentId = null;
            if (in_array($payStatus, ['paid', 'refunded'])) {
                $stripePaymentIntentId = 'pi_3' . strtoupper(substr(md5(uniqid()), 0, 20));
            }

            $appointmentId = DB::table('appointments')->insertGetId([
                'user_id'                    => $userId,
                'expert_id'                  => $expert->id,
                'admin_id'                   => null,
                'scheduled_at'               => $scheduled,
                'scheduled_date'             => $scheduled->toDateString(),
                'start_time'                 => $scheduled->format('H:i:s'),
                'end_time'                   => $scheduled->copy()->addMinutes($expert->consultation_duration_minutes ?? 60)->format('H:i:s'),
                'duration_minutes'           => $expert->consultation_duration_minutes ?? 60,
                'status'                     => $status,
                'notes'                      => 'Customer request: ' . $topics[$idx % count($topics)],
                'admin_notes'                => null,
                'fee'                        => $fee,
                'payment_status'             => $payStatus,
                'stripe_payment_intent_id'   => $stripePaymentIntentId,
                'stripe_payment_status'      => $stripePaymentIntentId ? 'succeeded' : null,
                'is_refunded'                => $payStatus === 'refunded' ? 1 : 0,
                'refunded_at'                => $payStatus === 'refunded' ? $scheduled->copy()->addDays(2) : null,
                'topic'                      => $topics[$idx % count($topics)],
                'meeting_link'               => in_array($status, ['confirmed', 'completed'])
                    ? 'https://meet.google.com/plantix-' . strtolower(substr(md5($idx), 0, 10))
                    : null,
                'accepted_at'                => in_array($status, ['confirmed', 'completed'])
                    ? $scheduled->copy()->subHours(2) : null,
                'completed_at'               => $status === 'completed'
                    ? $scheduled->copy()->addMinutes($expert->consultation_duration_minutes ?? 60) : null,
                'rejected_at'                => $status === 'rejected' ? $scheduled->copy()->subHours(1) : null,
                'reject_reason'              => $status === 'rejected' ? 'Expert unavailable on requested date.' : null,
                'cancellation_reason'        => $status === 'cancelled' ? 'Customer cancelled due to schedule conflict.' : null,
                'expert_response_notes'      => null,
                'customer_rating'            => $status === 'completed' ? rand(4, 5) : null,
                'customer_review'            => $status === 'completed'
                    ? ['Excellent consultation.', 'Very helpful and detailed advice.', 'Highly recommended!'][rand(0, 2)]
                    : null,
                'rated_at'                   => $status === 'completed' ? $scheduled->copy()->addDays(1) : null,
                'reminder_sent_at'           => null,
                'created_at'                 => $scheduled->copy()->subDays(rand(1, 10)),
                'updated_at'                 => $now,
            ]);

            // Status history entry — table is appointment_status_histories (plural)
            DB::table('appointment_status_histories')->insert([
                'appointment_id' => $appointmentId,
                'changed_by'     => $userId,
                'from_status'    => 'pending',
                'to_status'      => $status,
                'notes'          => 'Initial booking.',
                'changed_at'     => $scheduled->copy()->subDays(rand(1, 10)),
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
