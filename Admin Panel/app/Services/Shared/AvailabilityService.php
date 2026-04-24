<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Expert;
use App\Models\ExpertAvailability;
use App\Models\ExpertUnavailableDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * AvailabilityService
 *
 * Single point of truth for expert slot availability.
 *
 * Responsibilities:
 *  - Query available slots for a given expert + date range
 *  - Lock a slot atomically inside a DB transaction (SELECT FOR UPDATE)
 *  - Release a slot on cancellation
 *  - Cache available slot lists (invalidated on change)
 *  - Prevent double-booking via DB unique constraint + pessimistic locking
 *
 * Race-condition safety:
 *  - All slot mutations happen inside DB::transaction()
 *  - lockForUpdate() prevents concurrent reads on the same slot row
 *  - The UNIQUE(expert_id, date, start_time) DB constraint is the final guard
 */
class AvailabilityService
{
    private const CACHE_TTL = 120; // seconds

    // ── Queries ───────────────────────────────────────────────────────────────

    /**
     * Return available (not booked) slots for an expert on a given date.
     * Results are cached; invalidated whenever a slot is locked/released.
     */
    public function getAvailableSlots(Expert $expert, string $date): Collection
    {
        $cacheKey = $this->cacheKey($expert->id, $date);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($expert, $date) {
            $query = AppointmentSlot::where('expert_id', $expert->id)
                ->where('date', $date)
                ->where('is_booked', false)
                ->orderBy('start_time');

            // Only filter by current time for today's date.
            if ($date === now()->toDateString()) {
                $query->where('start_time', '>', now()->toTimeString());
            }

            return $query->get();
        });
    }

    /**
     * Return all slots (booked + available) for an expert across a date range.
     * Used by admin / expert dashboard calendar view.
     */
    public function getSlotsForRange(Expert $expert, string $from, string $to): Collection
    {
        return AppointmentSlot::where('expert_id', $expert->id)
            ->whereBetween('date', [$from, $to])
            ->with('appointment.user')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    // ── Mutations ─────────────────────────────────────────────────────────────

    /**
     * Atomically lock a slot for an appointment.
     *
     * Use inside the same outer DB::transaction() as Appointment::create().
     *
     * @throws \DomainException on conflict or invalid slot
     */
    public function lockSlot(int $slotId, Appointment $appointment): AppointmentSlot
    {
        // SELECT FOR UPDATE — blocks concurrent transactions on this row
        $slot = AppointmentSlot::lockForUpdate()->findOrFail($slotId);

        if ($slot->is_booked) {
            throw new \DomainException('The selected time slot has just been booked by someone else. Please choose another slot.');
        }

        if ((int) $slot->expert_id !== (int) $appointment->expert_id) {
            throw new \DomainException('Slot does not belong to this expert.');
        }

        $dateStr = $slot->date instanceof \Carbon\Carbon ? $slot->date->toDateString() : substr((string) $slot->date, 0, 10);
        $slotDate = Carbon::parse($dateStr . ' ' . $slot->start_time);
        if ($slotDate->isPast()) {
            throw new \DomainException('You cannot book a slot in the past.');
        }

        $slot->update([
            'is_booked'      => true,
            'appointment_id' => $appointment->id,
        ]);

        // Sync scheduled_at, scheduled_date, start_time, end_time on appointment
        $appointment->updateQuietly([
            'scheduled_at'   => $slotDate,
            'scheduled_date' => $slot->date,
            'start_time'     => $slot->start_time,
            'end_time'       => $slot->end_time,
        ]);

        // Always use toDateString() so the cache key matches getAvailableSlots()
        Cache::forget($this->cacheKey($slot->expert_id, $dateStr));

        return $slot;
    }

    /**
     * Release a slot back to available state (on cancellation / rejection).
     */
    public function releaseSlot(Appointment $appointment): void
    {
        $slot = AppointmentSlot::where('appointment_id', $appointment->id)->first();

        if (! $slot) {
            return; // slot may have been manually assigned — nothing to release
        }

        $dateStr = $slot->date instanceof \Carbon\Carbon ? $slot->date->toDateString() : substr((string) $slot->date, 0, 10);

        $slot->update(['is_booked' => false, 'appointment_id' => null]);

        // Always use toDateString() so the cache key matches getAvailableSlots()
        Cache::forget($this->cacheKey($slot->expert_id, $dateStr));
    }

    /**
     * Bulk-create availability slots for an expert.
     * Called from ExpertProfileController when expert sets their schedule.
     *
     * $slots = [
     *   ['date' => '2026-03-10', 'start_time' => '09:00', 'end_time' => '10:00'],
     *   ...
     * ]
     */
    public function createSlots(Expert $expert, array $slots): int
    {
        $now     = now();
        $records = [];

        foreach ($slots as $slot) {
            // Skip if date+time is in the past
            $start = Carbon::parse($slot['date'] . ' ' . $slot['start_time']);
            if ($start->isPast()) {
                continue;
            }

            $records[] = [
                'expert_id'  => $expert->id,
                'date'       => $slot['date'],
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
                'is_booked'  => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($records)) {
            return 0;
        }

        // insertOrIgnore respects UNIQUE(expert_id, date, start_time)
        $count = AppointmentSlot::insertOrIgnore($records);

        // Invalidate cache for each affected date
        $dates = array_unique(array_column($records, 'date'));
        foreach ($dates as $date) {
            Cache::forget($this->cacheKey($expert->id, $date));
        }

        return $count;
    }

    /**
     * Delete future unbooked slots for an expert on a given date.
     * Called if expert manually removes availability.
     */
    public function deleteUnbookedSlots(Expert $expert, string $date): int
    {
        $deleted = AppointmentSlot::where('expert_id', $expert->id)
            ->where('date', $date)
            ->where('is_booked', false)
            ->delete();

        Cache::forget($this->cacheKey($expert->id, $date));

        return $deleted;
    }

    /**
     * Check whether an expert has any available slot at a given datetime.
     * Used for validation before creating a PaymentIntent.
     */
    public function isSlotAvailable(Expert $expert, string $date, string $startTime): bool
    {
        return AppointmentSlot::where('expert_id', $expert->id)
            ->where('date', $date)
            ->where('start_time', $startTime)
            ->where('is_booked', false)
            ->exists();
    }

    /**
     * Confirm an expert is still active and available.
     * Guards against admin disabling expert after booking was initiated.
     * Uses Expert::isApproved() — single source of truth on experts.status.
     */
    public function assertExpertAvailable(Expert $expert): void
    {
        if (! $expert->isApproved()) {
            throw new \DomainException('This expert account has not been approved.');
        }

        if (! $expert->is_available) {
            throw new \DomainException('This expert is currently unavailable.');
        }
    }


    // ── Recurring schedule management ─────────────────────────────────────────

    /**
     * Generate concrete AppointmentSlots from the expert's ExpertAvailability
     * recurring schedule for every date in $period.
     *
     * Skips dates that are blocked in expert_unavailable_dates.
     * Uses insertOrIgnore — idempotent, safe to call multiple times.
     *
     * @param  Expert       $expert
     * @param  CarbonPeriod $period
     * @param  int          $slotDurationMinutes  Each slot's length in minutes
     * @return int  Number of slots inserted
     */
    public function generateWeeklySlots(
        Expert       $expert,
        CarbonPeriod $period,
        int          $slotDurationMinutes = 60
    ): int {
        $schedule = ExpertAvailability::where('expert_id', $expert->id)
            ->active()
            ->get()
            ->groupBy('day_of_week');

        if ($schedule->isEmpty()) {
            return 0;
        }

        $startStr = $period->getStartDate()->toDateString();
        $endStr   = $period->getEndDate()->toDateString();

        $blockedDates = ExpertUnavailableDate::where('expert_id', $expert->id)
            ->whereBetween('unavailable_date', [$startStr, $endStr])
            ->pluck('unavailable_date')
            ->map(fn ($d) => (string) $d)
            ->flip()
            ->toArray();

        $records = [];
        $now     = now();

        foreach ($period as $date) {
            $dateStr   = $date->toDateString();
            $dayOfWeek = $date->dayOfWeek;  // 0 = Sunday … 6 = Saturday

            if (isset($blockedDates[$dateStr]) || ! $schedule->has($dayOfWeek)) {
                continue;
            }

            foreach ($schedule[$dayOfWeek] as $block) {
                $cursor = Carbon::parse($dateStr . ' ' . $block->start_time);
                $end    = Carbon::parse($dateStr . ' ' . $block->end_time);

                while ($cursor->lt($end)) {
                    $slotEnd = $cursor->copy()->addMinutes($slotDurationMinutes);
                    if ($slotEnd->gt($end)) {
                        break;  // Partial slot — discard
                    }
                    $records[] = [
                        'expert_id'  => $expert->id,
                        'date'       => $dateStr,
                        'start_time' => $cursor->format('H:i:s'),
                        'end_time'   => $slotEnd->format('H:i:s'),
                        'is_booked'  => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $cursor->addMinutes($slotDurationMinutes);
                }
            }
        }

        if (empty($records)) {
            return 0;
        }

        $inserted = 0;
        foreach (array_chunk($records, 500) as $chunk) {
            $inserted += AppointmentSlot::insertOrIgnore($chunk);
        }

        $generatedDates = array_unique(array_column($records, 'date'));
        foreach ($generatedDates as $d) {
            Cache::forget($this->cacheKey($expert->id, $d));
        }

        return $inserted;
    }

    /**
     * Block an expert on a specific date.
     * Deletes any unbooked slots for that date.
     * Refuses block if booked slots already exist.
     *
     * @throws \RuntimeException if date has booked slots
     */
    public function blockDate(
        Expert  $expert,
        string  $date,
        ?string $reason     = null,
        ?string $blockFrom  = null,
        ?string $blockUntil = null
    ): ExpertUnavailableDate {
        return DB::transaction(function () use ($expert, $date, $reason, $blockFrom, $blockUntil) {
            $bookedCount = AppointmentSlot::where('expert_id', $expert->id)
                ->where('date', $date)
                ->where('is_booked', true)
                ->count();

            if ($bookedCount > 0) {
                throw new \RuntimeException(
                    "Cannot block {$date}: {$bookedCount} booked slot(s) already exist."
                );
            }

            $this->deleteUnbookedSlots($expert, $date);

            return ExpertUnavailableDate::updateOrCreate(
                ['expert_id' => $expert->id, 'unavailable_date' => $date],
                [
                    'reason'      => $reason,
                    'block_from'  => $blockFrom,
                    'block_until' => $blockUntil,
                ]
            );
        });
    }

    /**
     * Remove a date block — expert is available again on that date.
     * Does NOT re-generate slots automatically; call generateWeeklySlots separately.
     */
    public function unblockDate(Expert $expert, string $date): void
    {
        ExpertUnavailableDate::where('expert_id', $expert->id)
            ->where('unavailable_date', $date)
            ->delete();

        Cache::forget($this->cacheKey($expert->id, $date));
    }
    // ── Private ───────────────────────────────────────────────────────────────

    private function cacheKey(int $expertId, string $date): string
    {
        return "expert_slots_{$expertId}_{$date}";
    }
}
