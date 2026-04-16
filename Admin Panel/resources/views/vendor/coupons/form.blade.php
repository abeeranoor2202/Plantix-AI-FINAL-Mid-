@extends('vendor.layouts.app')
@section('title', $coupon ? 'Edit Coupon' : 'New Coupon')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <a href="{{ route('vendor.coupons.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Coupons</a>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                    <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $coupon ? 'Edit Coupon' : 'Create Coupon' }}</span>
                </div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $coupon ? 'Edit Coupon' : 'Create New Coupon' }}</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">{{ $coupon ? 'Update coupon settings and limits.' : 'Set up a new discount code for your customers.' }}</p>
            </div>
        </div>

        <div class="card-agri mb-5">
            <div class="card-body p-4 p-md-5">

                @if ($coupon)
                    <form action="{{ route('vendor.coupons.update', $coupon->id) }}" method="POST">
                        @csrf @method('PUT')
                @else
                    <form action="{{ route('vendor.coupons.store') }}" method="POST">
                        @csrf
                @endif

                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase fw-bold small mb-2">Coupon Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-upc-scan text-muted"></i></span>
                            <input type="text" name="code" class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 text-uppercase font-monospace text-dark @error('code') is-invalid @enderror"
                                   value="{{ old('code', $coupon?->code) }}"
                                   placeholder="Leave blank to auto-generate">
                        </div>
                        @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-text small mt-1"><i class="bi bi-info-circle me-1"></i>Letters and numbers only. Max 50 characters.</div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Discount Type <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-list-task text-muted"></i></span>
                                <select name="type" id="discountType" class="form-select form-select-lg fs-6 bg-light border-0 rounded-end-3 @error('type') is-invalid @enderror" required>
                                    <option value="percentage" {{ old('type', $coupon?->type) === 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)
                                    </option>
                                    <option value="fixed" {{ old('type', $coupon?->type) === 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount ({{ config('plantix.currency_symbol') }})
                                    </option>
                                </select>
                            </div>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2" id="valueLabel">Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3 fw-bold text-dark" id="valueSymbol">%</span>
                                <input type="number" step="0.01" name="value"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('value') is-invalid @enderror"
                                       value="{{ old('value', $coupon?->value) }}" min="0.01" required placeholder="0.00">
                            </div>
                            @error('value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Minimum Order Amount</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3 fw-bold">{{ config('plantix.currency_symbol') }}</span>
                                <input type="number" step="0.01" name="min_order"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('min_order') is-invalid @enderror"
                                       value="{{ old('min_order', $coupon?->min_order) }}" min="0" placeholder="0.00">
                            </div>
                            @error('min_order')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text small mt-1"><i class="bi bi-info-circle me-1"></i>Leave blank for no minimum.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Max Discount Cap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3 fw-bold">{{ config('plantix.currency_symbol') }}</span>
                                <input type="number" step="0.01" name="max_discount"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('max_discount') is-invalid @enderror"
                                       value="{{ old('max_discount', $coupon?->max_discount) }}" min="0" placeholder="0.00">
                            </div>
                            @error('max_discount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text small mt-1"><i class="bi bi-info-circle me-1"></i>Cap the maximum discount amount (for % coupons).</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Start Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-calendar-check text-muted"></i></span>
                                <input type="datetime-local" name="starts_at"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('starts_at') is-invalid @enderror"
                                       value="{{ old('starts_at', $coupon?->starts_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            @error('starts_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Expiry Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-calendar-x text-muted"></i></span>
                                <input type="datetime-local" name="expires_at"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('expires_at') is-invalid @enderror"
                                       value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            @error('expires_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-muted text-uppercase fw-bold small mb-2">Usage Limit</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-3"><i class="bi bi-people text-muted"></i></span>
                            <input type="number" name="usage_limit"
                                   class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('usage_limit') is-invalid @enderror"
                                   value="{{ old('usage_limit', $coupon?->usage_limit) }}"
                                   min="1" placeholder="Leave blank for unlimited uses">
                        </div>
                        @error('usage_limit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block fw-bold text-dark mb-1">Coupon Status</span>
                            <span class="d-block small text-muted">Allow customers to use this coupon</span>
                        </div>
                        <div class="form-check form-switch fs-4 mb-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                   {{ old('is_active', $coupon?->is_active ?? true) ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                        <x-button :href="route('vendor.coupons.index')" variant="outline">Cancel</x-button>
                        <x-button type="submit" :variant="$coupon ? 'primary' : 'primary'" icon="fas fa-save">{{ $coupon ? 'Update Coupon' : 'Create Coupon' }}</x-button>
                    </div>

                </form>

            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const typeSelect = document.getElementById('discountType');
const symbol     = document.getElementById('valueSymbol');

function updateSymbol() {
    symbol.textContent = typeSelect.value === 'percentage'
        ? '%'
        : '{{ config('plantix.currency_symbol') }}';
}
typeSelect.addEventListener('change', updateSymbol);
updateSymbol();
</script>
@endpush
