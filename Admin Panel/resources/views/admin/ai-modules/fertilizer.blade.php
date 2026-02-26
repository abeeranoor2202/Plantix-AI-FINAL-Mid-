@extends('layouts.app')

@section('title', 'Fertilizer Recommendations')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-flask me-2 text-danger"></i>Fertilizer Recommendations</h2>
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
                        <th>Growth Stage</th>
                        <th>N (kg/acre)</th>
                        <th>P (kg/acre)</th>
                        <th>K (kg/acre)</th>
                        <th>Est. Cost (PKR)</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recommendations as $rec)
                    <tr>
                        <td>{{ $rec->id }}</td>
                        <td>{{ $rec->user->name ?? '—' }}</td>
                        <td>{{ $rec->crop_type }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $rec->growth_stage ?? '')) }}</td>
                        <td>{{ $rec->n_recommendation ?? '—' }}</td>
                        <td>{{ $rec->p_recommendation ?? '—' }}</td>
                        <td>{{ $rec->k_recommendation ?? '—' }}</td>
                        <td>{{ $rec->estimated_cost ? 'Rs ' . number_format($rec->estimated_cost) : '—' }}</td>
                        <td>{{ $rec->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No fertilizer recommendations yet.</td></tr>
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
