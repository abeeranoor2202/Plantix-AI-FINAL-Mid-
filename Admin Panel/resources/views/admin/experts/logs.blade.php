@extends('layouts.app')

@section('title', 'Expert Audit Log')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Audit Log</h4>
            <p class="text-muted mb-0">Expert: {{ $expert->display_name }}</p>
        </div>
        <a href="{{ route('admin.experts.show', $expert->id) }}" class="btn btn-outline-secondary btn-sm">
            ← Back to Expert
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
