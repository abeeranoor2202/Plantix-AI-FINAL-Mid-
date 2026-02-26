@extends('layouts.app')

@section('title', 'Crop Recommendations')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-seedling me-2 text-primary"></i>Crop Recommendations</h2>
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
                        <th>Top Crop</th>
                        <th>Crops Count</th>
                        <th>Soil Source</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recommendations as $rec)
                    <tr>
                        <td>{{ $rec->id }}</td>
                        <td>{{ $rec->user->name ?? '—' }}</td>
                        <td><span class="badge bg-success">{{ $rec->top_crop }}</span></td>
                        <td>{{ count($rec->recommended_crops ?? []) }}</td>
                        <td>{{ $rec->soil_test_id ? 'Lab Test #'.$rec->soil_test_id : 'Manual Input' }}</td>
                        <td>{{ $rec->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.ai.crop-recommendations.show', $rec->id) }}" class="btn btn-xs btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No recommendations yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($recommendations->hasPages())
        <div class="card-footer">{{ $recommendations->links() }}</div>
        @endif
    </div>

</div>
@endsection
