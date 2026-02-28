@extends('vendor.layouts.app')
@section('title', 'Vendor Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-5">
    {{-- Summary Cards --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card-agri hover-lift p-4 h-100">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Orders</h6>
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-shopping-bag fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['total_orders'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small"><span class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>All Time</span></p>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card-agri hover-lift p-4 h-100 border border-warning border-opacity-25" style="border-left: 4px solid var(--agri-secondary) !important;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Orders</h6>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-clock fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['pending_orders'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small"><span class="text-warning fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Action Required</span></p>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card-agri hover-lift p-4 h-100">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Products</h6>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-boxes fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats['total_products'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small"><span class="text-muted">Active Listings</span></p>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card-agri hover-lift p-4 h-100 border border-danger border-opacity-25" style="{{ ($stats['low_stock'] ?? 0) > 0 ? 'border-left: 4px solid #DC2626 !important;' : '' }}">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="text-muted fw-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Low Stock Alerts</h6>
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-exclamation-triangle fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold {{ ($stats['low_stock'] ?? 0) > 0 ? 'text-danger' : 'text-dark' }} mb-1">{{ $stats['low_stock'] ?? 0 }}</h3>
            <p class="text-muted mb-0 small">Items near depletion</p>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Revenue Focus --}}
    <div class="col-lg-4 mb-4">
        <div class="card-agri p-0 h-100 overflow-hidden d-flex flex-column">
            <div class="p-4 bg-success bg-opacity-10 border-bottom border-success border-opacity-25 text-center flex-grow-1 d-flex flex-column justify-content-center">
                <h6 class="text-success fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="fas fa-chart-line me-2"></i>Today's Revenue</h6>
                <h2 class="fw-bold text-dark mb-0" style="font-size: 2.5rem;">
                    {{ config('plantix.currency_symbol') }}{{ number_format($stats['today_revenue'] ?? 0, 2) }}
                </h2>
            </div>
            <div class="p-4 bg-white text-center flex-grow-1 d-flex flex-column justify-content-center">
                <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">This Month</h6>
                <h3 class="fw-bold text-dark mb-0">
                    {{ config('plantix.currency_symbol') }}{{ number_format($stats['month_revenue'] ?? 0, 2) }}
                </h3>
            </div>
        </div>
    </div>

    {{-- Recent Orders List --}}
    <div class="col-lg-8 mb-4">
        <div class="card-agri p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark m-0"><i class="fas fa-clipboard-list text-muted me-2"></i>Recent Orders</h5>
                <a href="{{ route('vendor.orders.index') }}" class="btn-agri btn-agri-outline py-1 px-3" style="font-size: 13px;">View All Orders</a>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle" style="border-collapse: separate; border-spacing: 0 8px;">
                    <thead style="background: var(--agri-bg);">
                        <tr>
                            <th class="border-0 py-3 rounded-start px-3 text-muted text-uppercase" style="font-size: 12px; font-weight: 600;">Order ID</th>
                            <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 12px; font-weight: 600;">Customer</th>
                            <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 12px; font-weight: 600;">Amount</th>
                            <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 12px; font-weight: 600;">Status</th>
                            <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 12px; font-weight: 600;">Date</th>
                            <th class="border-0 py-3 rounded-end text-muted text-uppercase text-center" style="font-size: 12px; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders ?? [] as $order)
                        <tr style="background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <td class="border-bottom-0 py-3 px-3 rounded-start">
                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="fw-bold text-decoration-none" style="color: var(--agri-primary);">#{{ $order->id }}</a>
                            </td>
                            <td class="border-bottom-0 py-3 text-dark fw-medium">{{ $order->user->name ?? 'N/A' }}</td>
                            <td class="border-bottom-0 py-3 fw-bold">{{ config('plantix.currency_symbol') }}{{ number_format($order->grand_total, 2) }}</td>
                            <td class="border-bottom-0 py-3">
                                <span class="badge-agri badge-{{ match($order->order_status) {
                                    'pending'   => 'warning',
                                    'accepted'  => 'info',
                                    'preparing' => 'primary',
                                    'ready'     => 'success',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    default     => 'secondary'
                                } }}-agri">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="border-bottom-0 py-3 text-muted small">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="border-bottom-0 py-3 rounded-end text-center">
                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-sm btn-light border shadow-sm text-secondary rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-box-open fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark">No orders found</h6>
                                <p class="text-muted small">Your newest orders will appear here automatically.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
