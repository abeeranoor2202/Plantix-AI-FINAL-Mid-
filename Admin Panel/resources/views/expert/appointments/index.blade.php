@extends('expert.layouts.app')

@section('title', 'Appointments')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('expert.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Appointments</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Appointments</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage consultation requests and session lifecycle.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3"><div class="card-agri"><h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Total</h5><h2 style="font-size: 28px; font-weight: 700; margin:0;">{{ $stats['total'] }}</h2></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card-agri"><h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Pending</h5><h2 style="font-size: 28px; font-weight: 700; margin:0;">{{ $stats['pending'] }}</h2></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card-agri"><h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Upcoming</h5><h2 style="font-size: 28px; font-weight: 700; margin:0;">{{ $stats['upcoming'] }}</h2></div></div>
    <div class="col-sm-6 col-lg-3"><div class="card-agri"><h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Completed</h5><h2 style="font-size: 28px; font-weight: 700; margin:0;">{{ $stats['completed'] }}</h2></div></div>
</div>

<div class="card-agri mb-4" style="padding: 20px 24px;">
    <form method="GET" action="{{ route('expert.appointments.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="agri-label">Search</label>
                <x-input name="search" :value="$filters['search'] ?? ''" placeholder="Farmer or topic" />
            </div>
            <div class="col-md-3">
                <label class="agri-label">Status</label>
                <select name="status" class="form-agri">
                    <option value="">All</option>
                    @foreach(['pending_payment','pending_expert_approval','confirmed','reschedule_requested','rescheduled','completed','rejected','cancelled'] as $st)
                        <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="agri-label">Date From</label>
                <x-input type="date" name="date_from" :value="$filters['date_from'] ?? ''" />
            </div>
            <div class="col-md-2">
                <label class="agri-label">Date To</label>
                <x-input type="date" name="date_to" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="col-md-2 d-flex gap-2">
                <x-button type="submit" variant="primary" icon="fas fa-filter" style="width:100%;">Apply</x-button>
                <x-button :href="route('expert.appointments.index')" variant="outline" style="width:100%;">Reset</x-button>
            </div>
        </div>
    </form>
</div>

<div class="card-agri" style="padding: 0; overflow: hidden;">
    <x-table>
        <thead style="background: var(--agri-bg);">
            <tr>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">ID</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">FARMER</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TOPIC</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TYPE</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">SCHEDULED</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">STATUS</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">FEE</th>
                <th class="text-end" style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments->items() as $appt)
                <tr>
                    <td class="px-4 py-3">#{{ $appt->id }}</td>
                    <td class="px-4 py-3">
                        <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $appt->user->name }}</div>
                        <small class="text-muted">{{ $appt->user->email }}</small>
                    </td>
                    <td class="px-4 py-3">{{ Str::limit($appt->topic ?? 'General consultation', 35) }}</td>
                    <td class="px-4 py-3">
                        <x-badge :variant="$appt->type === 'physical' ? 'success' : 'info'">{{ strtoupper($appt->type_label) }}</x-badge>
                    </td>
                    <td class="px-4 py-3">{{ $appt->scheduled_at?->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusVariant = match($appt->status) {
                                'completed', 'confirmed' => 'success',
                                'pending_payment', 'pending_expert_approval', 'reschedule_requested', 'rescheduled' => 'warning',
                                'rejected', 'cancelled' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <x-badge :variant="$statusVariant">{{ ucfirst(str_replace('_',' ', $appt->status)) }}</x-badge>
                    </td>
                    <td class="px-4 py-3">PKR {{ number_format($appt->fee) }}</td>
                    <td class="px-4 py-3 text-end">
                        <div style="display: inline-flex; gap: 8px;">
                            <a href="{{ route('expert.appointments.show', $appt) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #9ca3af; border-radius: 999px; border: none;" title="Edit unavailable" disabled><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #fca5a5; border-radius: 999px; border: none;" title="Delete unavailable" disabled><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5" style="color: var(--agri-text-muted);">
                        <i class="mdi mdi-calendar-blank-outline" style="font-size: 28px; display:block; margin-bottom: 8px; opacity: .5;"></i>
                        No appointments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>

@if($appointments->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center;">
        {{ $appointments->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
