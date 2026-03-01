@extends('layouts.frontend')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row justify-content-center pt-4">
            <div class="col-lg-8">
                <div class="card-agri p-0 border-0 overflow-hidden">

                    <!-- Success header -->
                    <div class="p-5 text-center" style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);">
                        <div style="width: 80px; height: 80px; background: #10B981; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-check text-white" style="font-size: 36px;"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Thank You For Your Order!</h2>
                        <p class="text-muted mb-0">Your order has been placed and is being processed.</p>
                    </div>

                    <div class="p-4 p-md-5 bg-white">

                        <!-- Info grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Order #</div>
                                    <div class="fw-bold text-dark">#{{ $order->id }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Date</div>
                                    <div class="fw-bold text-dark">{{ $order->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Payment</div>
                                    <div class="fw-bold text-dark">{{ strtoupper($order->payment_method ?? 'N/A') }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 rounded-3 text-center" style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <div class="small text-muted mb-1">Total</div>
                                    <div class="fw-bold" style="color: var(--agri-primary);">PKR {{ number_format($order->total ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-3 fs-6"><i class="fas fa-box-open text-success me-2"></i>Order Items</h5>
                            <div class="d-flex flex-column gap-2">
                                @foreach($order->items ?? [] as $item)
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-3"
                                     style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                    <span class="fw-medium text-dark">{{ $item->product->name ?? 'Product' }}
                                        <span class="text-muted ms-1">× {{ $item->quantity }}</span>
                                    </span>
                                    <span class="fw-bold text-muted">PKR {{ number_format(($item->unit_price ?? 0) * $item->quantity, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded-3"
                                 style="background: var(--agri-primary-light); border: 1px solid var(--agri-border);">
                                <span class="fw-bold text-dark">Total</span>
                                <span class="fw-bold fs-5" style="color: var(--agri-primary);">PKR {{ number_format($order->total ?? 0, 2) }}</span>
                            </div>
                        </div>

                        <!-- Delivery note -->
                        <div class="alert border-0 mb-4"
                             style="background: #EFF6FF; border-radius: var(--agri-radius-sm); color: #1E40AF;">
                            <i class="fas fa-truck me-2"></i>
                            <strong>Estimated delivery: 3–5 business days.</strong>
                            We'll send tracking details to {{ auth('web')->user()->email }}.
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-3 flex-wrap justify-content-center">
                            <a href="{{ route('order.details', $order->id) }}"
                               class="btn-agri btn-agri-primary text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-receipt me-2"></i> View Order
                            </a>
                            <a href="{{ route('order.invoice', $order->id) }}"
                               class="btn-agri btn-agri-outline text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-download me-2"></i> Invoice
                            </a>
                            <a href="{{ route('shop') }}"
                               class="btn-agri btn-agri-outline text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

