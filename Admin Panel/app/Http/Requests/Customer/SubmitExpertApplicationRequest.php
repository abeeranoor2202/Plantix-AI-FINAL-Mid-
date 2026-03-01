<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SubmitExpertApplicationRequest
 *
 * Validates a customer's application to become an expert.
 * File uploads are optional but must be PDF / JPEG / PNG, max 5 MB each.
 */
class SubmitExpertApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // ── Required fields ──────────────────────────────────────────
            'full_name'        => ['required', 'string', 'min:2', 'max:255'],
            'specialization'   => ['required', 'string', 'min:2', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],

            // ── Optional fields ──────────────────────────────────────────
            'qualifications'   => ['nullable', 'string', 'max:5000'],
            'bio'              => ['nullable', 'string', 'max:2000'],
            'contact_phone'    => ['nullable', 'string', 'max:30'],
            'city'             => ['nullable', 'string', 'max:100'],
            'country'          => ['nullable', 'string', 'max:100'],
            'website'          => ['nullable', 'url', 'max:255'],
            'linkedin'         => ['nullable', 'url', 'max:255'],
            'account_type'     => ['nullable', 'in:individual,agency'],
            'agency_name'      => ['required_if:account_type,agency', 'nullable', 'string', 'max:255'],

            // ── File uploads ──────────────────────────────────────────────
            // PDF / JPEG / PNG, max 5 MB
            'certifications_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'id_document_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'        => 'Please enter your full name.',
            'specialization.required'   => 'Please specify your area of expertise.',
            'experience_years.required' => 'Please enter your years of experience.',
            'experience_years.integer'  => 'Experience years must be a number.',
            'website.url'               => 'Please enter a valid website URL (including https://).',
            'linkedin.url'              => 'Please enter a valid LinkedIn URL.',
            'certifications_file.mimes' => 'Certifications file must be a PDF, JPG, or PNG.',
            'certifications_file.max'   => 'Certifications file must not exceed 5 MB.',
            'id_document_file.mimes'    => 'ID document must be a PDF, JPG, or PNG.',
            'id_document_file.max'      => 'ID document must not exceed 5 MB.',
            'agency_name.required_if'   => 'Agency name is required when account type is Agency.',
        ];
    }
}
