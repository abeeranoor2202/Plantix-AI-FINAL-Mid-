@extends('vendor.layouts.app')
@section('title', 'Coupon ' . $coupon->code)

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Coupon Details</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review coupon configuration and usage.</p>
        </div>
        <div class="d-flex gap-2">
            <x-button :href="route('vendor.coupons.edit', $coupon->id)" variant="primary" icon="fas fa-pen">Edit</x-button>
            <x-button :href="route('vendor.coupons.index')" variant="outline" icon="fas fa-arrow-left">Back</x-button>
        </div>
    </div>

    <x-card>
        <div class="p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="small text-muted">Code</div>
                    <div class="fw-bold text-dark">{{ $coupon->code }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Discount Type</div>
                    <div class="fw-bold text-dark">{{ $coupon->type === 'percentage' ? 'Percentage' : 'Fixed' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Value</div>
                    <div class="fw-bold text-dark">{{ $coupon->type === 'percentage' ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '%' : config('plantix.currency_symbol', 'PKR') . number_format((float) $coupon->value, 2) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Usage Limit</div>
                    <div class="fw-bold text-dark">{{ $coupon->usage_limit ?? 'Unlimited' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Used Count</div>
                    <div class="fw-bold text-dark">{{ (int) ($coupon->usages_count ?? $coupon->used_count ?? 0) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Status</div>
                    <x-badge :variant="$coupon->is_active ? 'success' : 'secondary'">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</x-badge>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Start Date</div>
                    <div class="fw-bold text-dark">{{ $coupon->starts_at ? $coupon->starts_at->format('d M Y, h:i A') : 'Not set' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Expiry Date</div>
                    <div class="fw-bold text-dark">{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y, h:i A') : 'Never' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Minimum Order</div>
                    <div class="fw-bold text-dark">{{ config('plantix.currency_symbol', 'PKR') . number_format((float) ($coupon->min_order ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection
