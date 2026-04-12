@extends('expert.layouts.app')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<form method="POST" action="{{ route('expert.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4 mb-4">
        {{-- Left column --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            {{-- Personal Info --}}
            <div class="card-agri p-0 border-0 bg-white hover-lift">
                <div class="p-4 bg-light border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-user me-2 text-primary"></i>Personal Details</h5>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Full Name</label>
                            <input type="text" name="name" class="form-agri @error('name') is-invalid @enderror"
                                   value="{{ old('name', $expert->user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Email Address</label>
                            <input type="email" class="form-agri bg-light text-muted" value="{{ $expert->user->email }}" disabled>
                            <div class="form-text small mt-1"><i class="fas fa-lock me-1"></i>Email cannot be changed directly.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Phone Number</label>
                            <input type="tel" name="phone" class="form-agri @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $profile?->contact_phone ?? $expert->user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Consultation Rate (PKR/hr)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold border" style="border-right: none;">PKR</span>
                                <input type="number" name="hourly_rate" class="form-agri" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                       value="{{ old('hourly_rate', $expert->hourly_rate) }}" min="0">
                            </div>
                            @error('hourly_rate')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-dark fw-bold small mb-2">Professional Summary (Bio)</label>
                            <textarea name="bio" rows="4" class="form-agri @error('bio') is-invalid @enderror"
                                      placeholder="Briefly describe your expertise, background, and what you specialize in...">{{ old('bio', $expert->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label text-dark fw-bold small mb-2">City</label>
                            <input type="text" name="city" class="form-agri"
                                   value="{{ old('city', $profile?->city) }}">
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label text-dark fw-bold small mb-2">Address</label>
                            <input type="text" name="address" class="form-agri"
                                   value="{{ old('address', $profile?->address) }}" placeholder="Street address or clinic location">
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label text-dark fw-bold small mb-2">Country</label>
                            <input type="text" name="country" class="form-agri"
                                   value="{{ old('country', $profile?->country) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-dark fw-bold small mb-2">Map Link</label>
                            <input type="url" name="map_link" class="form-agri"
                                   value="{{ old('map_link', $profile?->map_link) }}" placeholder="https://maps.google.com/...">
                        </div>
                        <div class="col-12 col-md-4 px-3 py-2 bg-light rounded border border-dashed d-flex flex-column justify-content-center">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Booking Status</label>
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input fs-4 cursor-pointer m-0 mt-1" type="checkbox" role="switch" id="is_available"
                                       name="is_available" value="1"
                                       {{ old('is_available', $expert->is_available) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark mt-1" for="is_available">Available</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional Info --}}
            <div class="card-agri p-0 border-0 bg-white hover-lift">
                <div class="p-4 bg-light border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-briefcase me-2 text-primary"></i>Professional Identity</h5>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        @if($expert->user->role === 'agency_expert')
                        <div class="col-12">
                            <label class="form-label text-dark fw-bold small mb-2">Agency / Company Name</label>
                            <input type="text" name="agency_name" class="form-agri @error('agency_name') is-invalid @enderror"
                                   value="{{ old('agency_name', $profile?->agency_name) }}">
                            @error('agency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Core Specialization</label>
                            <input type="text" name="specialization" class="form-agri @error('specialization') is-invalid @enderror"
                                   value="{{ old('specialization', $profile?->specialization ?? $expert->specialty) }}"
                                   placeholder="e.g. Crop Science, Plant Pathology">
                            @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Years of Experience</label>
                            <input type="number" name="experience_years" class="form-agri @error('experience_years') is-invalid @enderror"
                                   value="{{ old('experience_years', $profile?->experience_years) }}" min="0" max="60">
                            @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-dark fw-bold small mb-2">Certifications & Qualifications</label>
                            <textarea name="certifications" rows="3" class="form-agri @error('certifications') is-invalid @enderror"
                                      placeholder="List degrees, certifications, or professional memberships...">{{ old('certifications', $profile?->certifications) }}</textarea>
                            @error('certifications')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">Professional Website</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold border" style="border-right: none;"><i class="fas fa-link"></i></span>
                                <input type="url" name="website" class="form-agri" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                       value="{{ old('website', $profile?->website) }}" placeholder="https://...">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark fw-bold small mb-2">LinkedIn Profile</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold border" style="border-right: none; color: #0A66C2;"><i class="fab fa-linkedin-in"></i></span>
                                <input type="url" name="linkedin" class="form-agri" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                       value="{{ old('linkedin', $profile?->linkedin) }}" placeholder="https://linkedin.com/in/...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Specialization Tags --}}
            <div class="card-agri p-0 border-0 bg-white hover-lift">
                <div class="p-4 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tags me-2 text-primary"></i>Expertise Tags</h5>
                    <button type="button" class="btn-agri btn-agri-outline py-1 px-3 d-flex align-items-center gap-2" id="addSpecBtn" style="font-size: 13px;">
                        <i class="fas fa-plus"></i> Add Tag
                    </button>
                </div>
                <div class="p-4">
                    <div id="specializationsContainer">
                        @forelse($specializations as $i => $spec)
                        <div class="row g-3 mb-3 spec-row align-items-center bg-light p-3 rounded border border-dashed">
                            <div class="col-md-7">
                                <label class="form-label fw-bold small text-muted d-md-none">Skill Name</label>
                                <input type="text" name="specializations[{{ $i }}][name]"
                                       class="form-agri bg-white"
                                       value="{{ $spec->name }}" placeholder="e.g. Soil Analysis">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted d-md-none">Proficiency</label>
                                <select name="specializations[{{ $i }}][level]" class="form-agri bg-white">
                                    <option value="beginner"  {{ $spec->level==='beginner'?'selected':'' }}>Beginner</option>
                                    <option value="intermediate" {{ $spec->level==='intermediate'?'selected':'' }}>Intermediate</option>
                                    <option value="expert"    {{ $spec->level==='expert'?'selected':'' }}>Expert</option>
                                </select>
                            </div>
                            <div class="col-md-1 text-end mt-3 mt-md-0 d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-spec rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div id="emptySpecNote" class="text-center text-muted p-4 bg-light rounded border border-dashed">
                            <i class="fas fa-tags fs-3 mb-2 opacity-50 d-block"></i>
                            <p class="mb-0 fw-medium small">No expertise tags added. Click "Add Tag" above to showcase your skills.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- Avatar --}}
            <div class="card-agri p-0 border-0 bg-white">
                <div class="p-4 bg-light border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-camera-retro me-2 text-primary"></i>Profile Photo</h5>
                </div>
                <div class="p-4 text-center">
                    <div class="mb-4 position-relative d-inline-block">
                        @if($expert->profile_image)
                            <img src="{{ Storage::url($expert->profile_image) }}" id="avatarPreview"
                                 class="rounded-circle shadow-sm border border-3 border-light" style="width:140px;height:140px;object-fit:cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm border border-3 border-light mx-auto"
                                 id="avatarPlaceholder" style="width:140px;height:140px;font-size:3.5rem;font-weight:700; font-family: var(--font-heading);">
                                {{ strtoupper(substr($expert->user->name, 0, 1)) }}
                            </div>
                            <img id="avatarPreview" class="rounded-circle shadow-sm border border-3 border-light mx-auto d-none"
                                 style="width:140px;height:140px;object-fit:cover" src="">
                        @endif
                        <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow border" style="width:40px; height:40px; cursor:pointer; transform: translate(10%, 10%); transition: all 0.2s;">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" name="avatar" id="avatarInput" class="d-none @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/gif">
                    <div class="text-muted small fw-medium mt-2"><i class="fas fa-info-circle me-1"></i>Allowed JPG, GIF or PNG. Max: 2MB</div>
                    @error('avatar')<div class="text-danger small fw-bold mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Security --}}
            <div class="card-agri p-0 border-0 bg-white">
                <div class="p-4 bg-light border-bottom">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-shield-alt me-2 text-warning"></i>Change Password</h5>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold small mb-2">Current Password</label>
                        <input type="password" name="current_password" class="form-agri @error('current_password') is-invalid @enderror">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold small mb-2">New Password</label>
                        <input type="password" name="new_password" class="form-agri @error('new_password') is-invalid @enderror">
                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold small mb-2">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-agri">
                    </div>
                    <div class="form-text mt-3 text-muted small px-3 py-2 bg-light rounded border border-dashed"><i class="fas fa-info-circle me-1 text-warning"></i>Leave blank if you do not want to modify your password.</div>
                </div>
            </div>

            <div class="d-grid gap-3 pt-2">
                <button type="submit" class="btn-agri btn-agri-primary py-3 fs-6 shadow-sm">
                    <i class="fas fa-check-circle me-2"></i> Save Profile Details
                </button>
                <a href="{{ route('expert.profile.show') }}" class="btn-agri btn-agri-outline py-3 fs-6 bg-white">
                    <i class="fas fa-times me-2 text-muted"></i> Cancel Edit
                </a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
// Avatar preview
document.getElementById('avatarInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const url = URL.createObjectURL(this.files[0]);
        const preview = document.getElementById('avatarPreview');
        const placeholder = document.getElementById('avatarPlaceholder');
        preview.src = url;
        preview.classList.remove('d-none');
        if (placeholder) placeholder.classList.add('d-none');
    }
});

// Specialization rows
let specCount = {{ count($specializations) }};
document.getElementById('addSpecBtn').addEventListener('click', function() {
    const container = document.getElementById('specializationsContainer');
    const empty = document.getElementById('emptySpecNote');
    if (empty) empty.remove();
    const row = document.createElement('div');
    row.className = 'row g-3 mb-3 spec-row align-items-center bg-light p-3 rounded border border-dashed';
    row.innerHTML = `
        <div class="col-md-7">
            <label class="form-label fw-bold small text-muted d-md-none">Skill Name</label>
            <input type="text" name="specializations[${specCount}][name]" class="form-agri bg-white" placeholder="e.g. Greenhouse Tech">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold small text-muted d-md-none">Proficiency</label>
            <select name="specializations[${specCount}][level]" class="form-agri bg-white">
                <option value="beginner">Beginner</option>
                <option value="intermediate" selected>Intermediate</option>
                <option value="expert">Expert</option>
            </select>
        </div>
        <div class="col-md-1 text-end mt-3 mt-md-0 d-flex justify-content-end align-items-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-spec rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:36px; height:36px;"><i class="fas fa-trash-alt"></i></button>
        </div>`;
    container.appendChild(row);
    specCount++;
    row.querySelector('.remove-spec').addEventListener('click', () => row.remove());
});
document.querySelectorAll('.remove-spec').forEach(btn => btn.addEventListener('click', () => btn.closest('.spec-row').remove()));
</script>
@endpush
@endsection
