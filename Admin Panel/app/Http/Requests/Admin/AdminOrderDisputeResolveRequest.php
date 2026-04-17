<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminOrderDisputeResolveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'resolution' => ['required', 'string', 'min:10', 'max:1000'],
            'status' => ['required', 'in:resolved,rejected,refunded'],
            'refund_reference' => ['nullable', 'string', 'max:120'],
        ];
    }
}