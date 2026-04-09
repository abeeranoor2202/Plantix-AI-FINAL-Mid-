@extends('layouts.app')

@section('title', 'Expert Audit Log')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Experts</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.experts.show', $expert->id) }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{ $expert->display_name }}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Audit Log</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Audit Log</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Track all moderation and status transitions for this expert profile.</p>
        </div>
        <a href="{{ route('admin.experts.show', $expert->id) }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Expert
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Actor</th>
                            <th>IP</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="text-muted small text-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <code class="small">{{ $log->action }}</code>
                            </td>
                            <td>
                                @if($log->from_status)
                                <span class="badge bg-secondary">{{ $log->from_status }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->to_status)
                                @php
                                    $badge = match($log->to_status) {
                                        'approved'     => 'success',
                                        'pending'      => 'warning',
                                        'under_review' => 'info',
                                        'rejected'     => 'danger',
                                        'suspended'    => 'secondary',
                                        'inactive'     => 'dark',
                                        default        => 'light'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $log->to_status }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $log->actor?->name ?? '<em class="text-muted">System</em>' }}
                            </td>
                            <td class="text-muted small">{{ $log->ip_address ?? '—' }}</td>
                            <td class="small text-muted" style="max-width:250px;">
                                {{ Str::limit($log->notes, 80) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No log entries found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-transparent">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
