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
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Order List</h4>
            <form method="GET" action="{{ route('vendor.orders.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    @foreach($statuses ?? ['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ strtoupper(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="dispute_status" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Disputes</option>
                    @foreach(['pending', 'vendor_responded', 'escalated', 'resolved', 'rejected', 'cancelled'] as $disputeStatus)
                        <option value="{{ $disputeStatus }}" @selected(request('dispute_status') === $disputeStatus)>{{ strtoupper(str_replace('_', ' ', $disputeStatus)) }}</option>
                    @endforeach
                </select>
                <div class="agri-search-wrap" style="width: 320px;">
                    <i class="fas fa-search agri-search-icon"></i>
                    <input type="text" name="search" class="form-agri agri-search-input" placeholder="Search orders..." value="{{ request('search') }}">
                </div>
                <input type="number" min="0" step="0.01" name="min_total" class="form-agri" placeholder="Min amount" value="{{ request('min_total') }}" style="height: 42px; width: 130px; margin-bottom: 0;">
                <input type="number" min="0" step="0.01" name="max_total" class="form-agri" placeholder="Max amount" value="{{ request('max_total') }}" style="height: 42px; width: 130px; margin-bottom: 0;">
                <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                <a href="{{ route('vendor.orders.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
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
                                    @if(($order->dispute_status ?? 'none') !== 'none')
                                        <span class="badge rounded-pill bg-warning text-dark" style="width: fit-content;">DISPUTE: {{ strtoupper(str_replace('_', ' ', $order->dispute_status)) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <button
                                        type="button"
                                        class="btn-agri js-order-delete-trigger"
                                        style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;"
                                        data-order-id="{{ $order->id }}"
                                        data-order-number="{{ $order->order_number ?? ('#'.$order->id) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteOrderModal"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--agri-border);">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderModalLabel">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to delete this order?</p>
                <p class="mb-0 text-muted" id="deleteOrderHelpText" style="font-size: 13px;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteOrderForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Order</button>
                </form>
            </div>
        </div>
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteForm = document.getElementById('deleteOrderForm');
    const helpText = document.getElementById('deleteOrderHelpText');

    document.querySelectorAll('.js-order-delete-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-order-id');
            const orderNumber = this.getAttribute('data-order-number');
            deleteForm.action = '{{ route('vendor.orders.destroy', '__ORDER__') }}'.replace('__ORDER__', orderId);
            helpText.textContent = 'Order ' + orderNumber + ' will be removed from your vendor order list.';
        });
    });
});
</script>
@endpush
