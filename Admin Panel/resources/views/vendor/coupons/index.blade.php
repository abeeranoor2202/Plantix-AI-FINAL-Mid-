@extends('vendor.layouts.app')
@section('title', 'My Coupons')
@section('page-title', 'Discount Coupons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-tags-fill me-2 text-success"></i>Discount Coupons</h4>
        <span class="text-muted small fw-medium mt-1 d-block">Create and manage discount coupons for your store</span>
    </div>
    <a href="{{ route('vendor.coupons.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="bi bi-plus-lg me-1"></i>New Coupon
    </a>
</div>

<div class="card border-0 shadow-sm hover-card" style="border-radius:16px;">
    <div class="card-body p-0">
        @if ($coupons->isEmpty())
            <div class="text-center text-muted py-5 my-3">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-tag fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No coupons yet</h6>
                <p class="small mb-3">You haven't created any discount codes for your customers.</p>
                <a href="{{ route('vendor.coupons.create') }}" class="btn btn-outline-primary rounded-pill px-4">Create your first coupon</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small">Code</th>
                            <th class="fw-semibold text-muted text-uppercase small">Type</th>
                            <th class="fw-semibold text-muted text-uppercase small">Status</th>
                            <th class="fw-semibold text-muted text-uppercase small">Value</th>
                            <th class="fw-semibold text-muted text-uppercase small">Min. Order</th>
                            <th class="fw-semibold text-muted text-uppercase small">Usage</th>
                            <th class="fw-semibold text-muted text-uppercase small">Expires</th>
                            <th class="text-end pe-4 fw-semibold text-muted text-uppercase small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coupons as $coupon)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-inline-flex align-items-center bg-light border border-secondary border-opacity-25 rounded px-2 py-1 shadow-sm">
                                        <i class="bi bi-ticket-detailed text-muted me-2 small"></i>
                                        <strong class="font-monospace text-dark tracking-wide">{{ $coupon->code }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center text-secondary small fw-medium">
                                        @if($coupon->type === 'percentage')
                                            <i class="bi bi-percent me-1"></i>Percent
                                        @else
                                            <i class="bi bi-currency-dollar me-1"></i>Fixed
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if ($coupon->is_active && $coupon->isValid())
                                        <span class="badge rounded-pill border border-success text-success bg-success bg-opacity-10 px-3 py-1 fw-bold shadow-sm"><i class="bi bi-check-circle me-1"></i>Active</span>
                                    @elseif (!$coupon->is_active)
                                        <span class="badge rounded-pill border border-secondary text-secondary bg-secondary bg-opacity-10 px-3 py-1 fw-bold shadow-sm"><i class="bi bi-ban me-1"></i>Disabled</span>
                                    @else
                                        <span class="badge rounded-pill border border-warning text-dark bg-warning bg-opacity-25 px-3 py-1 fw-bold shadow-sm"><i class="bi bi-exclamation-triangle me-1"></i>Expired/Maxed</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark fs-6">
                                    @if ($coupon->type === 'percentage')
                                        {{ $coupon->value }}%
                                    @else
                                        {{ config('plantix.currency_symbol') }}{{ number_format($coupon->value, 2) }}
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small fw-medium">
                                        {{ $coupon->min_order ? config('plantix.currency_symbol') . number_format($coupon->min_order, 2) : 'No min' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2 rounded-pill" style="width: 50px; height: 6px;">
                                            @php
                                                $usedCount = isset($coupon->usages_count) ? (int) $coupon->usages_count : (int) $coupon->used_count;
                                                $pct = $coupon->usage_limit ? min(100, ($usedCount / $coupon->usage_limit) * 100) : 0;
                                                $bgClass = $pct >= 90 ? 'bg-danger' : ($pct >= 50 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $bgClass }}" role="progressbar" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="small fw-bold text-dark">{{ $usedCount }}</span>
                                        @if ($coupon->usage_limit)
                                            <span class="small text-muted ms-1">/ {{ $coupon->usage_limit }}</span>
                                        @else
                                            <i class="bi bi-infinity small text-muted ms-1" title="Unlimited"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($coupon->expires_at)
                                        <div class="d-flex align-items-center {{ $coupon->expires_at->isPast() ? 'text-danger fw-bold' : 'text-muted fw-medium' }} small">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $coupon->expires_at->format('d M, Y') }}
                                        </div>
                                    @else
                                        <span class="text-muted small align-items-center d-flex fw-medium"><i class="bi bi-infinity me-1"></i>Never</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Toggle active --}}
                                        <form action="{{ route('vendor.coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm {{ $coupon->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    style="width: 32px; height: 32px;" title="{{ $coupon->is_active ? 'Disable Coupon' : 'Enable Coupon' }}">
                                                <i class="bi bi-{{ $coupon->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                                            </button>
                                        </form>

                                        <a href="{{ route('vendor.coupons.edit', $coupon->id) }}"
                                           class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Edit Coupon">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form action="{{ route('vendor.coupons.destroy', $coupon->id) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Delete Coupon">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($coupons->hasPages())
                <div class="p-4 border-top">
                    {{ $coupons->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
