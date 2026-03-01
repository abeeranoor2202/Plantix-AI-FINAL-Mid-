@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Incentives & Campaign Registry</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                Promotional Incentives
            </h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage promotional discounts, marketing campaigns, and platform vouchers.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            @if($id != '')
                <a href="{!! route('admin.coupons.create') !!}/{{$id}}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                    <i class="fas fa-plus"></i> Generate Campaign
                </a>
            @else
                <a href="{!! route('admin.coupons.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                    <i class="fas fa-plus"></i> Generate Campaign
                </a>
            @endif
        </div>
    </div>

    {{-- Store Tabs (Visible if $id is present) --}}
    @if($id != '')
    <div style="display: flex; gap: 32px; border-bottom: 1px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 0; overflow-x: auto;">
        <a href="{{route('admin.vendors.view', $id)}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_basic')}}</a>
        <a href="{{route('admin.products.index')}}?storeId={{$id}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_items')}}</a>
        <a href="{{route('admin.orders.index')}}?storeId={{$id}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_orders')}}</a>
        <a href="{{route('admin.coupons')}}" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 800; font-size: 14px; border-bottom: 3px solid var(--agri-primary); white-space: nowrap;">{{trans('lang.tab_promos')}}</a>
        <a href="{{route('admin.vendors')}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_payouts')}}</a>
    </div>
    @endif

    {{-- Strategy Filters --}}
    <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div>
                    <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-text-heading); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Active Promotion Ledger</h4>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--agri-text-muted); font-weight: 600;">Monitor and control discount vectors across the ecosystem.</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px;"></i>
                    <input type="text" id="search-input" class="form-agri" placeholder="Scan by Hash or Campaign..." style="padding: 10px 16px 10px 44px; font-size: 13px; font-weight: 600; min-width: 280px;">
                </div>

                @if(in_array('coupons.delete', json_decode(@session('admin_permissions'), true)))
                    <a id="deleteAll" href="javascript:void(0)" class="btn-agri" style="color: var(--agri-error); font-size: 13px; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #FEF2F2; border-radius: 12px; border: 1px solid #FCA5A5;">
                        <i class="fas fa-trash-alt"></i> ELIMINATE SELECTED
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Coupons Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); color: var(--agri-primary); font-weight: 800; border-radius: 12px; z-index: 10; align-items: center; justify-content: center; height: 100%; width: 100%; position: absolute; top:0; left:0;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--agri-primary);"></div>
                <div>INITIALIZING TELEMETRY...</div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="couponTable" class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        @if(in_array('coupons.delete', json_decode(@session('admin_permissions'), true)))
                            <th style="padding: 20px 24px; border: none; width: 40px; border-top-left-radius: 12px;">
                                <div class="form-check" style="margin: 0; display: flex; justify-content: center;">
                                    <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer; width: 20px; height: 20px;">
                                </div>
                            </th>
                        @endif
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Incentive Hash</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Yield (Discount)</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Visibility</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Originating Node</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Expiration Vector</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Live Status</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none; border-top-right-radius: 12px;" class="text-end">Command</th>
                    </tr>
                </thead>
                <tbody id="append_list1">
                            @forelse($coupons as $coupon)
                            <tr>
                                <td><code style="font-weight:700;">{{ $coupon->code }}</code></td>
                                <td>{{ ucfirst($coupon->type) }}</td>
                                <td>{{ $coupon->type === 'percentage' ? $coupon->value.'%' : '$'.number_format($coupon->value,2) }}</td>
                                <td>{{ $coupon->vendor ? $coupon->vendor->title : 'All' }}</td>
                                <td>{{ $coupon->expires_at ? \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') : '—' }}</td>
                                <td>
                                    @if($coupon->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-success me-1" style="border-radius:8px;font-weight:700;">
                                        <i class="fas fa-edit me-1"></i>{{ trans('lang.edit') }}
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-coupon-btn" data-id="{{ $coupon->id }}" style="border-radius:8px;font-weight:700;">
                                        <i class="fas fa-trash me-1"></i>{{ trans('lang.delete') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No coupons found.</td></tr>
                            @endforelse
            </table>
        </div>
    </div>
</div>

<style>
    .badge-agri { padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; }
    .badge-agri-success { background: #DCFCE7; color: #166534; border-color: #BBF7D0; }
    .badge-agri-error { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }
    .badge-agri-primary { background: var(--agri-primary-light); color: var(--agri-primary); border-color: var(--agri-primary); }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
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
            if (confirm("Delete this coupon?")) {
                $.ajax({
                    url: '{{ url("admin/coupons/delete") }}/' + id,
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
