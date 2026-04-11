@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($expert) && $expert->exists;
    $profile = $expert->profile ?? null;
@endphp
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Experts</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $isEdit ? 'Edit Expert' : 'Add Expert' }}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $isEdit ? 'Edit Expert' : 'Add Expert' }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">{{ $isEdit ? 'Update the expert account and profile details.' : 'Create a new expert account with profile details.' }}</p>
        </div>

        @if($isEdit)
            <form method="POST" action="{{ route('admin.experts.destroy', $expert->id) }}" onsubmit="return confirm('Archive this expert?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-agri" style="background: #fef2f2; color: #ef4444; border: none; height: 44px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
                    <i class="fas fa-trash"></i> Delete Expert
                </button>
            </form>
        @endif
    </div>

    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">{{ $isEdit ? 'Expert Details' : 'New Expert' }}</h4>
            <span class="badge rounded-pill {{ $isEdit ? ($expert->status === 'approved' ? 'bg-success' : 'bg-warning text-dark') : 'bg-secondary' }}">
                {{ $isEdit ? ucfirst(str_replace('_', ' ', $expert->status ?? 'pending')) : 'Draft' }}
            </span>
        </div>

        <div style="padding: 24px;">
            <form method="POST" action="{{ $isEdit ? route('admin.experts.update', $expert->id) : route('admin.experts.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Full Name</label>
                        <input type="text" name="name" class="form-agri" value="{{ old('name', $expert->user->name ?? '') }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Email</label>
                        <input type="email" name="email" class="form-agri" value="{{ old('email', $expert->user->email ?? '') }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Phone</label>
                        <input type="text" name="phone" class="form-agri" value="{{ old('phone', $expert->user->phone ?? '') }}" required>
                    </div>
                    @unless($isEdit)
                        <div class="col-lg-6">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Password</label>
                            <input type="password" name="password" class="form-agri" required>
                        </div>
                        <div class="col-lg-6">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-agri" required>
                        </div>
                    @endunless
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Primary Specialty</label>
                        <input type="text" name="specialty" class="form-agri" value="{{ old('specialty', $expert->specialty ?? $profile->specialization ?? '') }}">
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="hourly_rate" class="form-agri" value="{{ old('hourly_rate', $expert->hourly_rate ?? 0) }}">
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Consultation Price</label>
                        <input type="number" step="0.01" min="0" name="consultation_price" class="form-agri" value="{{ old('consultation_price', $expert->consultation_price ?? '') }}">
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Consultation Duration (minutes)</label>
                        <input type="number" min="15" name="consultation_duration_minutes" class="form-agri" value="{{ old('consultation_duration_minutes', $expert->consultation_duration_minutes ?? 60) }}">
                    </div>
                    <div class="col-12">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Bio</label>
                        <textarea name="bio" class="form-agri" rows="4">{{ old('bio', $expert->bio ?? '') }}</textarea>
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Account Type</label>
                        <select name="account_type" class="form-agri">
                            <option value="individual" @selected(old('account_type', $profile?->account_type ?? 'individual') === 'individual')>Individual</option>
                            <option value="agency" @selected(old('account_type', $profile?->account_type ?? '') === 'agency')>Agency</option>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Agency Name</label>
                        <input type="text" name="agency_name" class="form-agri" value="{{ old('agency_name', $profile?->agency_name ?? '') }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Experience Years</label>
                        <input type="number" min="0" max="60" name="experience_years" class="form-agri" value="{{ old('experience_years', $profile?->experience_years ?? 0) }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">City</label>
                        <input type="text" name="city" class="form-agri" value="{{ old('city', $profile?->city ?? '') }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Country</label>
                        <input type="text" name="country" class="form-agri" value="{{ old('country', $profile?->country ?? 'Pakistan') }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-agri" value="{{ old('contact_phone', $profile?->contact_phone ?? '') }}">
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Website</label>
                        <input type="url" name="website" class="form-agri" value="{{ old('website', $profile?->website ?? '') }}">
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">LinkedIn</label>
                        <input type="url" name="linkedin" class="form-agri" value="{{ old('linkedin', $profile?->linkedin ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Certifications</label>
                        <textarea name="certifications" class="form-agri" rows="3">{{ old('certifications', $profile?->certifications ?? '') }}</textarea>
                    </div>
                    <div class="col-lg-4" style="display: flex; align-items: end;">
                        <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: 600; color: var(--agri-text-heading);">
                            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $expert->is_available ?? true))>
                            Available for bookings
                        </label>
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
                        <select name="approval_status" class="form-agri">
                            @foreach(['pending','under_review','approved','rejected','suspended','inactive'] as $status)
                                <option value="{{ $status }}" @selected(old('approval_status', $expert->status ?? 'pending') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--agri-border); gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; height: 44px; display: inline-flex; align-items: center;">Back</a>
                    <button type="submit" class="btn-agri btn-agri-primary" style="height: 44px; border: none; font-weight: 700;">{{ $isEdit ? 'Save Changes' : 'Create Expert' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection