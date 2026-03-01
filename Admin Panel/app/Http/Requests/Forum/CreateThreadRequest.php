<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class CreateThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is done via Policy in the controller.
        // Here we just confirm the user is authenticated.
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'min:5', 'max:255'],
            'body'              => ['required', 'string', 'min:20', 'max:20000'],
            'forum_category_id' => ['nullable', 'integer', 'exists:forum_categories,id'],
        ];
    }
}
