@extends('vendor.layouts.app')
@section('title', 'Orders Management')
@section('page-title', 'Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Orders</h4>
        <span class="text-muted small fw-medium d-block">Manage and track customer orders</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card-agri" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
        <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Order List</h4>
        <form method="GET" class="panel-filter-wrap">
            <select name="status" class="form-agri" style="min-width: 170px;">
                <option value="">All Statuses</option>
                @foreach(['pending','accepted','preparing','ready','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-agri" value="{{ request('date') }}" style="min-width: 170px;">
            <x-ui.button variant="primary" size="md" type="submit">Apply Filters</x-ui.button>
            <x-ui.button :href="route('vendor.orders.index')" variant="outline" size="md">Clear</x-ui.button>
        </form>
    </div>
</div>

<div class="card-agri border-0 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th class="py-3 px-4 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Order ID</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Customer</th>
                    <th class="py-3 text-center border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Items</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Total Amount</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Payment</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Status</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Date Ordered</th>
                    <th class="text-end py-3 px-4 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr style="background: white; border-bottom: 1px solid var(--sidebar-border); transition: background 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                    <td class="px-4 py-3">
                        <a href="{{ route('vendor.orders.show', $order->id) }}" class="fw-bold text-decoration-none" style="color: var(--agri-primary);">#{{ $order->id }}</a>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-dark">{{ $order->user->name ?? 'N/A' }}</div>
                        <div class="small text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 10px;"></i>{{ $order->user->phone ?? 'No phone' }}</div>
                    </td>
                    <td class="text-center py-3">
                        <span class="badge-agri bg-light text-dark border">{{ $order->order_items_count ?? $order->orderItems->count() }}</span>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-success fs-6">{{ config('plantix.currency_symbol') }}{{ number_format($order->total, 2) }}</div>
                    </td>
                    <td class="py-3">
                        <span class="badge-agri border {{ $order->payment_status === 'paid' ? 'badge-success-agri border-success border-opacity-25' : 'badge-warning-agri border-warning border-opacity-25' }}">
                            <i class="fas {{ $order->payment_status === 'paid' ? 'fa-check-circle' : 'fa-hourglass-half' }} me-1"></i>
                            {{ ucfirst($order->payment_status ?? 'pending') }}
                        </span>
                    </td>
                    <td class="py-3">
                        <span class="badge-agri border badge-{{ match($order->status) {
                                'pending'=>'warning',
                                'accepted'=>'info',
                                'preparing'=>'primary',
                                'ready'=>'success',
                                'delivered'=>'success',
                                'cancelled'=>'danger',
                                default=>'secondary'
                            } }}-agri border-{{ match($order->status) {
                                'pending'=>'warning',
                                'accepted'=>'info',
                                'preparing'=>'primary',
                                'ready'=>'success',
                                'delivered'=>'success',
                                'cancelled'=>'danger',
                                default=>'secondary'
                            } }} border-opacity-25">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="text-dark fw-medium small"><i class="far fa-calendar-alt text-muted me-1"></i>{{ $order->created_at->format('d M Y, h:i A') }}</div>
                    </td>
                    <td class="text-end px-4 py-3">
                        <div class="panel-action-group">
                            <x-ui.button :href="route('vendor.orders.show', $order->id)" variant="info-soft" size="sm" :circle="true" icon="fas fa-eye" title="View Order Details" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 border-0">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border border-dashed" style="width:100px; height:100px;">
                            <i class="fas fa-shopping-basket fs-1 text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No Orders Found</h5>
                        <p class="text-muted">You do not have any orders matching these criteria right now.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->hasPages())
    <div class="p-4 border-top bg-light text-center">
        {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
