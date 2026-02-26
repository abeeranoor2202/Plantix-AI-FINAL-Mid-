<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CropRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public access allowed (guest + auth)
    }

    public function rules(): array
    {
        return [
            'nitrogen'    => ['required', 'numeric', 'min:0', 'max:500'],
            'phosphorus'  => ['required', 'numeric', 'min:0', 'max:300'],
            'potassium'   => ['required', 'numeric', 'min:0', 'max:400'],
            'ph_level'    => ['required', 'numeric', 'min:0', 'max:14'],
            'humidity'    => ['required', 'numeric', 'min:0', 'max:100'],
            'rainfall_mm' => ['required', 'numeric', 'min:0', 'max:10000'],
            'temperature' => ['required', 'numeric', 'min:-20', 'max:55'],
        ];
    }

    public function messages(): array
    {
        return [
            'nitrogen.required'    => 'Nitrogen content is required.',
            'phosphorus.required'  => 'Phosphorus content is required.',
            'potassium.required'   => 'Potassium content is required.',
            'ph_level.required'    => 'Soil pH level is required.',
            'ph_level.max'         => 'pH must be between 0 and 14.',
            'humidity.required'    => 'Humidity percentage is required.',
            'rainfall_mm.required' => 'Annual rainfall estimate is required.',
            'temperature.required' => 'Temperature is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nitrogen'    => 'Nitrogen (N)',
            'phosphorus'  => 'Phosphorus (P)',
            'potassium'   => 'Potassium (K)',
            'ph_level'    => 'Soil pH',
            'humidity'    => 'Humidity',
            'rainfall_mm' => 'Rainfall (mm)',
            'temperature' => 'Temperature (°C)',
        ];
    }
}
