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
            'nitrogen'     => ['required', 'numeric', 'min:0', 'max:500'],
            'phosphorus'   => ['required', 'numeric', 'min:0', 'max:300'],
            'potassium'    => ['required', 'numeric', 'min:0', 'max:400'],
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
