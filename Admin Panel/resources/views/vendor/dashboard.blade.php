@extends('vendor.layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="card-agri mb-4" style="border: none; background: transparent; box-shadow: none; padding: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h2 style="font-size: 24px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Business Analytics</h2>
            <div style="background: var(--agri-white); padding: 8px 16px; border-radius: 12px; border: 1px solid var(--agri-border); font-size: 14px; font-weight: 500; color: var(--agri-text-muted);">Real-time Data</div>
        </div>

        <div class="row g-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card-agri">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div style="background: var(--agri-primary-light); padding:10px; border-radius:12px;"><i class="mdi mdi-receipt-text-outline" style="color:var(--agri-primary);font-size:24px;"></i></div>
                    </div>
                    <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Total Orders</h5>
                    <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $stats['total_orders'] ?? 0 }}</h2>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card-agri">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div style="background: #FFFBEB; padding:10px; border-radius:12px;"><i class="mdi mdi-clock-outline" style="color:var(--agri-secondary);font-size:24px;"></i></div>
                    </div>
                    <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Pending Orders</h5>
                    <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['pending_orders'] ?? 0 }}</h2>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card-agri">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div style="background: #EFF6FF; padding:10px; border-radius:12px;"><i class="mdi mdi-cart-outline" style="color: var(--agri-info);font-size:24px;"></i></div>
                    </div>
                    <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Total Products</h5>
                    <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['total_products'] ?? 0 }}</h2>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card-agri">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                        <div style="background: #F0FDF4; padding:10px; border-radius:12px;"><i class="mdi mdi-alert-outline" style="color: var(--agri-primary-hover);font-size:24px;"></i></div>
                    </div>
                    <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Low Stock Alerts</h5>
                    <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['low_stock'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-12 mb-4">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Recent Orders</h3>
                    <a href="{{ route('vendor.orders.index') }}" style="color: var(--agri-primary); font-size: 14px; font-weight: 600; text-decoration: none;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table" style="margin: 0;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">ID</th>
                                <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">Customer</th>
                                <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">Amount</th>
                                <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">Status</th>
                                <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders ?? [] as $order)
                                <tr>
                                    <td style="padding: 12px 24px;">#{{ $order->id }}</td>
                                    <td style="padding: 12px 24px;">{{ $order->user->name ?? 'N/A' }}</td>
                                    <td style="padding: 12px 24px;">{{ config('plantix.currency_symbol') }}{{ number_format($order->total, 2) }}</td>
                                    <td style="padding: 12px 24px;"><span class="badge rounded-pill bg-info">{{ strtoupper($order->status) }}</span></td>
                                    <td style="padding: 12px 24px;"><a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5" style="color: var(--agri-text-muted);">No recent orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
