<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Expert;
use App\Models\ExpertAvailability;
use App\Models\ExpertProfile;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function assertBookingAllowed(
        Expert $expert,
        Carbon $scheduledAt,
        string $type,
        int $durationMinutes = 60,
        ?int $slotId = null
    ): void
    {
        $this->availability->assertExpertAvailable($expert);

        if (! in_array($type, ['physical', 'online'], true)) {
            throw ValidationException::withMessages([
                'type' => 'Please choose a valid appointment type.',
            ]);
        }

        if ($type === 'physical' && ! $this->resolveLocation($expert)) {
            throw ValidationException::withMessages([
                'type' => 'This expert has not published a physical consultation location yet.',
            ]);
        }

        $time    = $scheduledAt->format('H:i:s');
        $endTime = $scheduledAt->copy()->addMinutes(max(1, $durationMinutes))->format('H:i:s');
        $dayName = strtolower($scheduledAt->englishDayOfWeek);

        $blocks = ExpertAvailability::query()
            ->where('expert_id', $expert->id)
            ->active()
            ->get();

        if ($blocks->isEmpty()) {
            if ($slotId && $this->slotMatchesRequestedWindow($expert, $scheduledAt, $slotId)) {
                return;
            }

            throw ValidationException::withMessages([
                $slotId ? 'slot_id' : 'scheduled_at' => 'The selected expert has no availability configured.',
            ]);
        }

        $fitsBlock = $blocks->contains(function (ExpertAvailability $block) use ($scheduledAt, $time, $endTime, $dayName) {
            $blockDay = strtolower((string) ($block->day ?: $block->day_name));

            if ($block->day && $blockDay !== $dayName) {
                return false;
            }

            if (! empty($block->day_of_week) && (int) $block->day_of_week !== (int) $scheduledAt->dayOfWeek) {
                return false;
            }

            return $block->start_time <= $time && $block->end_time >= $endTime;
        });

        if (! $fitsBlock) {
            if ($slotId && $this->slotMatchesRequestedWindow($expert, $scheduledAt, $slotId)) {
                return;
            }

            throw ValidationException::withMessages([
                $slotId ? 'slot_id' : 'scheduled_at' => 'The selected time is outside the expert availability window.',
            ]);
        }

        $start = $scheduledAt->copy();
        $end   = $scheduledAt->copy()->addMinutes(max(1, $durationMinutes));

        $conflict = Appointment::query()
            ->where('expert_id', $expert->id)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_REJECTED, Appointment::STATUS_PAYMENT_FAILED])
            ->whereRaw('scheduled_at < ? AND DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$end, $start])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                $slotId ? 'slot_id' : 'scheduled_at' => 'This time slot is already booked. Please choose another time.',
            ]);
        }
    }

    private function slotMatchesRequestedWindow(Expert $expert, Carbon $scheduledAt, int $slotId): bool
    {
        $slot = AppointmentSlot::query()
            ->where('id', $slotId)
            ->where('expert_id', $expert->id)
            ->first();

        if (! $slot || (bool) $slot->is_booked) {
            return false;
        }

        $slotDate = $slot->date instanceof Carbon ? $slot->date->toDateString() : substr((string) $slot->date, 0, 10);
        $slotStart = Carbon::parse($slotDate . ' ' . $slot->start_time);

        return $slotStart->equalTo($scheduledAt);
    }

    public function resolveLocation(Expert|ExpertProfile|null $subject): ?string
    {
        $profile = $subject instanceof Expert ? $subject->profile : $subject;

        if (! $profile) {
            return null;
        }

        $parts = array_filter([
            $profile->address,
            $profile->city,
            $profile->country,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}