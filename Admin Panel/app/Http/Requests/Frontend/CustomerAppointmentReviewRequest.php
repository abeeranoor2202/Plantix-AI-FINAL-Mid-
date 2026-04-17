<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAppointmentReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'customer_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'customer_review' => ['nullable', 'string', 'max:2000'],
        ];
    }
}