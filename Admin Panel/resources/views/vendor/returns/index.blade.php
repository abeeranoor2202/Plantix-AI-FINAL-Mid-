@extends('vendor.layouts.app')
@section('title', 'Returns & Refunds')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Return Requests</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage and process customer return and refund requests.</p>
        </div>
    </div>

    <x-card style="padding: 0; overflow: hidden;">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center" style="gap:10px; flex-wrap:wrap;">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">All Returns</h4>
                <form method="GET" action="{{ route('vendor.returns.index') }}" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-agri" style="height:42px; min-width:160px; margin-bottom:0;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </x-slot>
        @if($returns->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No returns found</h6>
                <p class="small mb-0">There are currently no return requests matching your criteria.</p>
            </div>
        @else
            <x-table>
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
                                <x-button :href="route('vendor.returns.show', $ret->id)" variant="icon" title="View" style="color: #2563eb; background: var(--agri-bg); width:34px; height:34px;"><i class="fas fa-eye"></i></x-button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </x-table>
        @endif
    </x-card>
    @if($returns->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
            {{ $returns->links() }}
        </div>
    @endif
</div>
@endsection
