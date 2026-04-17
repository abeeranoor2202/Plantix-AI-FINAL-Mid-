@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
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
            <form method="GET" action="{{ route('admin.orders.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ strtoupper(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="dispute_status" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Disputes</option>
                    @foreach(['pending', 'vendor_responded', 'escalated', 'resolved', 'rejected', 'cancelled'] as $disputeStatus)
                        <option value="{{ $disputeStatus }}" @selected(request('dispute_status') === $disputeStatus)>{{ strtoupper(str_replace('_', ' ', $disputeStatus)) }}</option>
                    @endforeach
                </select>
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search orders..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <input type="number" min="0" step="0.01" name="min_total" class="form-agri" placeholder="Min amount" value="{{ request('min_total') }}" style="height: 42px; width: 130px; margin-bottom: 0;">
                <input type="number" min="0" step="0.01" name="max_total" class="form-agri" placeholder="Max amount" value="{{ request('max_total') }}" style="height: 42px; width: 130px; margin-bottom: 0;">
                <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Order ID</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Vendor</th>
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
                                <div style="font-weight: 700; color: var(--agri-primary-dark);">{{ $order->order_number }}</div>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4 py-3">{{ $order->user->name ?? 'Deleted User' }}</td>
                            <td class="px-4 py-3">{{ $order->vendor->title ?? 'No Vendor' }}</td>
                            <td class="px-4 py-3"><strong>{{ config('plantix.currency_symbol', 'PKR') }}{{ number_format($order->total, 2) }}</strong></td>
                            <td class="px-4 py-3">
                                @php($ps = strtolower((string) $order->payment_status))
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
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <button type="button" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none; opacity: .6; cursor: not-allowed;" title="Delete unavailable on this page" disabled>
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
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
