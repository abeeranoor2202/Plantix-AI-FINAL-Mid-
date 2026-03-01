@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Appointments</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage and monitor agricultural consultation bookings between farmers and experts.</p>
        </div>
        <div style="background: var(--agri-white); padding: 8px 16px; border-radius: 12px; border: 1px solid var(--agri-border); font-size: 14px; font-weight: 600; color: var(--agri-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-calendar-check"></i>
            {{ $appointments->total() }} Total Bookings
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card-agri mb-4" style="padding: 24px;">
        <form method="GET" action="{{ route('admin.appointments.index') }}">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Appointment Status</label>
                    <div style="position: relative;">
                        <i class="fas fa-filter" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <select name="status" class="form-agri" style="padding-left: 40px;">
                            <option value="">All Statuses</option>
                            @foreach(['draft','pending_payment','payment_failed','pending_expert_approval','confirmed','reschedule_requested','rejected','completed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>
                                    {{ ucwords(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Booking Date</label>
                    <div style="position: relative;">
                        <i class="fas fa-calendar-day" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <input type="date" name="date" class="form-agri" style="padding-left: 40px;"
                               value="{{ request('date') }}">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Search</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <input type="text" name="search" class="form-agri" style="padding-left: 40px;"
                               placeholder="Customer or expert name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-12 col-md-6 d-flex align-items-end gap-2 justify-content-end" style="margin-top: 8px;">
                    <button type="submit" class="btn-agri btn-agri-primary">
                        Filter Bookings
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn-agri btn-agri-outline" style="min-width: 80px; text-decoration: none;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Appointments Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">No.</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Farmer / Expert</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Schedule</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Consultation Fee</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-center">Status</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                        <tr style="border-bottom: 1px solid var(--agri-border);">
                            <td style="padding: 20px 24px;">
                                <div style="font-weight: 600; color: var(--agri-text-muted);">#{{ $appt->id }}</div>
                            </td>
                            <td style="padding: 20px 24px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span style="font-weight: 600; color: var(--agri-text-heading); font-size: 14px;">{{ $appt->user->name ?? 'N/A' }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--agri-secondary-light); color: var(--agri-secondary); display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <span style="font-size: 13px; color: var(--agri-text-main);">{{ optional($appt->expert)->user->name ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 20px 24px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="font-weight: 600; color: var(--agri-text-heading); font-size: 14px;">
                                        <i class="far fa-calendar-alt" style="margin-right: 6px; color: var(--agri-primary);"></i>
                                        {{ $appt->scheduled_at ? $appt->scheduled_at->format('d M, Y') : '—' }}
                                    </div>
                                    <div style="font-size: 13px; color: var(--agri-text-muted);">
                                        <i class="far fa-clock" style="margin-right: 6px;"></i>
                                        {{ $appt->scheduled_at ? $appt->scheduled_at->format('g:i A') : '—' }}
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 20px 24px;">
                                <div style="font-weight: 700; color: var(--agri-primary-dark); font-size: 16px;">
                                    {{ config('plantix.currency_symbol', 'PKR') }}{{ number_format($appt->fee ?? 0, 2) }}
                                </div>
                            </td>
                            <td style="padding: 20px 24px;" class="text-center">
                                @php
                                    $bc = [
                                        'draft'                   => ['#6B7280', '#F3F4F6'],
                                        'pending_payment'         => ['#D97706', '#FEF3C7'],
                                        'payment_failed'          => ['#DC2626', '#FEE2E2'],
                                        'pending_expert_approval' => ['#7C3AED', '#EDE9FE'],
                                        'confirmed'               => ['#059669', '#D1FAE5'],
                                        'reschedule_requested'    => ['#2563EB', '#DBEAFE'],
                                        'rejected'                => ['#B91C1C', '#FEE2E2'],
                                        'completed'               => ['#0D9488', '#CCFBF1'],
                                        'cancelled'               => ['#9CA3AF', '#F9FAFB'],
                                    ];
                                    $currentStatus = $bc[$appt->status] ?? ['#9CA3AF', '#F9FAFB'];
                                @endphp
                                <div style="display: inline-flex; align-items: center; gap: 6px; color: {{ $currentStatus[0] }}; background: {{ $currentStatus[1] }}; padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 600; border: 1px solid {{ $currentStatus[0] }}20;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $currentStatus[0] }};"></span>
                                    {{ ucwords(str_replace('_', ' ', $appt->status)) }}
                                </div>
                            </td>
                            <td style="padding: 20px 24px;" class="text-end">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($appt->status, ['pending_expert_approval', 'reschedule_requested']))
                                        <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: var(--agri-success-light); color: var(--agri-success); border-radius: 10px; border: none;" title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.appointments.cancel', $appt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel appointment?')">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: var(--agri-error-light); color: var(--agri-error); border-radius: 10px; border: none;" title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 60px 24px; text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 16px; color: var(--agri-text-muted);">
                                    <i class="fas fa-calendar-times" style="font-size: 48px; opacity: 0.3;"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No appointments found</p>
                                        <p style="margin: 4px 0 0 0; font-size: 14px;">Use the filters above to search for specific consultations.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Section --}}
    @if($appointments->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $appointments->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection

