<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpertProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('expert')->check();
    }

    public function rules(): array
    {
        return [
            // User base fields
            'name'  => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],

            // Expert base
            'specialty'    => ['nullable', 'string', 'max:150'],
            'bio'          => ['nullable', 'string', 'max:2000'],
            'is_available' => ['nullable', 'boolean'],
            'hourly_rate'  => ['nullable', 'numeric', 'min:0', 'max:99999'],

            // Expert profile
            'agency_name'           => ['nullable', 'string', 'max:150'],
            'specialization'        => ['nullable', 'string', 'max:150'],
            'experience_years'      => ['nullable', 'integer', 'min:0', 'max:60'],
            'certifications'        => ['nullable', 'string', 'max:3000'],
            'availability_schedule' => ['nullable', 'array'],
            'website'               => ['nullable', 'url', 'max:255'],
            'linkedin'              => ['nullable', 'url', 'max:255'],
            'contact_phone'         => ['nullable', 'string', 'max:30'],
            'city'                  => ['nullable', 'string', 'max:100'],
            'address'               => ['nullable', 'string', 'max:255'],
            'map_link'              => ['nullable', 'url', 'max:500'],
            'country'               => ['nullable', 'string', 'max:100'],
            'account_type'          => ['nullable', 'in:individual,agency'],

            // Avatar
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Specializations
            'specializations'         => ['nullable', 'array', 'max:10'],
            'specializations.*.name'  => ['required_with:specializations', 'string', 'max:100'],
            'specializations.*.level' => ['nullable', 'in:beginner,intermediate,expert'],

            // Password change (optional)
            'current_password'         => ['nullable', 'string'],
            'new_password'             => ['nullable', 'string', 'min:8', 'confirmed', 'required_with:current_password'],
            'new_password_confirmation' => ['nullable', 'string'],
        ];
    }
}
