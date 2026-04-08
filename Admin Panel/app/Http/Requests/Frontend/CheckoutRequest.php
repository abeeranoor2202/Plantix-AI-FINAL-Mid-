<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    /**
     * Combine individual address fields into the single `delivery_address`
     * field that the backend service expects.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('street')) {
            $parts = array_filter([
                $this->input('first_name') . ' ' . $this->input('last_name'),
                $this->input('street'),
                $this->input('street2'),
                $this->input('city'),
                $this->input('state'),
                $this->input('country'),
                'Phone: ' . $this->input('phone'),
            ]);
            $this->merge(['delivery_address' => implode(', ', $parts)]);
        }
    }

    public function rules(): array
    {
        return [
            'delivery_address' => 'required|string|max:1000',
            'payment_method'   => 'required|in:cod,stripe',
            'coupon_code'      => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
            'delivery_fee'     => 'nullable|numeric|min:0',
        ];
    }
}
