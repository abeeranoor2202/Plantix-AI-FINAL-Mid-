<?php

namespace App\Http\Requests\Frontend;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $expertId = (int) $this->input('expert_id');
        $slotId = $this->input('slot_id');
        $scheduledAtRaw = $this->input('scheduled_at');

        if (! empty($slotId) || ! $expertId || empty($scheduledAtRaw)) {
            return;
        }

        try {
            $scheduledAt = Carbon::parse((string) $scheduledAtRaw);
        } catch (\Throwable) {
            return;
        }

        $resolvedSlot = AppointmentSlot::query()
            ->where('expert_id', $expertId)
            ->whereDate('date', $scheduledAt->toDateString())
            ->where('start_time', $scheduledAt->format('H:i:s'))
            ->first();

        if ($resolvedSlot) {
            $this->merge(['slot_id' => $resolvedSlot->id]);
        }
    }

    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'expert_id'    => ['required', 'exists:experts,id'],
            'slot_id'      => ['required_without:scheduled_at', 'integer', 'exists:appointment_slots,id'],
            'scheduled_at' => ['nullable', 'date'],
            'type'         => ['required', 'in:physical,online'],
            'topic'        => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $expertId = (int) $this->input('expert_id');
            $slotId = (int) $this->input('slot_id');
            $expert = $expertId ? Expert::query()->with('profile')->find($expertId) : null;

            if (
                $expert
                && $this->input('type') === 'physical'
                && ! $this->hasPhysicalLocation($expert)
            ) {
                $validator->errors()->add('type', 'This expert has not published a physical consultation location yet.');
                return;
            }

            if (! $expertId || ! $slotId) {
                return;
            }

            $slot = AppointmentSlot::query()->find($slotId);
            if (! $slot) {
                return;
            }

            if ((int) $slot->expert_id !== $expertId) {
                $validator->errors()->add('slot_id', 'Selected slot does not belong to the selected expert.');
                return;
            }

            $slotDateStr = $slot->date instanceof Carbon ? $slot->date->toDateString() : substr((string) $slot->date, 0, 10);
            $slotStart = Carbon::parse($slotDateStr . ' ' . $slot->start_time);
            if ($slotStart->isPast()) {
                $validator->errors()->add('slot_id', 'Selected slot is no longer available.');
                return;
            }

            if ((bool) $slot->is_booked) {
                $validator->errors()->add('slot_id', 'Selected slot is already booked. Please choose another slot.');
                return;
            }

            $hasConflict = Appointment::query()
                ->where('expert_id', $expertId)
                ->whereDate('scheduled_at', $slot->date)
                ->whereTime('scheduled_at', $slot->start_time)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELLED,
                    Appointment::STATUS_REJECTED,
                    Appointment::STATUS_PAYMENT_FAILED,
                ])
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add('slot_id', 'This time slot is already booked. Please choose another time.');
            }
        });
    }

    private function hasPhysicalLocation(Expert $expert): bool
    {
        $profile = $expert->profile;
        if (! $profile) {
            return false;
        }

        return ! empty(array_filter([
            $profile->address,
            $profile->city,
            $profile->country,
        ]));
    }
}
