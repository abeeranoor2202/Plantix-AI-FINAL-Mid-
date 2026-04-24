@extends('expert.layouts.app')
@section('title', 'Appointment #' . $appointment->id)

@section('content')
@php
    $statusVariant = match($appointment->status) {
        'completed', 'confirmed' => 'success',
        'pending_payment', 'pending_expert_approval', 'reschedule_requested', 'rescheduled', 'pending' => 'warning',
        'rejected', 'cancelled' => 'danger',
        default => 'secondary',
    };

    $canEdit = !in_array($appointment->status, ['completed', 'cancelled', 'rejected'], true);
    $canDelete = in_array($appointment->status, ['pending_expert_approval', 'pending'], true);

    $showActions = $appointment->canBeAccepted() || $appointment->canBeCompleted() || $appointment->canBeRescheduled() || $appointment->canBeRejected();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('expert.appointments.index') }}" class="btn btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Back">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <div>
            <h4 class="mb-1 fw-bold text-dark">Consultation #{{ $appointment->id }}</h4>
            <div class="d-flex gap-2 flex-wrap">
                <x-badge :variant="$statusVariant">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</x-badge>
                <x-badge :variant="$appointment->type === 'physical' ? 'success' : 'info'">{{ strtoupper($appointment->type_label) }}</x-badge>
            </div>
        </div>
    </div>

    <div class="d-inline-flex gap-2 align-items-center">
        <a href="{{ route('expert.appointments.show', $appointment) }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="View">
            <i class="fas fa-eye text-primary"></i>
        </a>

        @if($canEdit)
            <a href="{{ route('expert.appointments.edit', $appointment) }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Edit">
                <i class="fas fa-pen text-success"></i>
            </a>
        @else
            <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Edit unavailable" disabled>
                <i class="fas fa-pen text-muted"></i>
            </button>
        @endif

        @if($canDelete)
            <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#deleteAppointmentModal" title="Delete">
                <i class="fas fa-trash text-danger"></i>
            </button>
        @else
            <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Delete unavailable" disabled>
                <i class="fas fa-trash text-muted"></i>
            </button>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8 d-flex flex-column gap-4">
        <x-card>
            <div class="p-3 border-bottom bg-light">
                <h5 class="mb-0 fw-bold text-dark">Session Information</h5>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Farmer</div>
                                    <div class="fw-bold text-dark">{{ $appointment->user->name }}</div>
                                    <div class="small text-muted">{{ $appointment->user->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-3">
                                    <i class="far fa-calendar-alt text-info"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Scheduled Time</div>
                                    <div class="fw-bold text-dark">{{ $appointment->scheduled_at?->format('D, d M Y') }}</div>
                                    <div class="small text-muted">{{ $appointment->scheduled_at?->format('h:i A') }} ({{ $appointment->duration_minutes }} min)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="small text-muted">Consultation Topic</div>
                            <div class="fw-bold text-dark">{{ $appointment->topic ?? 'General Consultation' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="small text-muted">Session Fee & Payment</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark">PKR {{ number_format($appointment->fee) }}</span>
                                <x-badge :variant="$appointment->payment_status === 'paid' ? 'success' : 'warning'">{{ ucfirst($appointment->payment_status) }}</x-badge>
                            </div>
                        </div>
                    </div>

                    @if($appointment->notes)
                        <div class="col-12">
                            <div class="border rounded p-3 bg-white">
                                <div class="small text-muted mb-1">Additional Notes from Farmer</div>
                                <div class="text-dark">{{ $appointment->notes }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->meeting_link)
                        <div class="col-12">
                            <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <div class="fw-bold text-dark">Meeting Room</div>
                                    <div class="small text-muted">Your virtual consultation link is ready.</div>
                                </div>
                                <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn-agri btn-agri-primary">
                                    <i class="fas fa-video me-1"></i> Join Session
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($appointment->isPhysical())
                        <div class="col-12">
                            <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <div class="small text-muted">Physical Location</div>
                                    <div class="fw-bold text-dark">{{ $appointment->location ?: trim(($appointment->expert?->profile?->address ? $appointment->expert->profile->address . ', ' : '') . ($appointment->expert?->profile?->city ? $appointment->expert->profile->city . ', ' : '') . ($appointment->expert?->profile?->country ?? '')) }}</div>
                                </div>
                                @if($appointment->expert?->profile?->map_link)
                                    <a href="{{ $appointment->expert->profile->map_link }}" target="_blank" class="btn-agri btn-agri-outline">Visit Location</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($appointment->reject_reason)
                        <div class="col-12">
                            <div class="border rounded p-3 bg-white">
                                <div class="small text-muted mb-1">Rejection Reason</div>
                                <div class="text-danger fw-semibold">{{ $appointment->reject_reason }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="p-3 border-bottom bg-light">
                <h5 class="mb-0 fw-bold text-dark">Timeline Tracker</h5>
            </div>
            <div class="p-0">
                <div class="list-group list-group-flush">
                    @forelse($appointment->statusHistory as $log)
                        <div class="list-group-item px-4 py-3 d-flex align-items-start gap-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-2 mt-1">
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <x-badge variant="secondary">{{ ucfirst($log->from_status) }}</x-badge>
                                    <i class="fas fa-long-arrow-alt-right text-muted"></i>
                                    <x-badge variant="success">{{ ucfirst($log->to_status) }}</x-badge>
                                    <span class="small text-muted ms-md-auto">{{ $log->changed_at?->format('d M, Y \a\t h:i A') }}</span>
                                </div>
                                <div class="small text-muted">Updated by <span class="fw-semibold text-dark">{{ $log->changedBy?->name ?? 'System' }}</span></div>
                                @if($log->notes)
                                    <div class="small text-muted mt-2">{{ $log->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-info-circle fs-4 d-block mb-2"></i>
                            No status changes have been recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>

    <div class="col-lg-4">
        <x-card>
            <div class="p-3 border-bottom bg-light">
                <h5 class="mb-0 fw-bold text-dark">Manage Session</h5>
            </div>
            <div class="p-3 d-grid gap-2">
                @if($appointment->canBeAccepted())
                    <button class="btn-agri btn-agri-primary" data-toggle="modal" data-target="#acceptModal">
                        <i class="fas fa-check-circle me-1"></i> Confirm & Accept
                    </button>
                @endif

                @if($appointment->canBeCompleted())
                    <button class="btn-agri btn-agri-primary" data-toggle="modal" data-target="#completeModal">
                        <i class="fas fa-clipboard-check me-1"></i> Mark as Completed
                    </button>
                @endif

                @if($appointment->canBeRescheduled())
                    <button class="btn-agri btn-agri-outline" data-toggle="modal" data-target="#rescheduleModal">
                        <i class="far fa-calendar-plus me-1"></i> Propose Reschedule
                    </button>
                @endif

                @if($appointment->canBeRejected())
                    <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times-circle me-1"></i> Reject Request
                    </button>
                @endif

                @unless($showActions)
                    <div class="py-4 text-center text-muted">
                        <i class="fas fa-info-circle fs-4 d-block mb-2"></i>
                        No actions available for this status.
                    </div>
                @endunless
            </div>
        </x-card>
    </div>
</div>

@if($canDelete)
    <div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('expert.appointments.delete', $appointment) }}" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Appointment</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted">Are you sure you want to delete appointment #{{ $appointment->id }}? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.accept', $appointment) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Accept Consultation</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">You are about to accept this consultation request.</p>
                <div>
                    <label class="form-label text-muted small">Meeting Link {{ $appointment->type === 'online' ? '(required)' : '(optional)' }}</label>
                    <input type="url" name="meeting_link" class="form-agri" placeholder="https://meet.example.com" {{ $appointment->type === 'online' ? 'required' : '' }}>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-agri btn-agri-primary">Accept Request</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.reject', $appointment) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Reject Consultation</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label text-muted small">Reason for Rejection</label>
                <textarea name="reason" class="form-agri" rows="4" required minlength="10" placeholder="Provide a clear reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Decline Request</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.complete', $appointment) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Complete Session</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label text-muted small">Consultation Notes (optional)</label>
                <textarea name="notes" class="form-agri" rows="4" placeholder="Session summary..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-agri btn-agri-primary">Mark as Done</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expert.appointments.reschedule', $appointment) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Propose Reschedule</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Suggested New Date & Time</label>
                    <input type="datetime-local" name="proposed_datetime" class="form-agri" required min="{{ now()->addHour()->format('Y-m-d\\TH:i') }}">
                </div>
                <div>
                    <label class="form-label text-muted small">Message to Farmer (optional)</label>
                    <textarea name="reason" class="form-agri" rows="3" placeholder="Explain why you are proposing a new time."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-agri btn-agri-primary">Send Proposal</button>
            </div>
        </form>
    </div>
</div>
@endsection
