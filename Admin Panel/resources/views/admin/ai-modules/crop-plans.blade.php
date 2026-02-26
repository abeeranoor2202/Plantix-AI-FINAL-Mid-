@extends('layouts.app')

@section('title', 'Crop Plans')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-calendar-check me-2 text-success"></i>Crop Plans</h2>
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
                        <th>Primary Crop</th>
                        <th>Season</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Est. Revenue (PKR)</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td>{{ $plan->id }}</td>
                        <td>{{ $plan->user->name ?? '—' }}</td>
                        <td>{{ $plan->primary_crop }}</td>
                        <td><span class="badge bg-info text-dark">{{ $plan->season }}</span></td>
                        <td>{{ $plan->year }}</td>
                        <td>
                            <span class="badge bg-{{ $plan->status === 'active' ? 'success' : ($plan->status === 'completed' ? 'secondary' : 'warning text-dark') }}">
                                {{ ucfirst($plan->status ?? 'draft') }}
                            </span>
                        </td>
                        <td>{{ $plan->estimated_revenue ? 'Rs ' . number_format($plan->estimated_revenue) : '—' }}</td>
                        <td>{{ $plan->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No crop plans yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
        <div class="card-footer">{{ $plans->links() }}</div>
        @endif
    </div>

</div>
@endsection
