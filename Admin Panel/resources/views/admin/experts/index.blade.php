@extends('layouts.app')

@section('title', 'Expert Management')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Experts</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Management</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review, approve and manage expert accounts.</p>
        </div>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 12px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Expert List</h4>
            <form method="GET" action="{{ route('admin.experts.index') }}" style="display: flex; gap: 10px; align-items: center;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                    <option value="">All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search experts..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expert</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Specialty</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Rate</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($experts as $expert)
                        @php
                            $approval = $expert->profile->approval_status ?? 'pending';
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $expert->user->name ?? 'N/A' }}</div>
                                <div style="font-size: 12px; color: var(--agri-text-muted);">{{ $expert->user->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-size: 14px; color: var(--agri-text-heading);">{{ $expert->specialty ?? $expert->profile->specialization ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-primary-dark);">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($expert->hourly_rate ?? 0, 2) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $approval === 'approved' ? 'bg-success' : ($approval === 'rejected' ? 'bg-danger' : 'bg-warning') }}">{{ ucfirst($approval) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.experts.show', $expert->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>

                                    @if($approval === 'pending')
                                        <form action="{{ route('admin.experts.approve', $expert->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px; border: none;" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.experts.reject', $expert->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reject this expert?');">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Reject">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.experts.suspend', $expert->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Suspend this expert?');">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Suspend">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color: var(--agri-text-muted);">No experts found</td>
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
