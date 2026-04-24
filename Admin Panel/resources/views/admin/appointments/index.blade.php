@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Appointments</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Appointments</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage and monitor consultation bookings in one consistent table.</p>
        </div>
        <a href="{{ route('admin.appointments.create') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i>
            Create Appointment
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Appointment List</h4>
            <form method="GET" action="{{ route('admin.appointments.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Types</option>
                    <option value="online" @selected(request('type') === 'online')>Online</option>
                    <option value="offline" @selected(request('type') === 'offline')>Offline</option>
                </select>
                <input type="date" name="date" class="form-agri" style="height: 42px; min-width: 160px; margin-bottom: 0;" value="{{ request('date') }}">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search customer or expert..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Type</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date & Time</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                        @php
                            $displayStatus = match ($appt->status) {
                                'confirmed', 'accepted'    => 'Confirmed',
                                'completed'                => 'Completed',
                                'cancelled', 'rejected'    => 'Cancelled',
                                'pending_admin_approval'   => 'Awaiting Approval',
                                'pending_expert_approval'  => 'Pending Expert',
                                'pending_payment'          => 'Pending Payment',
                                'payment_failed'           => 'Payment Failed',
                                default                    => 'Pending',
                            };
                            $statusColor = match ($appt->status) {
                                'confirmed'               => '#059669',
                                'completed'               => '#0D9488',
                                'cancelled', 'rejected'   => '#DC2626',
                                'pending_admin_approval'  => '#D97706',
                                'pending_expert_approval' => '#7C3AED',
                                'payment_failed'          => '#DC2626',
                                default                   => '#6B7280',
                            };
                            $statusBg = match ($appt->status) {
                                'confirmed'               => '#D1FAE5',
                                'completed'               => '#CCFBF1',
                                'cancelled', 'rejected'   => '#FEE2E2',
                                'pending_admin_approval'  => '#FEF3C7',
                                'pending_expert_approval' => '#EDE9FE',
                                'payment_failed'          => '#FEE2E2',
                                default                   => '#F3F4F6',
                            };
                        @endphp
                        <tr @if($appt->status === 'pending_admin_approval') style="background: #fffbeb;" @endif>
                            <td class="px-4 py-3">
                                {{ $appt->user->name ?? 'N/A' }}
                                @if($appt->status === 'pending_admin_approval')
                                    <div style="font-size: 10px; color: #d97706; font-weight: 700; margin-top: 2px;"><i class="fas fa-exclamation-circle me-1"></i>Action required</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ optional($appt->expert)->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $appt->type === 'physical' ? '#059669' : '#2563EB' }}; background: {{ $appt->type === 'physical' ? '#D1FAE5' : '#DBEAFE' }}; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; border: 1px solid {{ $appt->type === 'physical' ? '#059669' : '#2563EB' }}30; text-transform: uppercase;">
                                    {{ $appt->type === 'physical' ? 'OFFLINE' : 'ONLINE' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($appt->scheduled_at)
                                    {{ $appt->scheduled_at->format('M d, Y h:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $statusColor }}; background: {{ $statusBg }}; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; border: 1px solid {{ $statusColor }}30;">
                                    {{ $displayStatus }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px; align-items: center;">
                                    @if($appt->status === 'pending_admin_approval')
                                        <form method="POST" action="{{ route('admin.appointments.approve', $appt->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 6px 12px; background: #fef3c7; color: #92400e; border-radius: 10px; border: 1px solid #fcd34d; font-size: 11px; font-weight: 700; white-space: nowrap;" title="Approve & Forward to Expert">
                                                <i class="fas fa-paper-plane me-1"></i> Approve
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 10px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.appointments.edit', $appt->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 10px;" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.appointments.destroy', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #FEF2F2; color: var(--agri-error); border-radius: 10px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $appointments->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
