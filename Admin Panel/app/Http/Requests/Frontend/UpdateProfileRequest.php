<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'name'          => 'sometimes|required|string|max:100',
            'phone'         => 'nullable|string|max:30',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'notification_preferences' => 'nullable|array',
            'notification_preferences.appointment_emails' => 'nullable|boolean',
            'notification_preferences.forum_notifications' => 'nullable|boolean',
            'notification_preferences.system_alerts' => 'nullable|boolean',
        ];
    }
}
