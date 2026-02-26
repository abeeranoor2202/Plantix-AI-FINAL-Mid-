<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiseaseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow guests to submit disease reports
    }

    public function rules(): array
    {
        return [
            'image'       => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB
            ],
            'crop_name'   => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please upload a photo of the affected crop.',
            'image.image'    => 'The uploaded file must be an image.',
            'image.mimes'    => 'Only JPEG, PNG, and WebP images are accepted.',
            'image.max'      => 'Image size must not exceed 5MB.',
        ];
    }
}
