<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorOrderDisputeResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('vendor')->check();
    }

    public function rules(): array
    {
        return [
            'response' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}