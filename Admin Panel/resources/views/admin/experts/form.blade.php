@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($expert) && $expert->exists;
    $profile = $expert->profile ?? null;
    $expertImage = $expert->profile_image ?? null;
    $expertImageUrl = $expertImage ? (filter_var($expertImage, FILTER_VALIDATE_URL) ? $expertImage : asset('storage/' . ltrim($expertImage, '/'))) : asset('images/placeholder.png');
    $totalAppointments = $isEdit ? ($expert->appointments()->count()) : 0;
@endphp
<div class="container-fluid" style="padding-top: 24px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Experts</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $isEdit ? 'Edit Expert' : 'Add Expert' }}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $isEdit ? 'Edit Expert Account' : 'Create Expert Account' }}</h1>
    </div>

    @if($isEdit)
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card-agri" style="padding: 24px; padding-left: 32px; border-left: 6px solid #3b82f6;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Total Appointments</p>
                            <h2 style="font-size: 32px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">{{ $totalAppointments }}</h2>
                        </div>
                        <div style="width: 56px; height: 56px; border-radius: 14px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.experts.update', $expert->id) : route('admin.experts.store') }}" enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 40px;">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px; z-index: 10;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    Processing
                </div>

                <div style="margin-bottom: 40px;">
                    <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-user-edit"></i> Account Details
                    </h4>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Full Name</label>
                            <input type="text" name="name" class="form-agri" value="{{ old('name', $expert->user->name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="agri-label">Email</label>
                            <input type="email" name="email" class="form-agri" value="{{ old('email', $expert->user->email ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="agri-label">Phone</label>
                            <input type="text" name="phone" class="form-agri" value="{{ old('phone', $expert->user->phone ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="agri-label">Primary Specialty</label>
                            <input type="text" name="specialty" class="form-agri" value="{{ old('specialty', $expert->specialty ?? $profile?->specialization ?? '') }}">
                        </div>

                        @unless($isEdit)
                        <div class="col-md-6">
                            <label class="agri-label">Password</label>
                            <input type="password" name="password" class="form-agri" required>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-agri" required>
                        </div>
                        @endunless

                        <div class="col-12">
                            <label class="agri-label">Bio</label>
                            <textarea name="bio" class="form-agri" rows="4">{{ old('bio', $expert->bio ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-map-marker-alt"></i> Customer Address
                    </h4>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="agri-label">Account Type</label>
                            <select name="account_type" class="form-agri">
                            <option value="individual" @selected(old('account_type', $profile?->account_type ?? 'individual') === 'individual')>Individual</option>
                            <option value="agency" @selected(old('account_type', $profile?->account_type ?? '') === 'agency')>Agency</option>
                        </select>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Agency Name</label>
                            <input type="text" name="agency_name" class="form-agri" value="{{ old('agency_name', $profile?->agency_name ?? '') }}" placeholder="Agency / organization name">
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Experience Years</label>
                            <input type="number" min="0" max="60" name="experience_years" class="form-agri" value="{{ old('experience_years', $profile?->experience_years ?? 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">City</label>
                            <input type="text" name="city" class="form-agri" value="{{ old('city', $profile?->city ?? '') }}" placeholder="City">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Country</label>
                            <input type="text" name="country" class="form-agri" value="{{ old('country', $profile?->country ?? 'Pakistan') }}" placeholder="Country">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-agri" value="{{ old('contact_phone', $profile?->contact_phone ?? '') }}" placeholder="Public contact phone">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Website</label>
                            <input type="url" name="website" class="form-agri" value="{{ old('website', $profile?->website ?? '') }}" placeholder="https://example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">LinkedIn</label>
                            <input type="url" name="linkedin" class="form-agri" value="{{ old('linkedin', $profile?->linkedin ?? '') }}" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Certifications</label>
                            <input type="text" name="certifications" class="form-agri" value="{{ old('certifications', $profile?->certifications ?? '') }}" placeholder="e.g. BSc Agriculture, PhD Plant Pathology">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Image</h4>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div class="expert_image" style="width: 80px; height: 80px; border-radius: 12px; background: white; border: 1px solid var(--agri-border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="{{ $expertImageUrl }}" onerror="this.onerror=null;this.src='{{ asset('images/placeholder.png') }}';" alt="image">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="profile_image" onChange="handleExpertImageSelect(event)" class="form-control" style="font-size: 13px;" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="row g-4" style="margin-bottom: 24px;">
                    <div class="col-md-4">
                        <label class="agri-label">Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="hourly_rate" class="form-agri" value="{{ old('hourly_rate', $expert->hourly_rate ?? 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="agri-label">Consultation Price</label>
                        <input type="number" step="0.01" min="0" name="consultation_price" class="form-agri" value="{{ old('consultation_price', $expert->consultation_price ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="agri-label">Consultation Duration (minutes)</label>
                        <input type="number" min="15" name="consultation_duration_minutes" class="form-agri" value="{{ old('consultation_duration_minutes', $expert->consultation_duration_minutes ?? 60) }}">
                    </div>
                </div>

                <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 50px; font-size: 16px;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> {{ $isEdit ? 'Update Changes' : 'Create Expert' }}
                    </button>
                    <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
             <div class="card-agri" style="padding: 24px; background: #fffbeb; border-top: 4px solid #f59e0b;">
                 <h4 style="font-size: 16px; font-weight: 700; color: #92400e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                     <i class="fas fa-shield-alt"></i> Security & Status
                 </h4>

                 <div style="margin-bottom: 24px;">
                     <label class="agri-label" style="color:#92400e;">Account Status</label>
                     <select name="approval_status" class="form-agri" style="background: #fff;">
                        @foreach(['pending','under_review','approved','rejected','suspended','inactive'] as $status)
                            <option value="{{ $status }}" @selected(old('approval_status', $expert->status ?? 'pending') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                 </div>

                 <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(146, 64, 14, 0.1); padding-top: 16px;">
                     <div>
                         <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Available for bookings</span>
                         <p style="font-size: 12px; color: #b45309; margin: 0;">Toggle expert availability.</p>
                     </div>
                     <div class="form-check form-switch" style="padding: 0; margin: 0;">
                         <input type="checkbox" name="is_available" value="1" class="user_active" style="width: 44px; height: 22px; cursor: pointer; accent-color: var(--agri-primary);" @checked(old('is_available', $expert->is_available ?? true))>
                     </div>
                 </div>

                 @if($isEdit)
                    <div style="margin-bottom: 24px; padding-top: 16px; border-top: 1px solid rgba(146, 64, 14, 0.1);">
                        <form method="POST" action="{{ route('admin.experts.destroy', $expert->id) }}" onsubmit="return confirm('Archive this expert?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-agri" style="width: 100%; height: 42px; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 14px; font-weight: 700; justify-content: center;">
                                <i class="fas fa-trash" style="margin-right: 8px;"></i> Delete Expert
                            </button>
                        </form>
                    </div>
                 @endif
             </div>
        </div>
    </div>

    </form>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
</style>

@endsection

@section('scripts')
<script>
function handleExpertImageSelect(evt) {
    var f = evt.target.files[0];
    if (!f) return;

    var reader = new FileReader();
    reader.onload = function(e) {
        $('.expert_image').html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + e.target.result + '" alt="image">');
    };
    reader.readAsDataURL(f);
}
</script>
@endsection