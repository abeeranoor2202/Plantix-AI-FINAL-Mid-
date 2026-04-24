@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Expert Payout Requests</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Payout Requests</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and process expert payment requests for completed consultations.</p>
        </div>
        @if($pendingCount > 0)
        <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 12px; padding: 12px 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle" style="color: #d97706; font-size: 18px;"></i>
            <span style="font-weight: 700; color: #92400e; font-size: 14px;">{{ $pendingCount }} pending request{{ $pendingCount > 1 ? 's' : '' }} need review</span>
        </div>
        @endif
    </div>

    {{-- Filter bar --}}
    <div class="card-agri mb-4" style="padding: 20px 24px;">
        <form method="GET" action="{{ route('admin.payout-requests.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: block; margin-bottom: 6px;">Status</label>
                <select name="status" class="form-agri" style="min-width: 160px; height: 42px;">
                    <option value="">All Statuses</option>
                    @foreach(['pending','approved','rejected','paid'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: block; margin-bottom: 6px;">Search Expert</label>
                <input type="text" name="search" class="form-agri" placeholder="Expert name..." value="{{ request('search') }}" style="height: 42px; min-width: 220px;">
            </div>
            <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 20px; align-self: flex-end;">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="{{ route('admin.payout-requests.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; align-self: flex-end; text-decoration: none;">Reset</a>
        </form>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Appointment</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Amount</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Requested</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert Note</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $pr)
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
                    <tr @if($pr->status === 'pending') style="background: #fffbeb;" @endif>
                        <td style="padding: 16px 24px;">
                            <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ optional($pr->expert)->user->name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: var(--agri-text-muted);">{{ optional($pr->expert)->user->email ?? '' }}</div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 13px;">
                                <a href="{{ route('admin.appointments.show', $pr->appointment_id) }}" style="color: var(--agri-primary); text-decoration: none;">#{{ $pr->appointment_id }}</a>
                            </div>
                            <div style="font-size: 12px; color: var(--agri-text-muted);">{{ $pr->appointment->user->name ?? 'N/A' }}</div>
                        </td>
                        <td style="padding: 16px 24px; font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">
                            PKR {{ number_format($pr->amount, 0) }}
                        </td>
                        <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted);">
                            {{ $pr->created_at->format('d M Y') }}<br>
                            <span style="font-size: 11px;">{{ $pr->created_at->diffForHumans() }}</span>
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; background: {{ $prBg }}; color: {{ $prColor }};">
                                <i class="fas fa-{{ $pr->status === 'paid' ? 'check-circle' : ($pr->status === 'rejected' ? 'times-circle' : ($pr->status === 'approved' ? 'clock' : 'hourglass-half')) }}"></i>
                                {{ ucfirst($pr->status) }}
                            </span>
                            @if($pr->reviewed_at)
                            <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">by {{ $pr->reviewer->name ?? 'Admin' }}</div>
                            @endif
                        </td>
                        <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted); max-width: 180px;">
                            {{ $pr->expert_note ? \Illuminate\Support\Str::limit($pr->expert_note, 60) : '—' }}
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                @if($pr->isPending())
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal{{ $pr->id }}" style="font-weight: 700;">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $pr->id }}" style="font-weight: 700;">
                                        <i class="fas fa-times me-1"></i> Reject
                                    </button>
                                @else
                                    <span style="font-size: 12px; color: var(--agri-text-muted); font-style: italic;">Reviewed</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                            <i class="fas fa-inbox" style="font-size: 32px; display: block; margin-bottom: 12px; opacity: .4;"></i>
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">No payout requests found</div>
                            <div style="font-size: 13px;">Experts will appear here once they request payment for completed consultations.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div style="padding: 20px 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
            {{ $requests->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

{{-- ── Approve / Reject Modals ──────────────────────────────────────────────── --}}
@foreach($requests as $pr)
    @if($pr->isPending())

    <div class="modal fade" id="approveModal{{ $pr->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.payout-requests.approve', $pr->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payout — PKR {{ number_format($pr->amount, 0) }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                        <div style="font-size: 13px; color: #166534;">
                            <strong>{{ optional($pr->expert)->user->name }}</strong> will receive a Stripe transfer of
                            <strong>PKR {{ number_format($pr->amount, 0) }}</strong> (minus platform commission).
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: block; margin-bottom: 6px;">Admin Note (optional)</label>
                        <textarea name="admin_note" class="form-control" rows="2" placeholder="e.g. Processed via Stripe Connect"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" style="font-weight: 700;">
                        <i class="fas fa-check me-1"></i> Approve & Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="rejectModal{{ $pr->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.payout-requests.reject', $pr->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payout Request</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: block; margin-bottom: 6px;">Reason <span class="text-danger">*</span></label>
                    <textarea name="admin_note" class="form-control" rows="3" required placeholder="Explain why this request is being rejected..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="font-weight: 700;">
                        <i class="fas fa-times me-1"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif
@endforeach

@endsection
