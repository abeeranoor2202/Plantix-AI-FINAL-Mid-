<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expert Sign Up — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .reg-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 2.5rem;
        }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand i { font-size: 3rem; color: #2e7d32; }
        .brand h4 { font-weight: 700; margin-top: .5rem; }
        .badge-expert {
            background: #e8f5e9; color: #2e7d32;
            border: 1px solid #c8e6c9; border-radius: .5rem;
            font-size: .75rem; padding: .3rem .7rem; display: inline-block;
            margin-bottom: 1rem;
        }
        .section-heading {
            font-size: .7rem; font-weight: 700; letter-spacing: .08em;
            color: #9e9e9e; text-transform: uppercase;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: .4rem; margin-bottom: 1rem; margin-top: 1.5rem;
        }
        .btn-expert { background: #1b5e20; color: #fff; border: none; }
        .btn-expert:hover { background: #134418; color: #fff; }
        .type-card {
            border: 2px solid #e0e0e0; border-radius: .75rem;
            padding: 1rem; cursor: pointer; transition: all .2s;
            text-align: center; user-select: none;
        }
        .type-card:hover { border-color: #2e7d32; background: #f1f8e9; }
        .type-card.selected { border-color: #2e7d32; background: #e8f5e9; }
        .type-card i { font-size: 2rem; color: #2e7d32; }
        #agencyNameRow { display: none; }
    </style>
</head>
<body>
<div class="container py-2">
<div class="reg-card">

    {{-- Brand --}}
    <div class="brand">
        <i class="bi bi-person-badge"></i>
        <h4>Join as an Agricultural Expert</h4>
        <span class="badge-expert"><i class="bi bi-shield-check me-1"></i>Verified Experts Only</span>
        <p class="text-muted small mb-0">Submit your application and get connecting with farmers after admin approval.</p>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('expert.register') }}" id="regForm" enctype="multipart/form-data">
        @csrf

        {{-- ── Account Type ──────────────────────────────────────────────── --}}
        <p class="section-heading"><i class="bi bi-person-workspace me-1"></i>Account Type</p>
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="type-card {{ old('account_type', 'individual') === 'individual' ? 'selected' : '' }}"
                     id="typeIndividual" onclick="selectType('individual')">
                    <i class="bi bi-person-circle"></i>
                    <div class="fw-semibold mt-1">Individual Expert</div>
                    <small class="text-muted">Solo agricultural consultant</small>
                </div>
            </div>
            <div class="col-6">
                <div class="type-card {{ old('account_type') === 'agency' ? 'selected' : '' }}"
                     id="typeAgency" onclick="selectType('agency')">
                    <i class="bi bi-building"></i>
                    <div class="fw-semibold mt-1">Agency / Organisation</div>
                    <small class="text-muted">Team or consulting firm</small>
                </div>
            </div>
        </div>
        <input type="hidden" name="account_type" id="accountTypeInput" value="{{ old('account_type', 'individual') }}">

        {{-- Agency name (shown only for agency type) --}}
        <div class="row" id="agencyNameRow">
            <div class="col-12 mb-3">
                <label class="form-label fw-semibold">Agency / Organisation Name <span class="text-danger">*</span></label>
                <input type="text" name="agency_name" class="form-control @error('agency_name') is-invalid @enderror"
                       value="{{ old('agency_name') }}" placeholder="Green Agri Consultants Ltd.">
                @error('agency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- ── Personal Information ────────────────────────────────────────── --}}
        <p class="section-heading"><i class="bi bi-person me-1"></i>Personal Information</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Dr. John Smith" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="expert@example.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}" placeholder="+92 300 1234567" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Contact Phone (public) </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                    <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                           value="{{ old('contact_phone') }}" placeholder="Same as above, or different">
                    @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Shown publicly on your expert profile.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Min. 8 characters with letters & numbers" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>
        </div>

        {{-- ── Professional Details ──────────────────────────────────────── --}}
        <p class="section-heading mt-4"><i class="bi bi-award me-1"></i>Professional Details</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Primary Specialty <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tree"></i></span>
                    <input type="text" name="specialty" class="form-control @error('specialty') is-invalid @enderror"
                           value="{{ old('specialty') }}" placeholder="e.g. Crop Disease Management" required>
                    @error('specialty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Main area of expertise (used in search).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Additional Specializations</label>
                <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror"
                       value="{{ old('specialization') }}" placeholder="e.g. Soil Science, Irrigation, Pest Control">
                @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Comma-separated tags shown on your profile.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Years of Experience <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                    <input type="number" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror"
                           value="{{ old('experience_years', 0) }}" min="0" max="60" required>
                    @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                       value="{{ old('city') }}" placeholder="Lahore" required>
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                       value="{{ old('country', 'Pakistan') }}" placeholder="Pakistan" required>
                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Consultation Fee (PKR)</label>
                <div class="input-group">
                    <span class="input-group-text">₨</span>
                    <input type="number" name="consultation_price" class="form-control @error('consultation_price') is-invalid @enderror"
                           value="{{ old('consultation_price') }}" min="0" step="1" placeholder="e.g. 1500">
                    @error('consultation_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Leave blank to set later from your dashboard.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Certifications / Qualifications</label>
                <input type="text" name="certifications" class="form-control @error('certifications') is-invalid @enderror"
                       value="{{ old('certifications') }}" placeholder="e.g. BSc Agriculture, PhD Plant Pathology">
                @error('certifications')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Professional Bio <span class="text-danger">*</span></label>
                <textarea name="bio" rows="4"
                          class="form-control @error('bio') is-invalid @enderror"
                          placeholder="Describe your background, expertise, and how you can help farmers… (min. 50 characters)"
                          required>{{ old('bio') }}</textarea>
                @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">This appears on your public expert profile.</div>
            </div>
        </div>

        {{-- ── Online Presence (optional) ──────────────────────────────────── --}}
        <p class="section-heading mt-4"><i class="bi bi-globe me-1"></i>Online Presence <small class="text-muted fw-normal">(optional)</small></p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Website</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe2"></i></span>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                           value="{{ old('website') }}" placeholder="https://yoursite.com">
                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">LinkedIn Profile</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-linkedin"></i></span>
                    <input type="url" name="linkedin" class="form-control @error('linkedin') is-invalid @enderror"
                           value="{{ old('linkedin') }}" placeholder="https://linkedin.com/in/yourprofile">
                    @error('linkedin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ── Supporting Documents ─────────────────────────────────────────── --}}
        <p class="section-heading mt-4"><i class="bi bi-folder2-open me-1"></i>Supporting Documents <small class="text-muted fw-normal">(optional but recommended)</small></p>
        <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Uploading your credentials and a valid ID speeds up the review process.
            Accepted formats: <strong>PDF, JPG, PNG</strong> — max <strong>5 MB</strong> each.
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Certifications / Degree Certificate</label>
                <input type="file" name="certifications_file"
                       class="form-control @error('certifications_file') is-invalid @enderror"
                       accept=".pdf,.jpg,.jpeg,.png">
                @error('certifications_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">BSc/PhD certificate, professional qualification, etc.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Government-Issued ID</label>
                <input type="file" name="id_document"
                       class="form-control @error('id_document') is-invalid @enderror"
                       accept=".pdf,.jpg,.jpeg,.png">
                @error('id_document')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">National ID card, passport, or driving licence.</div>
            </div>
        </div>

        {{-- ── Terms & Submit ──────────────────────────────────────────────── --}}
        <div class="mt-4">
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">
                    I confirm that the information provided is accurate and I agree to Plantix AI's
                    <a href="#" class="text-success">Terms of Service</a>.
                </label>
            </div>
            <button type="submit" class="btn btn-expert w-100 py-2 fw-semibold fs-6">
                <i class="bi bi-send me-1"></i>Submit Application
            </button>
        </div>
    </form>

    <div class="text-center mt-3">
        <span class="text-muted small">Already registered?</span>
        <a href="{{ route('expert.login') }}" class="text-success small ms-1">Sign in</a>
        <span class="text-muted small mx-2">|</span>
        <a href="{{ route('home') }}" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectType(type) {
    document.getElementById('accountTypeInput').value = type;
    document.getElementById('typeIndividual').classList.toggle('selected', type === 'individual');
    document.getElementById('typeAgency').classList.toggle('selected', type === 'agency');
    document.getElementById('agencyNameRow').style.display = type === 'agency' ? '' : 'none';
}

// Restore state on page load (e.g. after validation error)
(function () {
    const type = document.getElementById('accountTypeInput').value;
    selectType(type);
})();
</script>
</body>
</html>
