@extends('layouts.app')

@section('title', 'Expert Management')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 48px;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Management</h1>
            <p style="color: var(--agri-text-muted); margin: 6px 0 0 0; font-size: 14px;">Review, approve and manage agricultural expert accounts.</p>
        </div>
        <div style="background: var(--agri-white); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--agri-border); font-size: 14px; font-weight: 700; color: var(--agri-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-user-tie"></i> {{ $experts->total() }} Total Experts
        </div>
    </div>

    @if(session('success'))
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card-agri mb-4" style="padding: 24px;">
        <form method="GET" action="{{ route('admin.experts.index') }}">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Approval Status</label>
                    <div style="position: relative;">
                        <i class="fas fa-filter" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <select name="status" class="form-agri" style="padding-left: 40px;">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Search</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <input type="text" name="search" class="form-agri" style="padding-left: 40px;"
                               placeholder="Name, email, specialization, city..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1;">Filter</button>
                    <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="min-width: 80px; text-decoration: none;">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Specialty</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Rate</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Bookings</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Status</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($experts as $expert)
                    @php
                        $approval = $expert->profile->approval_status ?? 'pending';
                        $approvalMap = [
                            'approved'  => ['#059669', '#D1FAE5'],
                            'pending'   => ['#D97706', '#FEF3C7'],
                            'rejected'  => ['#DC2626', '#FEE2E2'],
                            'suspended' => ['#6B7280', '#F3F4F6'],
                        ];
                        $ac = $approvalMap[$approval] ?? ['#9CA3AF', '#F9FAFB'];
                    @endphp
                    <tr style="border-bottom: 1px solid var(--agri-border);">
                        <td style="padding: 18px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0;">
                                    {{ strtoupper(substr($expert->user->name ?? 'E', 0, 1)) }}
                                </div>
                                <div>
                                    <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">{{ $expert->user->name ?? '—' }}</p>
                                    <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">{{ $expert->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 18px 24px;">
                            <p style="margin: 0; font-size: 13px; font-weight: 600; color: var(--agri-text-heading);">{{ $expert->specialty ?? $expert->profile->specialization ?? '—' }}</p>
                            @if($expert->profile->city ?? null)
                                <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--agri-text-muted);"><i class="fas fa-map-marker-alt" style="font-size: 10px; margin-right: 4px;"></i>{{ $expert->profile->city }}</p>
                            @endif
                        </td>
                        <td style="padding: 18px 24px; text-align: center;">
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--agri-primary-dark);">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($expert->hourly_rate ?? 0, 2) }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 11px; color: var(--agri-text-muted);">/ session</p>
                        </td>
                        <td style="padding: 18px 24px; text-align: center;">
                            <span style="font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $expert->appointments_count }}</span>
                        </td>
                        <td style="padding: 18px 24px; text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; color: {{ $ac[0] }}; background: {{ $ac[1] }}; padding: 5px 14px; border-radius: 100px; font-size: 12px; font-weight: 600; border: 1px solid {{ $ac[0] }}20; margin-bottom: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $ac[0] }};"></span>
                                {{ ucfirst($approval) }}
                            </div>
                            <br>
                            @if($expert->is_available)
                                <span style="font-size: 11px; font-weight: 600; color: #059669;"><i class="fas fa-circle" style="font-size: 8px; margin-right: 3px;"></i>Available</span>
                            @else
                                <span style="font-size: 11px; font-weight: 600; color: #9CA3AF;"><i class="fas fa-circle" style="font-size: 8px; margin-right: 3px;"></i>Unavailable</span>
                            @endif
                        </td>
                        <td style="padding: 18px 24px; text-align: end;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('admin.experts.show', $expert->id) }}" class="btn-agri"
                                   style="padding: 8px 12px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($approval === 'pending')
                                <form action="{{ route('admin.experts.approve', $expert->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-agri"
                                            style="padding: 8px 12px; background: #D1FAE5; color: #065F46; border-radius: 10px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;"
                                            onclick="return confirm('Approve this expert?')">
                                        <i class="fas fa-check"></i> Approve
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
                                <i class="fas fa-user-tie" style="font-size: 48px; opacity: 0.3;"></i>
                                <div>
                                    <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No experts found</p>
                                    <p style="margin: 4px 0 0 0; font-size: 14px;">Use the filters to narrow your search.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($experts->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $experts->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
