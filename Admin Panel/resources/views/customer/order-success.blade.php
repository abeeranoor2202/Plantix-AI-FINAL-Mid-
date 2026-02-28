@extends('layouts.frontend')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<!-- End Header -->

    <!-- Start Order Success -->
    <div class="order-success-area default-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="success-message text-center">
                        <div class="success-icon">
                            <i class="fas fa-check-circle text-success" style="font-size:4rem"></i>
                        </div>
                        <h2 class="mt-3">Thank You For Your Order!</h2>
                        <p class="lead">Your order has been successfully placed and is being processed.</p>

                        <div class="order-details text-start">
                            <div class="order-info-box">
                                <h4>Order Information</h4>
                                <table class="table">
                                    <tr><td><strong>Order Number:</strong></td><td>#{{ $order->id }}</td></tr>
                                    <tr><td><strong>Order Date:</strong></td><td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>
                                    <tr><td><strong>Payment Method:</strong></td><td>{{ strtoupper($order->payment_method ?? 'N/A') }}</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>{{ auth('web')->user()->email }}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-warning">{{ ucfirst($order->status) }}</span></td></tr>
                                </table>
                            </div>

                            <div class="delivery-info">
                                <h5><i class="fas fa-truck"></i> Estimated Delivery</h5>
                                <p><strong>3 – 5 business days</strong></p>
                                <p class="text-muted small">We'll send you a confirmation email with tracking details.</p>
                            </div>

                            <div class="order-items-summary">
                                <h5>Order Summary</h5>
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($order->admin->items ?? [] as $item)
                                        <tr>
                                            <td>{{ $item->product->name ?? 'Product' }} × {{ $item->quantity }}</td>
                                            <td class="text-end">PKR {{ number_format(($item->unit_price ?? 0) * $item->quantity, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="totals-display">
                                    <div class="total-row grand-total d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong>PKR {{ number_format($order->total ?? 0, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="shipping-address">
                                <h5><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                                <p>{{ $order->delivery_address ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="action-buttons d-flex gap-2 justify-content-center flex-wrap mt-3">
                            <a href="{{ route('shop') }}" class="btn btn-theme btn-md">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                            <a href="{{ route('order.details', $order->id) }}" class="btn btn-border btn-md">
                                <i class="fas fa-eye"></i> View Order
                            </a>
                            <a href="{{ route('order.invoice', $order->id) }}" class="btn btn-outline-primary btn-md">
                                <i class="fas fa-download"></i> Download Invoice
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-md">
                                <i class="fas fa-print"></i> Print Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Order Success -->
@endsection

