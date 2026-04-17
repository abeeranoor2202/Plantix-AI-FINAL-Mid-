<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderDisputeEscalateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'escalation_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}