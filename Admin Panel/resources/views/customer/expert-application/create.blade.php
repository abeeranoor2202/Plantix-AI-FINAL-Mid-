@extends('layouts.frontend')

@section('title', 'Apply to Become an Expert | Plantix-AI')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Header --}}
            <div class="mb-4">
                <h2 class="fw-bold">Apply to Become an Expert</h2>
                <p class="text-muted">
                    Join Plantix AI as a verified agricultural expert. Your application will be
                    reviewed by our team within 2–5 business days.
                </p>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Pre-fill notice if re-applying after rejection --}}
            @if($application?->isRejected())
            <div class="alert alert-warning">
                <strong>Your previous application was rejected.</strong>
                @if($application->admin_notes)
                    Reason: {{ $application->admin_notes }}
                @endif
                <br>You may submit a new application below.
            </div>
            @endif

            <form action="{{ route('customer.expert-application.store') }}" method="POST"
                  enctype="multipart/form-data" novalidate>
                @csrf

                {{-- ── Personal Info ──────────────────────────────────── --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Personal Information</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name', auth()->user()->name) }}" required>
                                @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="tel" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                                       value="{{ old('contact_phone') }}">
                                @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                       value="{{ old('city') }}">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                       value="{{ old('country') }}">
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Professional Info ──────────────────────────────── --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Professional Background</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Area of Specialization <span class="text-danger">*</span></label>
                                <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror"
                                       value="{{ old('specialization') }}"
                                       placeholder="e.g. Soil Science, Crop Disease, Pest Management" required>
                                @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Years of Experience <span class="text-danger">*</span></label>
                                <input type="number" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror"
                                       value="{{ old('experience_years', 0) }}" min="0" max="60" required>
                                @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Qualifications & Credentials</label>
                                <textarea name="qualifications" rows="4"
                                          class="form-control @error('qualifications') is-invalid @enderror"
                                          placeholder="List your degrees, certifications, training courses, publications...">{{ old('qualifications') }}</textarea>
                                @error('qualifications')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Brief Bio</label>
                                <textarea name="bio" rows="3"
                                          class="form-control @error('bio') is-invalid @enderror"
                                          placeholder="Tell farmers about yourself and your expertise...">{{ old('bio') }}</textarea>
                                @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                                       value="{{ old('website') }}" placeholder="https://">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LinkedIn Profile</label>
                                <input type="url" name="linkedin" class="form-control @error('linkedin') is-invalid @enderror"
                                       value="{{ old('linkedin') }}" placeholder="https://linkedin.com/in/...">
                                @error('linkedin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Account Type ─────────────────────────────────────── --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Account Type</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Account Type</label>
                                <select name="account_type" class="form-select @error('account_type') is-invalid @enderror"
                                        id="accountTypeSelect">
                                    <option value="individual" @selected(old('account_type', 'individual') === 'individual')>Individual Expert</option>
                                    <option value="agency"     @selected(old('account_type') === 'agency')>Agency / Organization</option>
                                </select>
                                @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6" id="agencyNameGroup" style="display:none">
                                <label class="form-label">Agency / Organization Name</label>
                                <input type="text" name="agency_name" class="form-control @error('agency_name') is-invalid @enderror"
                                       value="{{ old('agency_name') }}">
                                @error('agency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Documents ────────────────────────────────────────── --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Supporting Documents <span class="text-muted fw-normal">(optional)</span></div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">PDF, JPG, or PNG files only. Maximum 5 MB each.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Certifications / Degree</label>
                                <input type="file" name="certifications_file"
                                       class="form-control @error('certifications_file') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                @error('certifications_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ID Document</label>
                                <input type="file" name="id_document_file"
                                       class="form-control @error('id_document_file') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                @error('id_document_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Review notice --}}
                <div class="alert alert-info small mb-4">
                    <strong>What happens next?</strong><br>
                    Your application will be reviewed within 2–5 business days. You will receive an
                    email notification once a decision has been made.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success px-4">Submit Application</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const select = document.getElementById('accountTypeSelect');
    const group  = document.getElementById('agencyNameGroup');

    function toggleAgency() {
        group.style.display = select.value === 'agency' ? '' : 'none';
    }

    select.addEventListener('change', toggleAgency);
    toggleAgency();  // Run on page load for old() value
</script>
@endpush
