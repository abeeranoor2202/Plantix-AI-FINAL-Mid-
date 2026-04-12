<?php

namespace App\Http\Requests\Frontend;

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
}