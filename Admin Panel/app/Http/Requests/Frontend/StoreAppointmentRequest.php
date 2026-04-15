<?php

namespace App\Http\Requests\Frontend;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'expert_id'    => ['required', 'exists:experts,id'],
            'type'         => ['required', 'in:physical,online'],
            'scheduled_at' => ['required', 'date', 'after:now'],
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
            $scheduledAt = $this->date('scheduled_at');

            if (! $expertId || ! $scheduledAt) {
                return;
            }

            $hasConflict = Appointment::query()
                ->where('expert_id', $expertId)
                ->where('scheduled_at', $scheduledAt)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELLED,
                    Appointment::STATUS_REJECTED,
                    Appointment::STATUS_PAYMENT_FAILED,
                ])
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add('scheduled_at', 'This time slot is already booked. Please choose another time.');
            }
        });
    }
}
