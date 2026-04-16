@extends('layouts.frontend')

@section('title', 'My Orders | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Sidebar Menu -->
            <div class="col-lg-3 mb-4">
                <div class="card-agri p-0 overflow-hidden" style="border: none;">
                    <div class="bg-white p-4 text-center border-bottom">
                        <div style="width: 80px; height: 80px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 32px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ auth('web')->user()->name ?? 'Customer' }}</h5>
                        <p class="text-muted small mb-0">{{ auth('web')->user()->email ?? '' }}</p>
                    </div>
                    <div class="list-group border-0" style="border-radius: 0;">
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('account.profile') }}">
                            <i class="fas fa-user-circle fs-5"></i> Profile Settings
                        </a>
                        <a class="list-group-item border-0 py-3 px-4 d-flex align-items-center gap-3 active" href="{{ route('orders') }}" style="background: var(--agri-primary-light); color: var(--agri-primary); border-left: 4px solid var(--agri-primary) !important;">
                            <i class="fas fa-shopping-bag fs-5"></i> My Orders
                        </a>
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('appointments') }}">
                            <i class="fas fa-calendar-check fs-5"></i> Appointments
                        </a>
                        <a class="list-group-item border-0 text-danger py-3 px-4 d-flex align-items-center gap-3 mt-3 border-top" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fs-5"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card-agri p-4" style="border: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 20px;">Order History</h3>
                        <a href="{{ route('shop') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 8px 16px; font-size: 14px;">Continue Shopping</a>
                    </div>

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
                                        @php
                                            $statusColors = [
                                                'pending'          => ['bg' => 'rgba(245,158,11,0.1)',  'color' => '#F59E0B'],
                                                'pending_payment'  => ['bg' => 'rgba(59,130,246,0.1)',  'color' => '#3B82F6'],
                                                'confirmed'        => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#10B981'],
                                                'processing'       => ['bg' => 'rgba(99,102,241,0.1)', 'color' => '#6366F1'],
                                                'shipped'          => ['bg' => 'rgba(14,165,233,0.1)', 'color' => '#0EA5E9'],
                                                'delivered'        => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#10B981'],
                                                'cancelled'        => ['bg' => 'rgba(239,68,68,0.1)',  'color' => '#EF4444'],
                                                'refunded'         => ['bg' => 'rgba(156,163,175,0.1)','color' => '#6B7280'],
                                                'payment_failed'   => ['bg' => 'rgba(239,68,68,0.1)',  'color' => '#EF4444'],
                                            ];
                                            $sc = $statusColors[$order->status] ?? ['bg' => 'rgba(156,163,175,0.1)', 'color' => '#6B7280'];
                                        @endphp
                                        <span class="badge rounded-pill fw-medium"
                                              style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; padding: 6px 12px; font-size: 12px;">
                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
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
                                            @if(in_array($order->status, ['pending', 'confirmed']))
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
                                        You haven't placed any orders yet.
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
