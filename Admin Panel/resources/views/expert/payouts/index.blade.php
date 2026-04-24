@extends('expert.layouts.app')

@section('title', 'Payouts')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('expert.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Payouts</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Payouts & Earnings</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Request payment for completed consultations and track your earnings.</p>
    </div>
</div>

{{-- ── Earnings Summary ──────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card-agri h-100" style="padding: 24px;">
            <h6 style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 12px;">Stripe Connect</h6>
            @php $connected = ($stripeAccount?->onboarding_status ?? 'pending') === 'completed'; @endphp
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 700;
                    background: {{ $connected ? '#d1fae5' : '#fef3c7' }}; color: {{ $connected ? '#065f46' : '#92400e' }};">
                    <i class="fas fa-{{ $connected ? 'check-circle' : 'exclamation-circle' }}"></i>
                    {{ $connected ? 'Connected' : 'Action Required' }}
                </span>
                <a href="{{ route('expert.payouts.connect') }}" class="btn-agri btn-agri-{{ $connected ? 'outline' : 'primary' }}" style="font-size: 13px; padding: 6px 14px;">
                    {{ $connected ? 'Update' : 'Connect Stripe' }}
                </a>
            </div>
            <p style="font-size: 12px; color: var(--agri-text-muted); margin: 0;">Payouts are transferred to your Stripe account after admin approval.</p>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-agri h-100" style="padding: 24px;">
            <h6 style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 16px;">Earnings Summary</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <div style="background: var(--agri-bg); border-radius: 12px; padding: 16px; border: 1px solid var(--agri-border);">
                        <div style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px;">Gross Earned</div>
                        <div style="font-size: 20px; font-weight: 800; color: var(--agri-text-heading);">PKR {{ number_format($totals['gross'] ?? 0, 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: var(--agri-bg); border-radius: 12px; padding: 16px; border: 1px solid var(--agri-border);">
                        <div style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px;">Platform Fee</div>
                        <div style="font-size: 20px; font-weight: 800; color: #dc2626;">PKR {{ number_format($totals['commission'] ?? 0, 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div style="background: #d1fae5; border-radius: 12px; padding: 16px; border: 1px solid #a7f3d0;">
                        <div style="font-size: 11px; font-weight: 700; color: #065f46; text-transform: uppercase; margin-bottom: 6px;">Net Paid Out</div>
                        <div style="font-size: 20px; font-weight: 800; color: #065f46;">PKR {{ number_format($totals['net'] ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Request Payment Section ───────────────────────────────────────────── --}}
@if($requestableAppointments->isNotEmpty())
<div class="card-agri mb-4" style="padding: 0; overflow: hidden; border: 2px solid var(--agri-primary);">
    <div style="padding: 20px 24px; background: var(--agri-primary-light); border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
        <div style="width: 36px; height: 36px; background: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-hand-holding-usd" style="color: white; font-size: 16px;"></i>
        </div>
        <div>
            <h5 style="margin: 0; font-weight: 800; color: var(--agri-primary-dark); font-size: 16px;">Request Payment</h5>
            <p style="margin: 0; font-size: 13px; color: var(--agri-text-muted);">{{ $requestableAppointments->count() }} completed consultation{{ $requestableAppointments->count() > 1 ? 's' : '' }} awaiting payment request</p>
        </div>
    </div>
    <div style="padding: 0;">
        @foreach($requestableAppointments as $appt)
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 44px; height: 44px; background: var(--agri-primary-light); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; color: var(--agri-primary);">
                    {{ strtoupper(substr($appt->user->name ?? 'F', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $appt->user->name ?? 'Farmer' }}</div>
                    <div style="font-size: 12px; color: var(--agri-text-muted);">
                        {{ $appt->topic ?? 'General Consultation' }} &bull;
                        {{ $appt->scheduled_at?->format('d M Y') }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="text-align: right;">
                    <div style="font-size: 18px; font-weight: 800; color: var(--agri-primary-dark);">PKR {{ number_format($appt->fee, 0) }}</div>
                    <div style="font-size: 11px; color: var(--agri-text-muted);">Appointment #{{ $appt->id }}</div>
                </div>
                <button type="button"
                    class="btn-agri btn-agri-primary"
                    style="padding: 10px 20px; font-size: 13px; font-weight: 700; white-space: nowrap;"
                    data-toggle="modal"
                    data-target="#requestPayoutModal{{ $appt->id }}">
                    <i class="fas fa-paper-plane me-1"></i> Request Payment
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Payout Requests History ───────────────────────────────────────────── --}}
<div class="card-agri mb-4" style="padding: 0; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border);">
        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Payment Requests</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0" style="vertical-align: middle;">
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Appointment</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Amount</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Requested</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Admin Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payoutRequests as $pr)
                @php
                    $prColor = match($pr->status) {
                        'paid'     => '#065f46',
                        'approved' => '#1d4ed8',
                        'rejected' => '#dc2626',
                        default    => '#92400e',
                    };
                    $prBg = match($pr->status) {
                        'paid'     => '#d1fae5',
                        'approved' => '#dbeafe',
                        'rejected' => '#fee2e2',
                        default    => '#fef3c7',
                    };
                @endphp
                <tr>
                    <td style="padding: 16px 24px;">
                        <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">
                            #{{ $pr->appointment_id }} — {{ $pr->appointment->user->name ?? 'N/A' }}
                        </div>
                        <div style="font-size: 12px; color: var(--agri-text-muted);">{{ $pr->appointment->topic ?? 'General Consultation' }}</div>
                    </td>
                    <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading);">PKR {{ number_format($pr->amount, 0) }}</td>
                    <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $pr->created_at->format('d M Y') }}</td>
                    <td style="padding: 16px 24px;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; background: {{ $prBg }}; color: {{ $prColor }};">
                            <i class="fas fa-{{ $pr->status === 'paid' ? 'check-circle' : ($pr->status === 'rejected' ? 'times-circle' : ($pr->status === 'approved' ? 'clock' : 'hourglass-half')) }}"></i>
                            {{ ucfirst($pr->status) }}
                        </span>
                    </td>
                    <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $pr->admin_note ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fas fa-inbox" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        No payment requests yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payoutRequests->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--agri-border);">
        {{ $payoutRequests->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ── Payout Transfer History ───────────────────────────────────────────── --}}
<div class="card-agri" style="padding: 0; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border);">
        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Transfer History</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0" style="vertical-align: middle;">
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Gross</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Fee</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Net</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                    <th style="padding: 14px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Transfer ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                <tr>
                    <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ optional($payout->created_at)->format('d M Y, h:i A') }}</td>
                    <td style="padding: 16px 24px; font-weight: 600;">PKR {{ number_format((float) $payout->amount, 0) }}</td>
                    <td style="padding: 16px 24px; color: #dc2626; font-weight: 600;">PKR {{ number_format((float) $payout->commission, 0) }}</td>
                    <td style="padding: 16px 24px; font-weight: 800; color: #065f46;">PKR {{ number_format((float) $payout->net_amount, 0) }}</td>
                    <td style="padding: 16px 24px;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;
                            background: {{ $payout->status === 'paid' ? '#d1fae5' : ($payout->status === 'failed' ? '#fee2e2' : '#fef3c7') }};
                            color: {{ $payout->status === 'paid' ? '#065f46' : ($payout->status === 'failed' ? '#dc2626' : '#92400e') }};">
                            {{ ucfirst($payout->status) }}
                        </span>
                    </td>
                    <td style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); font-family: monospace;">{{ $payout->stripe_transfer_id ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fas fa-exchange-alt" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        No transfers yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payouts->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--agri-border);">
        {{ $payouts->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ── Request Payout Modals ─────────────────────────────────────────────── --}}
@foreach($requestableAppointments as $appt)
<div class="modal fade" id="requestPayoutModal{{ $appt->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('expert.payouts.request') }}" class="modal-content">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Request Payment — Appointment #{{ $appt->id }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div style="background: var(--agri-bg); border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid var(--agri-border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: var(--agri-text-muted);">Farmer</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $appt->user->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: var(--agri-text-muted);">Topic</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $appt->topic ?? 'General Consultation' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: var(--agri-text-muted);">Date</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $appt->scheduled_at?->format('d M Y, h:i A') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid var(--agri-border);">
                        <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Amount</span>
                        <span style="font-size: 18px; font-weight: 800; color: var(--agri-primary);">PKR {{ number_format($appt->fee, 0) }}</span>
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: block; margin-bottom: 6px;">Note to Admin (optional)</label>
                    <textarea name="expert_note" class="form-control" rows="3" placeholder="Any additional information for the admin..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" style="font-weight: 700;">
                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection
