<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CropPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'season'             => ['required', 'string', 'in:Rabi,Kharif,Zaid'],
            'year'               => ['required', 'integer', 'min:2020', 'max:2035'],
            'primary_crop'       => ['required', 'string', 'max:100'],
            'farm_size_acres'    => ['nullable', 'numeric', 'min:0.1', 'max:10000'],
            'soil_type'          => ['nullable', 'string', 'in:loamy,clay,sandy,silt,peat,chalky'],
            'water_source'       => ['nullable', 'string', 'in:rain,irrigation,both'],
            'irrigation_type'    => ['nullable', 'string', 'max:50'],
            'climate'            => ['nullable', 'string', 'max:50'],
            'water_availability' => ['nullable', 'string', 'in:abundant,moderate,limited,scarce'],
            'farm_profile_id'    => ['nullable', 'integer', 'exists:farm_profiles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'season.required'       => 'Crop season is required.',
            'season.in'             => 'Season must be Rabi, Kharif, or Zaid.',
            'year.required'         => 'Planning year is required.',
            'primary_crop.required' => 'Primary crop selection is required.',
        ];
    }
}
