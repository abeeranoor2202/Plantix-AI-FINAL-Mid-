@extends('layouts.frontend')

@section('title', 'Payment Successful | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row justify-content-center pt-4">
            <div class="col-lg-7">
                <div class="card-agri p-0 border-0 overflow-hidden text-center">

                    <!-- Success header -->
                    <div class="p-5" style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);">
                        <div style="width: 80px; height: 80px; background: #10B981; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-check text-white" style="font-size: 36px;"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Payment Successful!</h2>
                        <p class="text-muted mb-0">Your payment was processed and your order is confirmed.</p>
                    </div>

                    <!-- Order details -->
                    @if($order)
                    <div class="p-4 p-md-5 bg-white">
                        <div class="row g-3 text-start mb-4">
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Order Number</div>
                                    <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Payment Status</div>
                                    <div class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i> Paid</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Amount Charged</div>
                                    <div class="fw-bold text-dark">PKR {{ number_format($order->total ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Payment Method</div>
                                    <div class="fw-bold text-dark">{{ strtoupper($order->payment_method ?? 'Card') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert border-0 text-start mb-4"
                             style="background: #EFF6FF; border-radius: var(--agri-radius-sm); color: #1E40AF;">
                            <i class="fas fa-envelope me-2"></i>
                            A confirmation email has been sent to <strong>{{ auth('web')->user()->email }}</strong>.
                        </div>

                        <div class="d-flex gap-3 flex-wrap justify-content-center">
                            <a href="{{ route('order.details', $order->id) }}"
                               class="btn-agri btn-agri-primary text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-receipt me-2"></i> View Order
                            </a>
                            <a href="{{ route('shop') }}"
                               class="btn-agri btn-agri-outline text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="p-5 bg-white">
                        <p class="text-muted mb-4">Your payment was received. Check your email for order details.</p>
                        <div class="d-flex gap-3 flex-wrap justify-content-center">
                            <a href="{{ route('orders') }}"
                               class="btn-agri btn-agri-primary text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-list me-2"></i> My Orders
                            </a>
                            <a href="{{ route('shop') }}"
                               class="btn-agri btn-agri-outline text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
