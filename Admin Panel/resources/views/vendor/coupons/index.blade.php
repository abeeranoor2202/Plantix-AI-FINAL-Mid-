@extends('vendor.layouts.app')
@section('title', 'My Coupons')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Discount Coupons</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage discount coupons for your store.</p>
        </div>
        <x-button :href="route('vendor.coupons.create')" variant="primary" icon="fas fa-plus">New Coupon</x-button>
    </div>

    <x-card style="padding: 0; overflow: hidden;">
        <x-slot name="header">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Coupon List</h4>
        </x-slot>
        @if ($coupons->isEmpty())
            <div class="text-center text-muted py-5 my-3">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-tag fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No coupons yet</h6>
                <p class="small mb-3">You haven't created any discount codes for your customers.</p>
                <x-button :href="route('vendor.coupons.create')" variant="outline">Create your first coupon</x-button>
            </div>
        @else
            <x-table>
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
                                        <x-badge variant="success">Active</x-badge>
                                    @elseif (!$coupon->is_active)
                                        <x-badge variant="secondary">Disabled</x-badge>
                                    @else
                                        <x-badge variant="warning">Expired/Maxed</x-badge>
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
                                        <form action="{{ route('vendor.coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <x-toggle :checked="$coupon->is_active" onchange="this.form.submit()" />
                                        </form>

                                        <x-button :href="route('vendor.coupons.edit', $coupon->id)" variant="icon" title="Edit" style="color: var(--agri-primary); background: var(--agri-bg); width:34px; height:34px;"><i class="fas fa-pen"></i></x-button>

                                        <form action="{{ route('vendor.coupons.destroy', $coupon->id) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                            @csrf @method('DELETE')
                                            <x-button type="submit" variant="icon" title="Delete" style="color:#ef4444; background:#fef2f2; width:34px; height:34px;"><i class="fas fa-trash"></i></x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
            </x-table>
            @if($coupons->hasPages())
                <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                    {{ $coupons->links() }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
