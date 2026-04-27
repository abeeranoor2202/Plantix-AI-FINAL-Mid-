@extends('vendor.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Orders</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Orders</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage and track all customer orders.</p>
        </div>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 20px;">Order List</h4>
                <form method="GET" action="{{ route('vendor.orders.index') }}" class="d-flex gap-2" style="flex: 1; max-width: 500px;">
                    <div class="agri-search-wrap" style="flex: 1;">
                        <i class="fas fa-search agri-search-icon"></i>
                        <input type="text" name="search" class="form-agri agri-search-input" placeholder="Search by Order ID or Customer..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; white-space: nowrap;">Search</button>
                </form>
            </div>

            <form method="GET" action="{{ route('vendor.orders.index') }}" class="mb-4">
                {{-- Keep search in hidden if we submit from here, though we have two forms now, better to consolidate or handle sync --}}
                <input type="hidden" name="search" value="{{ request('search') }}">
                
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="agri-label-small">Order Status</label>
                        <select name="status" class="form-agri" style="margin-bottom: 0;">
                            <option value="">All Statuses</option>
                            @foreach($statuses ?? ['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ strtoupper(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="agri-label-small">Amount Range</label>
                        <div class="d-flex gap-2">
                            <div class="position-relative w-100">
                                <span class="input-icon-left"><i class="fas fa-arrow-down" style="font-size: 10px;"></i></span>
                                <input type="number" name="min_total" class="form-agri ps-4" placeholder="Min" value="{{ request('min_total') }}" style="margin-bottom: 0;">
                            </div>
                            <div class="position-relative w-100">
                                <span class="input-icon-left"><i class="fas fa-arrow-up" style="font-size: 10px;"></i></span>
                                <input type="number" name="max_total" class="form-agri ps-4" placeholder="Max" value="{{ request('max_total') }}" style="margin-bottom: 0;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="agri-label-small">Date Range</label>
                        <div class="d-flex gap-2">
                            <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}" style="margin-bottom: 0;">
                            <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}" style="margin-bottom: 0;">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pb-2">
                    <a href="{{ route('vendor.orders.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <button type="submit" class="btn-agri btn-agri-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Order ID</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Items</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Amount</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Payment</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Order Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-primary-dark);">{{ $order->order_number ?? ('#'.$order->id) }}</div>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4 py-3">{{ $order->user->name ?? 'Deleted User' }}</td>
                            <td class="px-4 py-3">{{ $order->order_items_count ?? $order->items->count() }}</td>
                            <td class="px-4 py-3"><strong>{{ config('plantix.currency_symbol', 'PKR') }}{{ number_format($order->total, 2) }}</strong></td>
                            <td class="px-4 py-3">
                                @php($ps = strtolower((string) ($order->payment_status ?? 'pending')))
                                <span class="badge rounded-pill {{ $ps === 'paid' ? 'bg-success' : ($ps === 'pending' ? 'bg-warning' : 'bg-danger') }}">{{ strtoupper($ps) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-column gap-2">
                                    <span class="badge rounded-pill bg-info" style="width: fit-content;">{{ strtoupper(str_replace('_', ' ', (string) $order->status)) }}</span>

                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end">
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-action btn-action-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-action btn-action-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .agri-search-wrap {
        position: relative;
    }

    .agri-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--agri-text-muted);
        font-size: 14px;
        pointer-events: none;
    }

    .agri-search-input {
        margin-bottom: 0;
        height: 42px;
        padding-left: 36px;
    }

    .agri-label-small {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--agri-text-muted);
        margin-bottom: 6px;
        display: block;
        letter-spacing: 0.5px;
    }

    .input-icon-left {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--agri-text-muted);
        z-index: 5;
        pointer-events: none;
    }
</style>
@endpush
