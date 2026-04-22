@extends('layouts.dashboard')

@section('title', 'My Orders | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Main Content -->
            <div class="col-12">
                <div class="card-agri p-4" style="border: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 20px;">Order History</h3>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('shop') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 8px 16px; font-size: 14px;">Continue Shopping</a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('orders') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="agri-label">Search</label>
                            <input type="text" name="search" class="form-agri" value="{{ request('search') }}" placeholder="Order #, ID, product name">
                        </div>
                        <div class="col-md-2">
                            <label class="agri-label">Status</label>
                            <select name="status" class="form-agri">
                                <option value="">All Statuses</option>
                                @foreach(['draft','pending_payment','payment_failed','pending','confirmed','processing','shipped','delivered','completed','cancelled','rejected','return_requested','returned','refunded'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ strtoupper(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="agri-label">Dispute</label>
                            <select name="dispute_status" class="form-agri">
                                <option value="">All Disputes</option>
                                @foreach(['pending', 'vendor_responded', 'escalated', 'resolved', 'rejected', 'cancelled'] as $disputeStatus)
                                    <option value="{{ $disputeStatus }}" @selected(request('dispute_status') === $disputeStatus)>{{ strtoupper(str_replace('_', ' ', $disputeStatus)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">Min</label>
                            <input type="number" min="0" step="0.01" name="min_total" class="form-agri" value="{{ request('min_total') }}" placeholder="0">
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">Max</label>
                            <input type="number" min="0" step="0.01" name="max_total" class="form-agri" value="{{ request('max_total') }}" placeholder="0">
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">From</label>
                            <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">To</label>
                            <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-12 d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn-agri btn-agri-primary" style="padding: 8px 16px; font-size: 14px;">Apply Filters</button>
                            <a href="{{ route('orders') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 8px 16px; font-size: 14px;">Reset</a>
                        </div>
                    </form>

                    <div id="ordersListTable" class="table-responsive">
                        <table class="table align-middle" style="border-collapse: separate; border-spacing: 0 10px;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th class="border-0 py-3 rounded-start" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Order #</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Date</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Items</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Total</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Status</th>
                                    <th class="border-0 py-3 rounded-end" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td class="border-bottom-0 py-3 rounded-start fw-bold text-dark">#{{ $order->id }}</td>
                                    <td class="border-bottom-0 py-3 text-muted" style="font-size: 13px;">{{ $order->created_at->format('d M Y') }}</td>
                                    <td class="border-bottom-0 py-3 text-muted">{{ $order->items->count() }} item(s)</td>
                                    <td class="border-bottom-0 py-3 fw-bold text-dark">PKR {{ number_format($order->total ?? 0, 2) }}</td>
                                    <td class="border-bottom-0 py-3">
                                        <div class="d-flex flex-column gap-2">
                                            <x-platform.status-badge domain="order" :status="$order->status" />
                                            @if(($order->dispute_status ?? 'none') !== 'none')
                                                <div>
                                                    <small class="text-muted d-block mb-1" style="font-size: 11px;">Dispute</small>
                                                    <x-platform.status-badge domain="dispute" :status="$order->dispute_status" />
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="border-bottom-0 py-3 rounded-end">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('order.details', $order->id) }}"
                                               class="btn-agri text-decoration-none"
                                               style="padding: 6px 12px; font-size: 13px; background: var(--agri-bg); color: var(--agri-text-main);">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order->status === 'pending_payment')
                                            <a href="{{ route('checkout.stripe.pay', $order->id) }}"
                                               class="btn-agri btn-agri-primary text-decoration-none"
                                               style="padding: 6px 14px; font-size: 13px;">
                                                <i class="fas fa-credit-card me-1"></i> Pay
                                            </a>
                                            @endif
                                            @if($order->canCancel())
                                            <form method="POST" action="{{ route('order.cancel', $order->id) }}">
                                                @csrf
                                                <button class="btn-agri text-danger"
                                                        style="padding: 6px 12px; font-size: 13px; background: rgba(239,68,68,0.1); border: none;"
                                                        onclick="return confirm('Cancel this order?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-shopping-bag fs-2 mb-3 opacity-50 d-block"></i>
                                        No orders match your current filters.
                                        <a href="{{ route('shop') }}" class="d-block mt-2 text-success text-decoration-none fw-bold">Start Shopping</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                    <div class="mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
