@extends('vendor.layouts.app')
@section('title', 'Returns & Refunds')
@section('page-title', 'Returns & Refunds')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Return Requests</h4>
        <span class="text-muted small fw-medium mt-1 d-block">Manage and process customer return and refund requests</span>
    </div>
</div>

<div class="card border-0 shadow-sm hover-card" style="border-radius:16px;">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary fs-5"></i>All Returns</h6>
        {{-- Filter --}}
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="text-muted small fw-bold text-uppercase text-nowrap mb-0">Filter by:</label>
            <select name="status" class="form-select border-0 bg-light rounded-pill px-3 py-2 fw-medium shadow-sm w-auto" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        @if($returns->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No returns found</h6>
                <p class="small mb-0">There are currently no return requests matching your criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small">Return Ref</th>
                            <th class="fw-semibold text-muted text-uppercase small">Order #</th>
                            <th class="fw-semibold text-muted text-uppercase small">Customer</th>
                            <th class="fw-semibold text-muted text-uppercase small">Reason</th>
                            <th class="text-center fw-semibold text-muted text-uppercase small">Status</th>
                            <th class="fw-semibold text-muted text-uppercase small">Requested On</th>
                            <th class="text-end pe-4 fw-semibold text-muted text-uppercase small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $ret)
                        <tr>
                            <td class="ps-4">
                                <div class="d-inline-flex bg-light border border-secondary border-opacity-25 rounded px-2 py-1 shadow-sm">
                                    <strong class="font-monospace text-dark small">#{{ $ret->id }}</strong>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('vendor.orders.show', $ret->order_id) }}" class="text-decoration-none fw-bold text-primary small d-flex align-items-center">
                                    <i class="bi bi-receipt me-1"></i>{{ $ret->order->order_number ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2 shadow-sm" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                        {{ substr($ret->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="fw-medium text-dark small">{{ $ret->user->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small d-inline-block text-truncate" style="max-width: 200px;" title="{{ $ret->reason->title ?? $ret->reason->reason ?? $ret->notes ?? '' }}">
                                    {{ Str::limit($ret->reason->title ?? $ret->reason->reason ?? $ret->notes ?? '—', 40) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php 
                                    $badgeMap = [
                                        'pending'  => 'warning text-dark',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'refund_processing' => 'primary',
                                        'completed' => 'info',
                                    ];
                                    $iconMap = [
                                        'pending'  => 'bi-hourglass-split',
                                        'approved' => 'bi-check-circle-fill',
                                        'rejected' => 'bi-x-circle-fill',
                                        'refund_processing' => 'bi-arrow-repeat',
                                        'completed' => 'bi-cash-coin',
                                    ];
                                    $badge = $badgeMap[$ret->status] ?? 'secondary';
                                    $icon = $iconMap[$ret->status] ?? 'bi-info-circle';
                                @endphp
                                <span class="badge bg-{{ $badge }} bg-opacity-10 text-{{ str_replace(' text-dark', '', $badge) }} border border-{{ str_replace(' text-dark', '', $badge) }} border-opacity-25 rounded-pill px-3 py-1 text-capitalize shadow-sm">
                                    <i class="bi {{ $icon }} me-1"></i>{{ ucfirst($ret->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-muted small fw-medium">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $ret->created_at->format('d M, Y') }}
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('vendor.returns.show', $ret->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                                    <i class="bi bi-eye-fill me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($returns->hasPages())
        <div class="card-footer bg-white border-top p-4 d-flex justify-content-center" style="border-radius: 0 0 16px 16px;">
            {{ $returns->links() }}
        </div>
    @endif
</div>
@endsection
