@extends('expert.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Expert Overview')

@section('content')
<div class="row g-4 mb-5">
    {{-- Stats cards --}}
    <div class="col-xl-3 col-sm-6">
        <div class="card-agri hover-lift p-4 h-100">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">All Appointments</h6>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="far fa-calendar-check fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['total'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Total Consultations</p>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card-agri hover-lift p-4 h-100 border border-warning border-opacity-25" style="border-left: 4px solid var(--agri-secondary) !important;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Requests</h6>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-hourglass-half fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['pending'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small"><span class="text-warning fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Needs Response</span></p>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card-agri hover-lift p-4 h-100 border border-info border-opacity-25" style="border-left: 4px solid #3B82F6 !important;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Upcoming Sessions</h6>
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="far fa-calendar-alt fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['upcoming'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Confirmed Bookings</p>
        </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
        <div class="card-agri hover-lift p-4 h-100 border border-success border-opacity-25" style="border-left: 4px solid var(--agri-primary) !important;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Completed</h6>
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-check-double fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['completed'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small"><span class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>Successful Consults</span></p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Pending appointment requests --}}
    <div class="col-lg-7">
        <div class="card-agri p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark m-0"><i class="fas fa-inbox text-warning me-2"></i>New Appointment Requests</h5>
                <a href="{{ route('expert.appointments.index') }}" class="btn-agri btn-agri-outline py-1 px-3" style="font-size: 13px;">View All</a>
            </div>
            
            <div class="list-group list-group-flush">
                @forelse($requested->items() as $appt)
                <div class="list-group-item px-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; font-size: 1.2rem;">
                            {{ strtoupper(substr($appt->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-6">{{ $appt->user->name }}</div>
                            <div class="text-muted small mt-1">
                                <span class="d-inline-flex align-items-center bg-light px-2 py-1 rounded me-2">
                                    <i class="far fa-calendar-alt me-1"></i>{{ $appt->scheduled_at?->format('d M Y, H:i') }}
                                </span>
                                @if($appt->topic)
                                    <span class="d-inline-flex align-items-center"><i class="fas fa-tag me-1 text-muted"></i>{{ Str::limit($appt->topic, 35) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ms-3">
                        <a href="{{ route('expert.appointments.show', $appt) }}"
                           class="btn btn-sm btn-light border shadow-sm text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Review Request">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fs-1 text-success opacity-50 mb-3 d-block"></i>
                    <h6 class="fw-bold text-dark">No Pending Requests</h6>
                    <p class="text-muted small">You are all caught up for now!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Profile summary --}}
    <div class="col-lg-5 d-flex flex-column gap-4">
        <div class="card-agri p-0 h-100 overflow-hidden d-flex flex-column">
            <div class="p-4 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>My Profile Summary</h6>
                <a href="{{ route('expert.profile.edit') }}" class="btn-agri btn-agri-outline py-1 px-3" style="font-size: 12px;">Edit Details</a>
            </div>
            <div class="p-4 flex-grow-1">
                <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom border-dashed">
                    @if($expert->avatar)
                        <img src="{{ Storage::url($expert->avatar) }}"
                             class="rounded-circle shadow-sm border border-3 border-white" style="width: 80px; height: 80px; object-fit: cover">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm border border-3 border-white"
                             style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700; font-family: var(--font-heading);">
                            {{ strtoupper(substr($expert->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $expert->user->name }}</h4>
                        <div class="text-primary fw-medium small mb-2">{{ $expert->specialty }}</div>
                        @if($expert->profile)
                            <span class="badge-agri bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="letter-spacing: 0.5px;">
                                {{ ucfirst($expert->profile->account_type) }} Account
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center h-100">
                            <i class="fas fa-map-marker-alt text-muted mb-2 fs-5"></i>
                            <div class="small fw-bold text-dark">{{ $expert->profile?->city ?? 'N/A' }}</div>
                            <div class="small text-muted" style="font-size: 11px;">{{ $expert->profile?->country ?? 'Location' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center h-100">
                            <i class="fas fa-briefcase text-muted mb-2 fs-5"></i>
                            <div class="small fw-bold text-dark">{{ $expert->profile?->experience_years ?? 0 }} Years</div>
                            <div class="small text-muted" style="font-size: 11px;">Experience</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center h-100">
                            <i class="fas fa-money-bill-wave text-muted mb-2 fs-5"></i>
                            <div class="small fw-bold text-dark">PKR {{ number_format($expert->hourly_rate) }}</div>
                            <div class="small text-muted" style="font-size: 11px;">Per Session</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3 text-center h-100 border {{ $expert->is_available ? 'border-success border-opacity-25' : 'border-danger border-opacity-25' }}">
                            <i class="fas fa-circle {{ $expert->is_available ? 'text-success' : 'text-danger' }} mb-2 fs-5"></i>
                            <div class="small fw-bold {{ $expert->is_available ? 'text-success' : 'text-danger' }}">{{ $expert->is_available ? 'Available' : 'Busy' }}</div>
                            <div class="small text-muted" style="font-size: 11px;">Current Status</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
