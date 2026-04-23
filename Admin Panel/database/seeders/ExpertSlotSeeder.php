<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * ExpertSlotSeeder
 *
 * Seeds appointment_slots for all approved experts.
 *
 * Schema (from migration 2026_03_01_200001):
 *   id, expert_id, date, start_time, end_time,
 *   is_booked (bool, default false), appointment_id (nullable FK), timestamps
 *
 * Strategy:
 *  - 4 weeks of slots: 2 weeks past + current week + 1 week future
 *  - Working days only: Monday → Saturday
 *  - Hours: 09:00 → 17:00 (eight 60-minute blocks per day)
 *  - ~30% of past slots are marked as booked and linked to existing appointments
 */
class ExpertSlotSeeder extends Seeder
{
    // Slot duration in minutes
    private int $slotMinutes = 60;

    // Working hours
    private string $dayStart = '09:00';
    private string $dayEnd   = '17:00';

    public function run(): void
    {
        $now     = Carbon::now();
        $experts = DB::table('experts')->where('status', 'approved')->get();

        if ($experts->isEmpty()) {
            $this->command->warn('ExpertSlotSeeder: no approved experts found — skipping.');
            return;
        }

        // Load existing confirmed/completed appointments indexed by expert_id
        $appointments = DB::table('appointments')
            ->whereIn('status', ['confirmed', 'completed'])
            ->select('id', 'expert_id', 'scheduled_date', 'start_time')
            ->get()
            ->groupBy('expert_id');

        // Date range: 14 days back → 21 days forward
        $startDate = $now->copy()->subDays(14)->startOfDay();
        $endDate   = $now->copy()->addDays(21)->startOfDay();

        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        $totalInserted = 0;

        foreach ($experts as $expert) {
            $expertAppts = $appointments->get($expert->id, collect());

            // Index existing appointments: "date|HH:MM" => appointment_id
            $apptIndex = [];
            foreach ($expertAppts as $a) {
                $key             = $a->scheduled_date . '|' . substr($a->start_time, 0, 5);
                $apptIndex[$key] = $a->id;
            }

            $slots    = [];
            $batchSize = 200;

            foreach ($period as $date) {
                // Skip Sundays (0 = Sunday in Carbon)
                if ($date->dayOfWeek === Carbon::SUNDAY) {
                    continue;
                }

                $slotStart = Carbon::parse($date->toDateString() . ' ' . $this->dayStart);
                $slotEnd   = Carbon::parse($date->toDateString() . ' ' . $this->dayEnd);

                while ($slotStart->lt($slotEnd)) {
                    $start = $slotStart->format('H:i:s');
                    $end   = $slotStart->copy()->addMinutes($this->slotMinutes)->format('H:i:s');

                    $dateStr = $date->toDateString();
                    $key     = $dateStr . '|' . substr($start, 0, 5);

                    $isBooked     = false;
                    $appointmentId = null;

                    // If there is a real appointment at this slot, mark it booked
                    if (isset($apptIndex[$key])) {
                        $isBooked      = true;
                        $appointmentId = $apptIndex[$key];
                    } elseif ($date->isPast()) {
                        // For past slots with no real appointment, randomly mark ~20% booked
                        // (simulates completed sessions not seeded via AppointmentSeeder)
                        $isBooked = (rand(1, 100) <= 20);
                    }

                    $slots[] = [
                        'expert_id'      => $expert->id,
                        'date'           => $dateStr,
                        'start_time'     => $start,
                        'end_time'       => $end,
                        'is_booked'      => $isBooked ? 1 : 0,
                        'appointment_id' => $appointmentId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];

                    // Bulk insert in batches
                    if (count($slots) >= $batchSize) {
                        $this->insertIgnore($slots);
                        $totalInserted += count($slots);
                        $slots = [];
                    }

                    $slotStart->addMinutes($this->slotMinutes);
                }
            }

            // Insert remaining slots for this expert
            if (! empty($slots)) {
                $this->insertIgnore($slots);
                $totalInserted += count($slots);
            }
        }

        $this->command->info("ExpertSlotSeeder: inserted {$totalInserted} slots for {$experts->count()} expert(s).");
    }

    /**
     * Insert with IGNORE to skip duplicates silently
     * (unique key: expert_id + date + start_time).
     */
    private function insertIgnore(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        // Build raw INSERT IGNORE for performance
        $columns    = array_keys($rows[0]);
        $columnList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $rowPlaceholders = implode(', ', array_fill(0, count($rows), "({$placeholders})"));

        $bindings = [];
        foreach ($rows as $row) {
            foreach ($row as $val) {
                $bindings[] = $val;
            }
        }

        DB::statement(
            "INSERT IGNORE INTO `appointment_slots` ({$columnList}) VALUES {$rowPlaceholders}",
            $bindings
        );
    }
}
