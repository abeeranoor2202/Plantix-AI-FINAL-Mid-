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
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Appointment List</h4>
            <form method="GET" action="{{ route('admin.appointments.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 200px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    @foreach(['draft','pending_payment','payment_failed','pending_expert_approval','confirmed','reschedule_requested','rejected','completed','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
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
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">No.</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Farmer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Type</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Schedule</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Fee</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                        <tr>
                            <td class="px-4 py-3">#{{ $appt->id }}</td>
                            <td class="px-4 py-3">{{ $appt->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ optional($appt->expert)->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $appt->type === 'physical' ? 'bg-success' : 'bg-primary' }}">{{ strtoupper($appt->type_label) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($appt->scheduled_at)
                                    {{ $appt->scheduled_at->format('M d, Y h:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3"><strong>{{ config('plantix.currency_symbol', 'PKR') }}{{ number_format($appt->fee ?? 0, 2) }}</strong></td>
                            <td class="px-4 py-3">
                                @php($st = strtolower((string) $appt->status))
                                <span class="badge rounded-pill {{ in_array($st, ['confirmed','completed']) ? 'bg-success' : (in_array($st, ['pending_payment','pending_expert_approval','reschedule_requested']) ? 'bg-warning text-dark' : (in_array($st, ['rejected','cancelled','payment_failed']) ? 'bg-danger' : 'bg-secondary')) }}">
                                    {{ strtoupper(str_replace('_', ' ', $st)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.appointments.edit', $appt->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #7C3AED; border-radius: 999px;" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.appointments.destroy', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #DC2626; border-radius: 999px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @if(in_array($appt->status, ['pending_expert_approval', 'reschedule_requested']))
                                        <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #ecfdf5; color: #059669; border-radius: 999px; border: none;" title="Confirm"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form action="{{ route('admin.appointments.cancel', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel appointment?')">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Cancel"><i class="fas fa-times"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5" style="color: var(--agri-text-muted);">No appointments found.</td></tr>
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
