@extends('layouts.app')

@section('title', 'Fulfillment Command Center')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">E-Commerce Hub</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Transaction Telemetry</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Multi-Channel Fulfillment Hub</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Provide real-time oversight of cross-platform commerce velocity and logistics routing.</p>
        </div>
        <div style="display: flex; gap: 16px;">
            <div style="background: white; padding: 10px 20px; border-radius: 14px; border: 1px solid var(--agri-border); font-size: 13px; font-weight: 800; color: var(--agri-primary); display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <i class="fas fa-satellite-dish"></i>
                LIVE TELEMETRY: {{ $orders->total() }} ACTIVE NODES
            </div>
        </div>
    </div>

    {{-- Strategy Filters --}}
    <div class="card-agri mb-4" style="padding: 24px 32px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: white;">
        <form method="GET" action="{{ route('admin.orders.index') }}">
            <div class="row g-4">
                <div class="col-lg-4">
                    <label class="agri-filter-label">Transaction Identifier</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-primary); opacity: 0.6;"></i>
                        <input type="text" name="search" class="form-agri" style="padding-left: 44px; font-size: 14px; font-weight: 600;"
                               placeholder="Scan Order ID or Account Name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="agri-filter-label">Logistics State</label>
                    <select name="status" class="form-agri" style="font-size: 14px; font-weight: 600;">
                        <option value="">All Fulfillment States</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>
                                {{ strtoupper(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="agri-filter-label">Fulfillment Partner ID</label>
                    <input type="number" name="vendor_id" class="form-agri" style="font-size: 14px; font-weight: 600;"
                           placeholder="Filter by Node ID" value="{{ request('vendor_id') }}">
                </div>
                <div class="col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; font-weight: 800; letter-spacing: 0.5px;">
                        ENGAGE FILTERS
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn-agri btn-agri-outline" style="min-width: 90px; text-decoration: none; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                        RESET
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Fulfillment Ledger Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: white;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Transaction Nexus</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Engaged Parties</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Cargo Volume</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Financial Exchange</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Settlement Status</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Logistics State</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Command</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="order-row" style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                            <td style="padding: 24px 32px;">
                                <a href="{{ route('admin.orders.show', $order->id) }}" style="text-decoration: none; display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 44px; height: 44px; background: var(--agri-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--agri-primary); border: 1px solid var(--agri-border);">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: var(--agri-primary-dark); font-size: 15px;">{{ $order->order_number }}</div>
                                        <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 700; margin-top: 4px; text-transform: uppercase;">{{ $order->created_at->format('M d, Y • H:i') }}</div>
                                    </div>
                                </a>
                            </td>
                            <td style="padding: 24px 32px;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 20px; height: 20px; background: var(--agri-primary-light); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: var(--agri-primary);">
                                            <i class="fas fa-user" style="font-size: 10px;"></i>
                                        </div>
                                        <span style="font-weight: 700; color: var(--agri-text-heading); font-size: 13px;">{{ $order->user->name ?? 'Account Terminated' }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 20px; height: 20px; background: var(--agri-bg); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: var(--agri-secondary);">
                                            <i class="fas fa-store" style="font-size: 10px;"></i>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">{{ $order->vendor->name ?? 'Direct Fulfillment' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 24px 32px;" class="text-center">
                                <div style="background: var(--agri-bg); color: var(--agri-primary-dark); padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--agri-border);">
                                    <i class="fas fa-box" style="font-size: 10px; opacity: 0.5;"></i> {{ $order->items->count() }} Assets
                                </div>
                            </td>
                            <td style="padding: 24px 32px;">
                                <div style="font-weight: 900; color: var(--agri-primary-dark); font-size: 16px; letter-spacing: -0.5px;">
                                    <span style="font-size: 12px; font-weight: 700; opacity: 0.6; margin-right: 2px;">{{ config('plantix.currency_symbol', 'PKR') }}</span>{{ number_format($order->total, 2) }}
                                </div>
                            </td>
                            <td style="padding: 24px 32px;" class="text-center">
                                @php
                                    $ps = $order->payment_status;
                                    $pColor = $ps === 'paid' ? '#059669' : ($ps === 'pending' ? '#B45309' : '#DC2626');
                                    $pBg = $ps === 'paid' ? '#D1FAE5' : ($ps === 'pending' ? '#FEF3C7' : '#FEE2E2');
                                    $pIcon = $ps === 'paid' ? 'fa-check-circle' : ($ps === 'pending' ? 'fa-clock' : 'fa-times-circle');
                                @endphp
                                <div style="background: {{ $pBg }}; color: {{ $pColor }}; padding: 6px 14px; border-radius: 100px; font-size: 10px; font-weight: 900; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px; border: 1px solid {{ $pColor }}30;">
                                    <i class="fas {{ $pIcon }}"></i> {{ $ps }}
                                </div>
                            </td>
                            <td style="padding: 24px 32px;" class="text-center">
                                @php
                                    $bc = [
                                        'pending'         => ['#B45309', '#FEF3C7'],
                                        'accepted'        => ['#1D4ED8', '#DBEAFE'],
                                        'preparing'       => ['#6D28D9', '#EDE9FE'],
                                        'ready'           => ['#4338CA', '#E0E7FF'],

                                        'picked_up'       => ['#047857', '#D1FAE5'],
                                        'delivered'       => ['#047857', '#D1FAE5'],
                                        'rejected'        => ['#B91C1C', '#FEE2E2'],
                                        'cancelled'       => ['#B91C1C', '#FEE2E2'],
                                    ];
                                    $currentStatus = $bc[$order->status] ?? ['#4B5563', '#F3F4F6'];
                                @endphp
                                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $currentStatus[0] }}; background: {{ $currentStatus[1] }}; padding: 6px 16px; border-radius: 12px; font-size: 11px; font-weight: 900; border: 1px solid {{ $currentStatus[0] }}40; text-transform: uppercase;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $currentStatus[0] }}; box-shadow: 0 0 0 2px {{ $currentStatus[0] }}30;"></span>
                                    {{ str_replace('_', ' ', $order->status) }}
                                </div>
                            </td>
                            <td style="padding: 24px 32px;" class="text-end">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-agri" style="padding: 10px 16px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; text-decoration: none; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
                                    OVERVIEW <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 100px 32px; text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                                    <div style="width: 80px; height: 80px; background: var(--agri-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--agri-text-muted); font-size: 32px;">
                                        <i class="fas fa-satellite" style="opacity: 0.4;"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-weight: 800; color: var(--agri-text-heading);">TELEMETRY DEADZONE</h4>
                                        <p style="margin: 8px 0 0 0; font-size: 14px; color: var(--agri-text-muted); max-width: 400px;">No active transactions detected within your specified filtering parameters.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Section --}}
    @if($orders->hasPages())
        <div style="margin-top: 32px; display: flex; justify-content: center;">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
    .agri-filter-label { font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: block; }
    .order-row:hover { background: var(--agri-bg); }
    .pagination { gap: 8px; border: none; }
    .page-link { border-radius: 12px !important; border: 1px solid var(--agri-border) !important; color: var(--agri-text-heading) !important; font-weight: 700 !important; padding: 10px 18px !important; }
    .page-item.active .page-link { background: var(--agri-primary) !important; border-color: var(--agri-primary) !important; color: white !important; }
</style>
@endsection
