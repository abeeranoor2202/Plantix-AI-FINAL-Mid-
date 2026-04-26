<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FertilizerRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'integer' rejects decimals; min/max enforce the allowed ranges.
            'nitrogen'     => ['required', 'integer', 'min:0', 'max:42'],
            'phosphorus'   => ['required', 'integer', 'min:0', 'max:42'],
            'potassium'    => ['required', 'integer', 'min:0', 'max:19'],
            'soil_test_id' => ['nullable', 'integer', 'exists:soil_tests,id'],
        ];
    }

    public function messages(): array
    {
        $msg = 'Invalid input. Please enter whole numbers within the allowed range.';

        return [
            'nitrogen.required'   => $msg,
            'nitrogen.integer'    => $msg,
            'nitrogen.min'        => $msg,
            'nitrogen.max'        => $msg,
            'phosphorus.required' => $msg,
            'phosphorus.integer'  => $msg,
            'phosphorus.min'      => $msg,
            'phosphorus.max'      => $msg,
            'potassium.required'  => $msg,
            'potassium.integer'   => $msg,
            'potassium.min'       => $msg,
            'potassium.max'       => $msg,
        ];
    }

    /**
     * Override the failed-validation response so both AJAX and browser
     * requests always receive the canonical structured error body.
     */
    protected function failedValidation(Validator $validator): never
    {
        $response = response()->json([
            'status'  => 'invalid',
            'message' => 'Invalid input. Please enter whole numbers within the allowed range.',
        ], 422);

        throw new HttpResponseException($response);
    }
}
