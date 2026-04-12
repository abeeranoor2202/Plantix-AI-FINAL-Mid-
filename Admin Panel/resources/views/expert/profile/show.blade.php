@extends('expert.layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-8 d-flex flex-column gap-4">
        {{-- Profile Overview --}}
        <div class="card-agri p-0 overflow-hidden border-0 bg-white">
            <div class="p-4 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="far fa-user-circle me-2 text-primary"></i>Profile Overview</h5>
                <a href="{{ route('expert.profile.edit') }}" class="btn-agri btn-agri-primary py-1 px-3 d-flex align-items-center gap-2" style="font-size: 13px;">
                    <i class="fas fa-edit"></i> Edit Details
                </a>
            </div>
            
            <div class="p-4">
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4 mb-4 p-4 bg-light rounded text-center text-sm-start border border-dashed">
                    <div class="position-relative">
                        @if($expert->profile_image)
                            <img src="{{ Storage::url($expert->profile_image) }}"
                                 class="rounded-circle shadow-sm border border-3 border-white" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center bg-primary text-white border border-3 border-white"
                                 style="width: 100px; height: 100px; font-size: 2.5rem; font-family: var(--font-heading); font-weight: 700;">
                                {{ strtoupper(substr($expert->user->name, 0, 1)) }}
                            </div>
                        @endif
                        @if($profile && $profile->approval_status === 'approved')
                            <div class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-2 border-white" style="width: 28px; height: 28px;" title="Verified Expert">
                                <i class="fas fa-check" style="font-size: 12px;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-grow-1">
                        <h3 class="fw-bold mb-1 text-dark">{{ $expert->user->name }}</h3>
                        <div class="text-primary fw-medium fs-6 mb-3">{{ $expert->specialty }}</div>
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2">
                            @if($profile)
                                <span class="badge-agri bg-light text-dark border"><i class="fas fa-id-badge me-1 text-muted"></i>{{ ucfirst($profile->account_type) }}</span>
                                <span class="badge-agri bg-{{ $profile->status_badge }} bg-opacity-10 text-{{ $profile->status_badge }} border border-{{ $profile->status_badge }} border-opacity-25">{{ ucfirst($profile->approval_status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    @if($profile?->agency_name)
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Agency / Company</div>
                        <div class="fw-bold text-dark"><i class="fas fa-building text-muted me-2"></i>{{ $profile->agency_name }}</div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Specialization</div>
                        <div class="fw-bold text-dark"><i class="fas fa-seedling text-success me-2"></i>{{ $profile?->specialization ?? $expert->specialty ?? '—' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Experience</div>
                        <div class="fw-bold text-dark"><i class="fas fa-briefcase text-muted me-2"></i>{{ $profile?->experience_years ?? 0 }} Years</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Session Rate</div>
                        <div class="fw-bold text-dark"><i class="fas fa-money-bill-wave text-primary me-2"></i>PKR {{ number_format($expert->hourly_rate) }} <span class="text-muted fw-normal small">/hr</span></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Location</div>
                        <div class="fw-bold text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i>{{ trim(($profile?->address ? $profile->address . ', ' : '') . ($profile?->city ?? 'City') . ', ' . ($profile?->country ?? 'Country')) }}</div>
                        @if($profile?->map_link)
                            <a href="{{ $profile->map_link }}" target="_blank" class="small text-primary text-decoration-none">Open map link</a>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Current Availability</div>
                        <div class="fw-bold {{ $expert->is_available ? 'text-success' : 'text-danger' }}">
                            <i class="fas fa-circle me-2" style="font-size: 10px;"></i>{{ $expert->is_available ? 'Accepting Bookings' : 'Currently Unavailable' }}
                        </div>
                    </div>
                    
                    @if($profile?->certifications)
                    <div class="col-12 mt-4 pt-3 border-top border-dashed">
                        <div class="text-muted small text-uppercase fw-bold mb-2" style="font-size: 11px; letter-spacing: 0.5px;"><i class="fas fa-certificate text-warning me-2"></i>Certifications & Qualifications</div>
                        <div class="text-dark fw-medium bg-light p-3 rounded" style="line-height: 1.6;">{{ $profile->certifications }}</div>
                    </div>
                    @endif
                    
                    @if($expert->bio)
                    <div class="col-12 mt-3 pt-3 border-top border-dashed">
                        <div class="text-muted small text-uppercase fw-bold mb-2" style="font-size: 11px; letter-spacing: 0.5px;"><i class="fas fa-user-edit text-info me-2"></i>Professional Bio</div>
                        <div class="text-dark fw-medium" style="line-height: 1.6;">{{ $expert->bio }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-flex flex-column gap-4">
        {{-- Specializations --}}
        <div class="card-agri p-0 border-0">
            <div class="p-4 bg-light border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tags me-2 text-success"></i>Expertise Focus</h5>
            </div>
            <div class="p-4">
                @forelse($specializations as $spec)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom-dashed">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-hashtag text-muted opacity-50"></i>
                        <span class="fw-bold text-dark">{{ $spec->name }}</span>
                    </div>
                    <span class="badge-agri border {{ match($spec->level) { 'expert' => 'badge-success-agri border-success border-opacity-25', 'intermediate' => 'badge-warning-agri border-warning border-opacity-25', default => 'badge-info-agri border-info border-opacity-25' } }}" style="font-size: 10px; padding: 0.4em 0.8em;">
                        {{ ucfirst($spec->level) }}
                    </span>
                </div>
                @empty
                <div class="text-center text-muted p-4 bg-light rounded border border-dashed">
                    <i class="fas fa-tags fs-3 mb-2 opacity-50 d-block"></i>
                    <p class="small mb-0 fw-medium">No specializations added.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Contact info --}}
        <div class="card-agri p-0 border-0">
            <div class="p-4 bg-light border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-address-book me-2 text-secondary"></i>Contact Info</h5>
            </div>
            <div class="p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="fw-bold text-dark">{{ $expert->user->email }}</div>
                </div>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="fw-bold text-dark">{{ $profile?->contact_phone ?? $expert->user->phone ?? '—' }}</div>
                </div>
                
                @if($profile?->website)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-info" style="width: 36px; height: 36px;">
                        <i class="fas fa-globe"></i>
                    </div>
                    <a href="{{ $profile->website }}" target="_blank" class="fw-bold text-primary text-decoration-none">{{ $profile->website }}</a>
                </div>
                @endif
                
                @if($profile?->linkedin)
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; color: #0A66C2;">
                        <i class="fab fa-linkedin-in"></i>
                    </div>
                    <a href="{{ $profile->linkedin }}" target="_blank" class="fw-bold text-primary text-decoration-none">LinkedIn Profile</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
