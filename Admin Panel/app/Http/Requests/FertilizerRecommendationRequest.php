<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FertilizerRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crop_type'    => ['required', 'string', 'max:100'],
            'nitrogen'     => ['nullable', 'numeric', 'min:0', 'max:500'],
            'phosphorus'   => ['nullable', 'numeric', 'min:0', 'max:300'],
            'potassium'    => ['nullable', 'numeric', 'min:0', 'max:400'],
            'soil_test_id' => ['nullable', 'integer', 'exists:soil_tests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'crop_type.required' => 'Please select or enter a crop type.',
        ];
    }
}
