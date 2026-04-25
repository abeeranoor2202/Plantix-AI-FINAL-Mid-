@extends('vendor.layouts.app')
@section('title', 'Coupons')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Coupons</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage your discount coupons</p>
        </div>
        <x-button :href="route('vendor.coupons.create')" variant="primary" icon="fas fa-plus">Create Coupon</x-button>
    </div>

    <x-card class="mb-4">
        <div class="p-3 p-lg-4">
            <form method="GET" action="{{ route('vendor.coupons.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-agri border-start-0" style="margin-bottom:0;" placeholder="Search code" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Status</label>
                    <select name="status" class="form-agri">
                        <option value="">All</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Expiry</label>
                    <select name="expiry" class="form-agri">
                        <option value="">All</option>
                        <option value="expired" {{ ($filters['expiry'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="expiring_soon" {{ ($filters['expiry'] ?? '') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="no_expiry" {{ ($filters['expiry'] ?? '') === 'no_expiry' ? 'selected' : '' }}>No Expiry</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <x-button type="submit" variant="primary" class="w-100">Apply Filters</x-button>
                    <x-button :href="route('vendor.coupons.index')" variant="outline" class="w-100">Clear</x-button>
                </div>
            </form>
        </div>
    </x-card>

    <x-card>
        @if($coupons->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border mb-3" style="width:72px;height:72px;">
                    <i class="fas fa-ticket-alt text-muted fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No coupons created yet</h6>
                <p class="text-muted small mb-3">Create your first discount coupon to start offering deals.</p>
                <x-button :href="route('vendor.coupons.create')" variant="primary" icon="fas fa-plus">Create Coupon</x-button>
            </div>
        @else
            <x-table>
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3 small text-muted text-uppercase">Code</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Discount Type</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Value</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Usage Limit</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Used Count</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Status</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Expiry Date</th>
                        <th class="px-4 py-3 small text-muted text-uppercase text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($coupons as $coupon)
                        @php $usedCount = (int) ($coupon->usages_count ?? $coupon->used_count ?? 0); @endphp
                        <tr>
                            <td class="px-4 py-3 fw-bold text-dark">{{ $coupon->code }}</td>
                            <td class="px-4 py-3">{{ $coupon->type === 'percentage' ? 'Percentage' : 'Fixed' }}</td>
                            <td class="px-4 py-3">
                                {{ $coupon->type === 'percentage' ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '%' : config('plantix.currency_symbol', 'PKR') . number_format((float) $coupon->value, 2) }}
                            </td>
                            <td class="px-4 py-3">{{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                            <td class="px-4 py-3">{{ $usedCount }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('vendor.coupons.toggle', $coupon->id) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <div class="form-check form-switch p-0 m-0">
                                        <input class="form-check-input ms-0" type="checkbox" role="switch" @checked($coupon->is_active) onchange="this.form.submit()" style="width: 36px; height: 18px; cursor: pointer;">
                                    </div>
                                    <span class="badge rounded-pill" style="background: {{ $coupon->is_active ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $coupon->is_active ? '#059669' : '#64748b' }}; font-weight: 700; font-size: 10px; text-transform: uppercase; padding: 4px 10px;">
                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </form>
                            </td>
                            <td class="px-4 py-3">{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'Never' }}</td>
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('vendor.coupons.show', $coupon->id) }}" class="btn-action btn-action-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('vendor.coupons.edit', $coupon->id) }}" class="btn-action btn-action-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="btn-action btn-action-delete" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteCouponModal{{ $coupon->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="deleteCouponModal{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('vendor.coupons.destroy', $coupon->id) }}" method="POST" class="modal-content">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Coupon</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-0">Are you sure you want to delete coupon <strong>{{ $coupon->code }}</strong>? This action cannot be undone.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </x-table>

            @if($coupons->hasPages())
                <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                    {{ $coupons->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
