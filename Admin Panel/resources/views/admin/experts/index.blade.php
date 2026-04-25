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
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.experts.create') }}" class="btn-agri btn-agri-primary" style="height: 44px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none;">
                <i class="fas fa-plus"></i> Add Expert
            </a>
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
                <select name="type" class="form-agri" style="height: 42px; min-width: 140px; margin-bottom: 0;">
                    <option value="">All Types</option>
                    <option value="individual" @selected(request('type') === 'individual')>Individual</option>
                    <option value="agency" @selected(request('type') === 'agency')>Agency</option>
                </select>
                <select name="activity" class="form-agri" style="height: 42px; min-width: 180px; margin-bottom: 0;">
                    <option value="">All Activity</option>
                    <option value="active_7d" @selected(request('activity') === 'active_7d')>Active in 7 days</option>
                    <option value="active_30d" @selected(request('activity') === 'active_30d')>Active in 30 days</option>
                    <option value="inactive_30d" @selected(request('activity') === 'inactive_30d')>Inactive 30+ days</option>
                </select>
                <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search experts..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
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
                                <div class="text-end">
                                    <a href="{{ route('admin.experts.show', $expert->id) }}" class="btn-action btn-action-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.experts.edit', $expert->id) }}" class="btn-action btn-action-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <form action="{{ route('admin.experts.destroy', $expert->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Archive this expert?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
