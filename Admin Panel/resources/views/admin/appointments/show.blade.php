@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.appointments.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Appointments</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Appointment #{{ $appointment->id }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Appointment #{{ $appointment->id }}</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Full consultation details and lifecycle management.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn-agri btn-agri-primary" style="text-decoration: none;">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i>Edit
                </a>
                <a href="{{ route('admin.appointments.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- ── Admin Approval Banner ─────────────────────────────────────────────── --}}
    @if($appointment->status === \App\Models\Appointment::STATUS_PENDING_ADMIN_APPROVAL)
    <div style="background: #fffbeb; border: 2px solid #fcd34d; border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; display: flex; align-items: flex-start; gap: 20px;">
        <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fas fa-shield-alt" style="color: #d97706; font-size: 20px;"></i>
        </div>
        <div style="flex: 1;">
            <h5 style="margin: 0 0 4px 0; font-weight: 800; color: #92400e; font-size: 16px;">
                <i class="fas fa-check-circle me-2" style="color: #059669;"></i>Payment Received — Awaiting Your Approval
            </h5>
            <p style="margin: 0 0 16px 0; color: #78350f; font-size: 14px;">
                The customer <strong>{{ $appointment->user->name ?? 'N/A' }}</strong> has paid
                <strong>{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($appointment->fee, 2) }}</strong>.
                Review the booking and approve to forward it to
                <strong>{{ optional($appointment->expert)->user->name ?? 'the expert' }}</strong>.
                The expert will only be notified after you approve.
            </p>
            <form method="POST" action="{{ route('admin.appointments.approve', $appointment->id) }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                @csrf
                <div style="flex: 1; min-width: 240px;">
                    <label style="font-size: 12px; font-weight: 700; color: #92400e; display: block; margin-bottom: 6px;">Admin Notes (optional)</label>
                    <input type="text" name="admin_notes" class="form-control" placeholder="e.g. Verified payment, forwarding to expert" style="border-radius: 10px; border: 1px solid #fcd34d; background: white;">
                </div>
                <button type="submit" class="btn btn-warning" style="font-weight: 700; padding: 10px 24px; border-radius: 10px; white-space: nowrap;">
                    <i class="fas fa-paper-plane me-2"></i> Approve & Forward to Expert
                </button>
            </form>
            <div style="margin-top: 12px;">
                <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#cancelModal" style="border-radius: 8px;">
                    <i class="fas fa-times me-1"></i> Reject & Refund Customer
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- ── Left column ──────────────────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card-agri" style="text-align: center; padding: 40px 24px; margin-bottom: 20px;">
                @php
                    $statusLabel = $appointment->display_status_label;
                    $statusColor = match($appointment->status) {
                        'confirmed'                => '#059669',
                        'completed'                => '#2563eb',
                        'cancelled', 'rejected'    => '#dc2626',
                        'pending_admin_approval'   => '#d97706',
                        'pending_expert_approval'  => '#7c3aed',
                        default                    => '#6b7280',
                    };
                @endphp
                <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 6px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-calendar-check"></i>
                    {{ $appointment->type === 'physical' ? 'Offline' : 'Online' }} Appointment
                </div>
                <h3 style="font-size: 18px; font-weight: 800; color: {{ $statusColor }}; margin-bottom: 16px;">{{ $statusLabel }}</h3>
                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border);">
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-user" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $appointment->user->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-user-tie" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ optional($appointment->expert)->user->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-calendar" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $appointment->scheduled_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                        <i class="fas fa-money-bill-wave" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>
                            {{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($appointment->fee ?? 0, 2) }}
                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 100px; margin-left: 4px;
                                background: {{ $appointment->payment_status === 'paid' ? '#d1fae5' : '#fef3c7' }};
                                color: {{ $appointment->payment_status === 'paid' ? '#065f46' : '#92400e' }};">
                                {{ ucfirst($appointment->payment_status ?? 'unpaid') }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Status Timeline --}}
            @if($appointment->statusHistory && $appointment->statusHistory->count())
            <div class="card-agri" style="padding: 24px; margin-bottom: 20px;">
                <h6 style="font-size: 12px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">Status Timeline</h6>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach($appointment->statusHistory->sortByDesc('changed_at') as $history)
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--agri-primary); margin-top: 5px; flex-shrink: 0;"></div>
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: var(--agri-text-heading);">
                                {{ ucfirst(str_replace('_', ' ', $history->from_status ?? '—')) }}
                                <i class="fas fa-arrow-right" style="font-size: 9px; color: var(--agri-text-muted); margin: 0 4px;"></i>
                                {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                            </div>
                            <div style="font-size: 11px; color: var(--agri-text-muted);">{{ optional($history->changed_at)->format('d M Y, H:i') }}</div>
                            @if($history->notes)
                            <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 2px; font-style: italic;">{{ $history->notes }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ── Right column ─────────────────────────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; margin-bottom: 20px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Appointment Details</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="agri-label">Date & Time</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('M d, Y h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Topic</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->topic ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Duration</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->duration_minutes }} minutes</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Payment Status</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ ucfirst($appointment->payment_status ?? 'unpaid') }}</p>
                        </div>
                    </div>
                    @if($appointment->admin_notes)
                    <div class="col-12">
                        <label class="agri-label">Admin Notes</label>
                        <div class="address-card" style="margin-bottom: 0; min-height: 60px;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->admin_notes }}</p>
                        </div>
                    </div>
                    @endif
                    @if($appointment->notes)
                    <div class="col-12">
                        <label class="agri-label">Customer Notes</label>
                        <div class="address-card" style="margin-bottom: 0; min-height: 60px;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-agri" style="padding: 32px; margin-bottom: 20px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">
                    {{ $appointment->type === 'physical' ? 'Physical Location' : 'Online Meeting' }}
                </h4>
                <div class="row g-4">
                    @if($appointment->type === 'online')
                        <div class="col-md-6">
                            <label class="agri-label">Platform</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->platform ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Meeting Link</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                @if($appointment->meeting_link)
                                    <a href="{{ $appointment->meeting_link }}" target="_blank" style="font-size: 13px; word-break: break-all;">{{ $appointment->meeting_link }}</a>
                                @else
                                    <p style="margin: 0; font-size: 14px; color: var(--agri-text-muted);">Not set yet</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="agri-label">Venue</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->venue_name ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">City</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->city ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="agri-label">Address</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">
                                    {{ $appointment->address_line1 ?: 'N/A' }}{{ $appointment->address_line2 ? ', ' . $appointment->address_line2 : '' }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Other admin actions (shown when not in approval/terminal states) --}}
            @php
                $terminalStatuses = [
                    \App\Models\Appointment::STATUS_PENDING_ADMIN_APPROVAL,
                    \App\Models\Appointment::STATUS_COMPLETED,
                    \App\Models\Appointment::STATUS_CANCELLED,
                    \App\Models\Appointment::STATUS_REJECTED,
                ];
            @endphp
            @if(!in_array($appointment->status, $terminalStatuses))
            <div class="card-agri" style="padding: 24px;">
                <h6 style="font-size: 12px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">Admin Actions</h6>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @if($appointment->status === \App\Models\Appointment::STATUS_PENDING_EXPERT_APPROVAL)
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#confirmModal">
                            <i class="fas fa-check me-1"></i> Force Confirm
                        </button>
                    @endif
                    @if(in_array($appointment->status, [\App\Models\Appointment::STATUS_CONFIRMED, \App\Models\Appointment::STATUS_RESCHEDULED]))
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#completeModal">
                            <i class="fas fa-flag-checkered me-1"></i> Mark Complete
                        </button>
                    @endif
                    <button class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-ban me-1"></i> Cancel
                    </button>
                    @if($appointment->payment_status === 'paid' && !$appointment->is_refunded)
                        <button class="btn btn-outline-warning btn-sm" data-toggle="modal" data-target="#refundModal">
                            <i class="fas fa-undo me-1"></i> Issue Refund
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Modals ──────────────────────────────────────────────────────────────────── --}}

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.appointments.confirm', $appointment->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Force Confirm Appointment</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                @if($appointment->type === 'online')
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Meeting Link <span class="text-danger">*</span></label>
                    <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.example.com" required value="{{ $appointment->meeting_link }}">
                </div>
                @endif
                <div>
                    <label class="form-label small text-muted fw-bold">Admin Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Appointment</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="completeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.appointments.complete', $appointment->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Mark as Completed</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-0">This will mark the appointment as completed and trigger the expert payout settlement.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Mark Complete</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.appointments.cancel', $appointment->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label class="form-label small text-muted fw-bold">Reason <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for cancellation..."></textarea>
                @if($appointment->payment_status === 'paid' && !$appointment->is_refunded)
                <p class="text-warning small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i> A refund will be automatically issued to the customer.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Back</button>
                <button type="submit" class="btn btn-danger">Cancel Appointment</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.appointments.refund', $appointment->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Issue Refund</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Refund Type</label>
                    <select name="refund_type" class="form-control" id="refundTypeSelect">
                        <option value="full">Full Refund ({{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($appointment->fee, 2) }})</option>
                        <option value="partial">Partial Refund</option>
                    </select>
                </div>
                <div class="mb-3" id="partialAmountRow" style="display:none;">
                    <label class="form-label small text-muted fw-bold">Amount</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $appointment->fee }}" placeholder="0.00">
                </div>
                <div>
                    <label class="form-label small text-muted fw-bold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for refund..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Issue Refund</button>
            </div>
        </form>
    </div>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
    .address-card {
        background: var(--agri-bg);
        border: 1px solid var(--agri-border);
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }
    .address-card:hover {
        border-color: var(--agri-primary);
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>

@push('scripts')
<script>
    document.getElementById('refundTypeSelect')?.addEventListener('change', function () {
        document.getElementById('partialAmountRow').style.display = this.value === 'partial' ? 'block' : 'none';
    });
</script>
@endpush
@endsection
