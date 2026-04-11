@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Coupons</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Promotional Incentives</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage promotional discounts and campaigns.</p>
        </div>
        @if($id != '')
            <a href="{{ route('admin.coupons.create') }}/{{ $id }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                <i class="fas fa-plus"></i> Generate Campaign
            </a>
        @else
            <a href="{{ route('admin.coupons.create') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                <i class="fas fa-plus"></i> Generate Campaign
            </a>
        @endif
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Coupon List</h4>
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                    <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                </span>
                <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search coupons..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
            </div>
        </div>

        <div class="table-responsive">
            <table id="couponTable" class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Code</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Type</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Value</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Vendor</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Expiry</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="px-4 py-3"><span style="font-weight: 700; color: var(--agri-text-heading);">{{ $coupon->code }}</span></td>
                            <td class="px-4 py-3">{{ ucfirst($coupon->type) }}</td>
                            <td class="px-4 py-3">{{ $coupon->type === 'percentage' ? $coupon->value.'%' : '$'.number_format($coupon->value, 2) }}</td>
                            <td class="px-4 py-3">{{ $coupon->vendor ? $coupon->vendor->title : 'All' }}</td>
                            <td class="px-4 py-3">{{ $coupon->expires_at ? \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <button class="btn-agri delete-coupon-btn" data-id="{{ $coupon->id }}" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No coupons found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var csrfToken = '{{ csrf_token() }}';
$(document).ready(function () {
    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#couponTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    $(document).on('click', '.delete-coupon-btn', function () {
        var id = $(this).data('id');
        if (confirm('Delete this coupon?')) {
            $.ajax({
                url: '{{ route("admin.coupons.destroy", ["id" => "__ID__"]) }}'.replace('__ID__', id),
                method: 'POST',
                data: { _method: 'DELETE', _token: csrfToken },
                success: function () { location.reload(); },
                error: function () { alert('Delete failed.'); }
            });
        }
    });
});
</script>
@endsection
