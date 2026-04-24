<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ExpertAvailabilitySlotSeeder
 *
 * Seeds appointment_slots for all approved experts for the next 30 days.
 *
 * Schedule:
 *  - Monday–Saturday (skip Sundays)
 *  - 09:00–17:00 (8 one-hour slots per day)
 *  - All slots start as unbooked (is_booked = false)
 *
 * Safe to re-run — uses INSERT IGNORE to skip duplicates.
 */
class ExpertAvailabilitySlotSeeder extends Seeder
{
    private const SLOT_DURATION_MINUTES = 60;
    private const DAY_START = '09:00';
    private const DAY_END   = '17:00';
    private const DAYS_FORWARD = 30;

    public function run(): void
    {
        $now = Carbon::now();
        $experts = DB::table('experts')
            ->where('status', 'approved')
            ->where('is_available', true)
            ->get();

        if ($experts->isEmpty()) {
            $this->command->warn('ExpertAvailabilitySlotSeeder: no approved experts found — skipping.');
            return;
        }

        $startDate = $now->copy()->startOfDay();
        $endDate   = $now->copy()->addDays(self::DAYS_FORWARD)->endOfDay();
        $period    = CarbonPeriod::create($startDate, '1 day', $endDate);

        $totalInserted = 0;

        foreach ($experts as $expert) {
            $slots = [];

            foreach ($period as $date) {
                // Skip Sundays
                if ($date->dayOfWeek === Carbon::SUNDAY) {
                    continue;
                }

                $slotStart = Carbon::parse($date->toDateString() . ' ' . self::DAY_START);
                $slotEnd   = Carbon::parse($date->toDateString() . ' ' . self::DAY_END);

                while ($slotStart->lt($slotEnd)) {
                    // Skip past slots
                    if ($slotStart->isPast()) {
                        $slotStart->addMinutes(self::SLOT_DURATION_MINUTES);
                        continue;
                    }

                    $start = $slotStart->format('H:i:s');
                    $end   = $slotStart->copy()->addMinutes(self::SLOT_DURATION_MINUTES)->format('H:i:s');

                    $slots[] = [
                        'expert_id'      => $expert->id,
                        'date'           => $date->toDateString(),
                        'start_time'     => $start,
                        'end_time'       => $end,
                        'is_booked'      => 0,
                        'appointment_id' => null,
                        'created_at'     => $now->toDateTimeString(),
                        'updated_at'     => $now->toDateTimeString(),
                    ];

                    // Bulk insert in batches of 200
                    if (count($slots) >= 200) {
                        $inserted = $this->insertIgnore($slots);
                        $totalInserted += $inserted;
                        $slots = [];
                    }

                    $slotStart->addMinutes(self::SLOT_DURATION_MINUTES);
                }
            }

            // Insert remaining slots for this expert
            if (! empty($slots)) {
                $inserted = $this->insertIgnore($slots);
                $totalInserted += $inserted;
            }
        }

        $this->command->info(sprintf(
            'ExpertAvailabilitySlotSeeder: inserted %d new slots for %d expert(s) over %d days.',
            $totalInserted,
            $experts->count(),
            self::DAYS_FORWARD
        ));
    }

    /**
     * Insert with IGNORE to skip duplicates silently.
     * UNIQUE(expert_id, date, start_time) prevents double-booking.
     */
    private function insertIgnore(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $columns         = array_keys($rows[0]);
        $columnList      = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
        $placeholders    = implode(', ', array_fill(0, count($columns), '?'));
        $rowPlaceholders = implode(', ', array_fill(0, count($rows), "({$placeholders})"));

        $bindings = [];
        foreach ($rows as $row) {
            foreach ($row as $val) {
                $bindings[] = $val;
            }
        }

        return DB::affectingStatement(
            "INSERT IGNORE INTO `appointment_slots` ({$columnList}) VALUES {$rowPlaceholders}",
            $bindings
        );
    }
}
