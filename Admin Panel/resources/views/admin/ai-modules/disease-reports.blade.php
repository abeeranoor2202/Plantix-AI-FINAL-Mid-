@extends('layouts.app')

@section('title', 'Disease Reports')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-virus me-2 text-warning"></i>Crop Disease Reports</h2>
        <a href="{{ route('admin.ai.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> AI Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Crop</th>
                        <th>Detected Disease</th>
                        <th>Confidence</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->id }}</td>
                        <td>{{ $report->user->name ?? '—' }}</td>
                        <td>{{ $report->crop_name }}</td>
                        <td>{{ $report->detected_disease ?? '<em class="text-muted">Pending</em>' }}</td>
                        <td>
                            @if($report->confidence_score)
                                <span class="badge bg-info">{{ $report->confidence_percent }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($report->status === 'verified')
                                <span class="badge bg-success">Verified</span>
                            @elseif($report->status === 'processed')
                                <span class="badge bg-primary">Processed</span>
                            @elseif($report->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($report->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $report->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.ai.disease-reports.show', $report->id) }}"
                               class="btn btn-xs btn-outline-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No disease reports yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="card-footer">{{ $reports->links() }}</div>
        @endif
    </div>

</div>
@endsection
