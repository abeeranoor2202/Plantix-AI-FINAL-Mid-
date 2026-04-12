@extends('expert.layouts.app')
@section('title', 'Appointment #' . $appointment->id)
@section('page-title', 'Consultation Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('expert.appointments.index') }}" class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                Consultation #{{ $appointment->id }}
                <span class="badge-agri border border-{{ $appointment->status_badge }} border-opacity-25 bg-{{ $appointment->status_badge }} bg-opacity-10 text-{{ $appointment->status_badge }} ms-2 shadow-sm" style="font-size: 14px; padding: 0.3em 1em;">
                    {{ ucfirst($appointment->status) }}
                </span>
                <span class="badge-agri border border-{{ $appointment->type === 'physical' ? 'success' : 'primary' }} border-opacity-25 bg-{{ $appointment->type === 'physical' ? 'success' : 'primary' }} bg-opacity-10 text-{{ $appointment->type === 'physical' ? 'success' : 'primary' }} shadow-sm" style="font-size: 14px; padding: 0.3em 1em;">
                    {{ strtoupper($appointment->type_label) }}
                </span>
            </h4>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-agri btn-agri-outline shadow-sm px-4 btn-sm" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Print Record
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Main details --}}
    <div class="col-lg-8 d-flex flex-column gap-4">
        <div class="card-agri p-0 h-100 border-0">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="far fa-id-card text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Session Information</h5>
            </div>
            
            <div class="p-4">
                <div class="row g-4 mb-4 pb-4 border-bottom border-dashed">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Farmer Details</div>
                                <div class="fw-bold fs-6 text-dark">{{ $appointment->user->name }}</div>
                                <div class="text-muted small mt-1"><i class="fas fa-envelope me-2 text-primary opacity-50"></i>{{ $appointment->user->email }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Scheduled Time</div>
                                <div class="fw-bold fs-6 text-dark">{{ $appointment->scheduled_at?->format('D, d M Y') }}</div>
                                <div class="text-muted small mt-1"><i class="far fa-clock me-2 text-info opacity-50"></i>{{ $appointment->scheduled_at?->format('h:i A') }} <span class="badge bg-light text-muted border ms-1">{{ $appointment->duration_minutes }} min</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="bg-light p-3 rounded h-100">
                            <div class="text-muted small text-uppercase fw-bold mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Consultation Topic</div>
                            <div class="fw-bold text-dark"><i class="fas fa-quote-left text-muted opacity-25 me-2"></i>{{ $appointment->topic ?? 'General Consultation' }}</div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="bg-light p-3 rounded h-100">
                            <div class="text-muted small text-uppercase fw-bold mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Session Fee & Payment</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold fs-5 text-dark">PKR {{ number_format($appointment->fee) }}</span>
                                <span class="badge-agri border border-{{ $appointment->payment_status === 'paid' ? 'success' : 'warning' }} border-opacity-25 bg-{{ $appointment->payment_status === 'paid' ? 'success' : 'warning' }} bg-opacity-10 text-{{ $appointment->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    <i class="fas fa-{{ $appointment->payment_status === 'paid' ? 'check' : 'clock' }} me-1"></i>{{ ucfirst($appointment->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($appointment->notes)
                    <div class="col-12">
                        <div class="border border-dashed p-4 rounded-3 h-100" style="background-color: #FAFAFA;">
                            <div class="text-muted small text-uppercase fw-bold mb-2 d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fas fa-sticky-note text-warning"></i> Additional Notes from Farmer
                            </div>
                            <div class="text-dark fw-medium small" style="line-height: 1.6;">{{ $appointment->notes }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($appointment->meeting_link)
                    <div class="col-12">
                        <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 p-4 rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <div>
                                <div class="text-primary small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Meeting Room</div>
                                <div class="fw-bold text-dark">Your virtual consultation link is ready</div>
                            </div>
                            <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn-agri btn-agri-primary shadow-sm px-4">
                                <i class="fas fa-video me-2"></i> Join Session
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($appointment->isPhysical())
                    <div class="col-12">
                        <div class="bg-light border p-4 rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <div>
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Physical Location</div>
                                <div class="fw-bold text-dark">{{ $appointment->location ?: trim(($appointment->expert?->profile?->address ? $appointment->expert->profile->address . ', ' : '') . ($appointment->expert?->profile?->city ? $appointment->expert->profile->city . ', ' : '') . ($appointment->expert?->profile?->country ?? '')) }}</div>
                            </div>
                            @if($appointment->expert?->profile?->map_link)
                                <a href="{{ $appointment->expert->profile->map_link }}" target="_blank" class="btn-agri btn-agri-outline shadow-sm px-4">
                                    <i class="fas fa-map-marker-alt me-2"></i>Visit Location
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($appointment->reject_reason)
                    <div class="col-12">
                        <div class="bg-danger bg-opacity-10 border border-danger border-opacity-25 p-4 rounded-3 d-flex align-items-start gap-3">
                            <i class="fas fa-exclamation-triangle text-danger fs-4 mt-1"></i>
                            <div>
                                <div class="text-danger small text-uppercase fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Rejection Reason</div>
                                <div class="text-dark fw-medium">{{ $appointment->reject_reason }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status History --}}
        <div class="card-agri p-0 border-0 flex-grow-1">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-history text-secondary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Timeline Tracker</h5>
            </div>
            <div class="p-0">
                <div class="list-group list-group-flush">
                    @forelse($appointment->statusHistory as $log)
                    <div class="list-group-item px-4 py-3 border-bottom-dashed d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-secondary z-index-1 mt-1" style="width: 32px; height: 32px;">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge border bg-light text-muted">{{ ucfirst($log->from_status) }}</span>
                                <i class="fas fa-long-arrow-alt-right text-muted opacity-50"></i>
                                <span class="badge-agri bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ ucfirst($log->to_status) }}</span>
                                <span class="ms-auto text-muted small" style="font-size: 11px;">
                                    <i class="far fa-clock me-1"></i>{{ $log->changed_at?->format('d M, Y \a\t h:i A') }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2">
                                Updated by <span class="fw-bold text-dark">{{ $log->changedBy?->name ?? 'System' }}</span>
                            </div>
                            @if($log->notes)
                                <div class="text-dark small mt-2 bg-light p-2 rounded border-start border-3 border-secondary" style="font-style: italic;">
                                    "{{ $log->notes }}"
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-info-circle fs-3 text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Timeline Empty</h6>
                        <p class="text-muted small mb-0">No status changes have been recorded yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Action Panel --}}
    <div class="col-lg-4">
        <div class="card-agri p-0 border-0 position-sticky" style="top: 100px;">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-cog text-dark fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Manage Session</h5>
            </div>
            
            <div class="p-4 d-grid gap-3">
                {{-- Accept --}}
                @if($appointment->canBeAccepted())
                <button class="btn-agri btn-agri-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#acceptModal">
                    <i class="fas fa-check-circle me-2"></i> Confirm & Accept
                </button>
                @endif
                
                {{-- Complete --}}
                @if($appointment->canBeCompleted())
                <button class="btn-agri shadow-sm text-white" style="background-color: var(--agri-primary-dark);" data-bs-toggle="modal" data-bs-target="#completeModal">
                    <i class="fas fa-clipboard-check me-2"></i> Mark as Completed
                </button>
                @endif
                
                {{-- Reschedule --}}
                @if($appointment->canBeRescheduled())
                <button class="btn-agri btn-agri-outline shadow-sm text-dark bg-white" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                    <i class="far fa-calendar-plus me-2 text-warning"></i> Propose Reschedule
                </button>
                @endif
                
                {{-- Reject --}}
                @if($appointment->canBeRejected())
                <button class="btn-agri btn-agri-outline shadow-sm text-danger bg-white" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times-circle me-2"></i> Reject Request
                </button>
                @endif

                @if(!$appointment->canBeAccepted() && !$appointment->canBeCompleted() && !$appointment->canBeRescheduled() && !$appointment->canBeRejected())
                    <div class="text-center py-4 bg-light rounded border border-dashed">
                        <i class="fas fa-lock text-muted mb-2 fs-4"></i>
                        <p class="small text-muted mb-0 fw-medium">No actions available for <br>current status.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals matching AgriTech style --}}

{{-- Accept Modal --}}
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.accept', $appointment) }}">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--agri-radius);">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-check-circle text-success me-2"></i>Accept Consultation</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4 pb-3 border-bottom">You are about to accept this consultation request. Once accepted, the farmer will be notified.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Meeting Link <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="url" name="meeting_link" class="form-agri" placeholder="e.g. https://meet.google.com/..." {{ $appointment->type === 'online' ? 'required' : '' }}>
                            <div class="form-text mt-2 small"><i class="fas fa-info-circle me-1"></i>{{ $appointment->type === 'online' ? 'Online consultations require a meeting link before acceptance.' : 'You can add this later if you have not created the link yet.' }}</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn-agri btn-agri-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-agri btn-agri-primary px-4">Accept Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.reject', $appointment) }}">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--agri-radius);">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Reject Consultation</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Are you sure you want to reject this request? Please provide a valid reason for the farmer.</p>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-agri" rows="4" placeholder="Briefly explain why you cannot accept this request..." required minlength="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn-agri btn-agri-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-agri bg-danger text-white px-4 border-0">Decline Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Complete Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.complete', $appointment) }}">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--agri-radius);">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold" style="color: var(--agri-primary-dark);"><i class="fas fa-clipboard-check me-2"></i>Complete Session</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4 pb-3 border-bottom">Marking this as complete signifies that the consultation session has successfully taken place.</p>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Consultation Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" class="form-agri" rows="4" placeholder="Add a quick summary or recommendations discussed during the session..."></textarea>
                        <div class="form-text mt-2 small"><i class="fas fa-globe me-1"></i>Notes may be visible to the farmer.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn-agri btn-agri-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-agri btn-agri-primary px-4 bg-primary text-white border-0">Mark as Done</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reschedule Modal --}}
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.reschedule', $appointment) }}">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--agri-radius);">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold text-warning"><i class="far fa-calendar-plus text-warning me-2"></i>Propose Reschedule</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4 pb-3 border-bottom">If you are unavailable at the currently requested time, you can suggest an alternative slot.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Suggested New Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="proposed_datetime" class="form-agri" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Message to Farmer <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="reason" class="form-agri" rows="3" placeholder="Explain why you're proposing a new time..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn-agri btn-agri-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-agri bg-warning text-dark fw-bold px-4 border-0 shadow-sm"><i class="fas fa-paper-plane me-2"></i>Send Proposal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
