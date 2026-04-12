<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class AcceptAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('expert')->check();
    }

    public function rules(): array
    {
        $appointment = $this->route('appointment');
        $isOnline = (string) ($appointment?->type ?? '') === 'online';

        return [
            'meeting_link' => [$isOnline ? 'required' : 'nullable', 'url', 'max:500'],
        ];
    }
}
