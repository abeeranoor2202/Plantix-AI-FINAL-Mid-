<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class CreateReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    public function rules(): array
    {
        return [
            'body'      => ['required', 'string', 'min:5', 'max:10000'],
            'parent_id' => ['nullable', 'integer', 'exists:forum_replies,id'],
        ];
    }
}
