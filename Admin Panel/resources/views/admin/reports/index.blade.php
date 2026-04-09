@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 28px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Reports</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 800; margin: 0; color: var(--agri-primary-dark);">Platform Analytics</h1>
            <p style="margin: 4px 0 0 0; color: var(--agri-text-muted);">Operational KPIs and commercial performance from {{ $from->toDateString() }} to {{ $to->toDateString() }}.</p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <a href="{{ route('admin.reports.sales', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-line"></i> Sales JSON
            </a>
        </div>
    </div>

    @php
        $delta = function ($current, $previous) {
            if ((float) $previous === 0.0) {
                return (float) $current > 0 ? 100.0 : 0.0;
            }
            return (($current - $previous) / $previous) * 100;
        };

        $cards = [
            [
                'label' => 'Orders (Period)',
                'value' => number_format((int) ($current['order_count'] ?? 0)),
                'delta' => $delta((float) ($current['order_count'] ?? 0), (float) ($previous['order_count'] ?? 0)),
                'icon' => 'fa-shopping-basket',
            ],
            [
                'label' => 'Revenue (Period)',
                'value' => config('plantix.currency_symbol') . number_format((float) ($current['revenue'] ?? 0), 2),
                'delta' => $delta((float) ($current['revenue'] ?? 0), (float) ($previous['revenue'] ?? 0)),
                'icon' => 'fa-sack-dollar',
            ],
            [
                'label' => 'Avg Order Value',
                'value' => config('plantix.currency_symbol') . number_format((float) ($current['avg_order'] ?? 0), 2),
                'delta' => $delta((float) ($current['avg_order'] ?? 0), (float) ($previous['avg_order'] ?? 0)),
                'icon' => 'fa-receipt',
            ],
            [
                'label' => 'New Customers',
                'value' => number_format((int) ($current['new_customers'] ?? 0)),
                'delta' => $delta((float) ($current['new_customers'] ?? 0), (float) ($previous['new_customers'] ?? 0)),
                'icon' => 'fa-user-plus',
            ],
        ];
    @endphp

    <div class="row g-4 mb-4">
        @foreach($cards as $card)
        <div class="col-xl-3 col-md-6">
            <div class="card-agri" style="padding: 22px; height: 100%;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 16px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center;">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                    <span style="font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 999px; background: {{ $card['delta'] >= 0 ? '#D1FAE5' : '#FEE2E2' }}; color: {{ $card['delta'] >= 0 ? '#065F46' : '#991B1B' }};">
                        {{ $card['delta'] >= 0 ? '+' : '' }}{{ number_format($card['delta'], 1) }}%
                    </span>
                </div>
                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--agri-text-muted);">{{ $card['label'] }}</p>
                <h3 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--agri-text-heading);">{{ $card['value'] }}</h3>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h4 style="margin: 0; font-size: 17px; font-weight: 800;">Reporting Endpoints</h4>
                    <small style="color: var(--agri-text-muted); font-weight: 700;">Live JSON feeds for dashboard widgets</small>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Dataset</th>
                                <th>Route</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sales Trend</td>
                                <td style="font-family: monospace;">admin.reports.sales</td>
                                <td class="text-end"><a href="{{ route('admin.reports.sales') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Open</a></td>
                            </tr>
                            <tr>
                                <td>Top Products</td>
                                <td style="font-family: monospace;">admin.reports.top-products</td>
                                <td class="text-end"><a href="{{ route('admin.reports.top-products') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Open</a></td>
                            </tr>
                            <tr>
                                <td>Top Vendors</td>
                                <td style="font-family: monospace;">admin.reports.top-vendors</td>
                                <td class="text-end"><a href="{{ route('admin.reports.top-vendors') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Open</a></td>
                            </tr>
                            <tr>
                                <td>Order Statuses</td>
                                <td style="font-family: monospace;">admin.reports.order-statuses</td>
                                <td class="text-end"><a href="{{ route('admin.reports.order-statuses') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Open</a></td>
                            </tr>
                            <tr>
                                <td>Refund Summary</td>
                                <td style="font-family: monospace;">admin.reports.refunds</td>
                                <td class="text-end"><a href="{{ route('admin.reports.refunds') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Open</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-agri" style="padding: 22px; height: 100%;">
                <h4 style="font-size: 16px; font-weight: 800; margin: 0 0 16px 0;">All-time Snapshot</h4>
                <div style="display: grid; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; background: var(--agri-bg);">
                        <span style="font-weight: 700; color: var(--agri-text-muted);">Total Orders</span>
                        <span style="font-weight: 800; color: var(--agri-text-heading);">{{ number_format($totalOrders) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; background: var(--agri-bg);">
                        <span style="font-weight: 700; color: var(--agri-text-muted);">Total Revenue</span>
                        <span style="font-weight: 800; color: var(--agri-text-heading);">{{ config('plantix.currency_symbol') }}{{ number_format((float) $totalRevenue, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; background: var(--agri-bg);">
                        <span style="font-weight: 700; color: var(--agri-text-muted);">Customers</span>
                        <span style="font-weight: 800; color: var(--agri-text-heading);">{{ number_format($totalCustomers) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; background: var(--agri-bg);">
                        <span style="font-weight: 700; color: var(--agri-text-muted);">Refunds Processed</span>
                        <span style="font-weight: 800; color: var(--agri-text-heading);">{{ config('plantix.currency_symbol') }}{{ number_format((float) $totalRefunds, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; background: #FEF3C7; border: 1px solid #FDE68A;">
                        <span style="font-weight: 700; color: #92400E;">Pending Actions</span>
                        <span style="font-weight: 800; color: #92400E;">{{ (int) $pendingOrders + (int) $pendingReturns }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
