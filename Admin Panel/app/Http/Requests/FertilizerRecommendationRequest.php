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
            'growth_stage' => ['nullable', 'string', 'in:pre-sowing,seedling,vegetative,flowering,fruiting,maturity'],
            'nitrogen'     => ['nullable', 'numeric', 'min:0', 'max:500'],
            'phosphorus'   => ['nullable', 'numeric', 'min:0', 'max:300'],
            'potassium'    => ['nullable', 'numeric', 'min:0', 'max:400'],
            'ph_level'     => ['nullable', 'numeric', 'min:0', 'max:14'],
            'temperature'  => ['nullable', 'numeric', 'min:-20', 'max:55'],
            'humidity'     => ['nullable', 'numeric', 'min:0', 'max:100'],
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
