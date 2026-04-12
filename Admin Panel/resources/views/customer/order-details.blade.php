@extends('layouts.frontend')

@section('title', 'Order Details | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4 justify-content-center">
            <div class="col-lg-10">
                
                <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('orders') }}" class="btn-agri btn-agri-outline d-flex align-items-center p-2 rounded-circle border-0" style="width: 40px; height: 40px; justify-content: center; background: white; box-shadow: var(--agri-shadow-sm);">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="fw-bold mb-0 text-dark d-flex align-items-center gap-3">
                            Order #{{ $order->id }}
                            <span class="badge rounded-pill fw-medium fs-6" style="background: {{ $order->status === 'delivered' ? 'rgba(16, 185, 129, 0.1); color: #10B981;' : ($order->status === 'cancelled' ? 'rgba(239, 68, 68, 0.1); color: #EF4444;' : 'rgba(245, 158, 11, 0.1); color: #F59E0B;') }} padding: 6px 12px; font-size: 14px; vertical-align: middle;">
                                {{ ucwords(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </h2>
                    </div>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        @if($order->status === 'pending_payment')
                        <a href="{{ route('checkout.pay', $order->id) }}"
                           class="btn-agri btn-agri-primary text-decoration-none d-flex align-items-center gap-2"
                           style="padding: 8px 20px;">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                        @endif
                        @if(in_array($order->status, ['pending','confirmed']))
                        <form method="POST" action="{{ route('order.cancel', $order->id) }}">
                            @csrf
                            <button class="btn-agri text-danger" style="padding: 8px 16px; background: rgba(239, 68, 68, 0.1); border: none;" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</button>
                        </form>
                        @endif
                        @if($canReturn && !$order->returnRequest)
                        <button class="btn-agri btn-agri-outline text-dark border-dark" style="padding: 8px 16px;" data-bs-toggle="modal" data-bs-target="#returnModal">Return Items</button>
                        @endif
                        <a href="{{ route('shop') }}" class="btn-agri btn-agri-primary" style="padding: 8px 16px;">Buy Again</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Order Info Cards -->
                    <div class="col-md-6">
                        <div class="card-agri h-100 p-4 border-0">
                            <h4 class="fw-bold mb-4 text-dark fs-5"><i class="fas fa-receipt text-success me-2"></i> Order Summary</h4>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Order Date</span>
                                    <span class="fw-medium text-dark">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Payment Method</span>
                                    <span class="fw-medium text-dark">{{ strtoupper($order->payment_method ?? 'N/A') }}</span>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark h5 mb-0">Total Amount</span>
                                    <span class="fw-bold text-success h4 mb-0">PKR {{ number_format($order->total ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-agri h-100 p-4 border-0">
                            <h4 class="fw-bold mb-4 text-dark fs-5"><i class="fas fa-map-marker-alt text-success me-2"></i> Shipping Details</h4>
                            <p class="mb-0 text-muted" style="line-height: 1.6;">
                                <strong class="text-dark d-block mb-1">{{ auth('web')->user()->name }}</strong>
                                {{ $order->delivery_address ?? 'No shipping address provided.' }}<br>
                                {{ auth('web')->user()->email }}<br>
                                {{ auth('web')->user()->phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="col-12 mt-4">
                        <div class="card-agri p-4 border-0">
                            <h4 class="fw-bold mb-4 text-dark fs-5"><i class="fas fa-box-open text-success me-2"></i> Order Items</h4>
                            <div class="table-responsive">
                                <table class="table align-middle" style="border-collapse: separate; border-spacing: 0;">
                                    <thead style="background: var(--agri-bg);">
                                        <tr>
                                            <th class="border-0 py-3 rounded-start" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Product Name</th>
                                            <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Unit Price</th>
                                            <th class="border-0 py-3 text-center" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Quantity</th>
                                            <th class="border-0 py-3 rounded-end text-end" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order->items ?? [] as $item)
                                        <tr style="border-bottom: 1px solid var(--agri-border);">
                                            <td class="py-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div style="width: 48px; height: 48px; background: var(--agri-primary-light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-seedling text-success fs-5"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark">{{ $item->product->name ?? 'Product' }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 text-muted">PKR {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                            <td class="py-4 text-center fw-medium">{{ $item->quantity }}</td>
                                            <td class="py-4 text-end fw-bold text-dark">PKR {{ number_format(($item->unit_price ?? 0) * $item->quantity, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open fs-2 mb-3 opacity-50 d-block"></i>
                                                No items found in this order.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Return Modal --}}
@if($canReturn && !isset($order->returnRequest))
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--agri-radius-md); border: none;">
            <form method="POST" action="{{ route('order.return', $order->id) }}">
                @csrf
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark">Request Item Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4 fs-sm">Please tell us why you are returning your order. We will process your request within 24-48 hours.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark text-sm">Reason for return <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-agri" rows="4" placeholder="Describe the issue with your items" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn-agri text-dark bg-light border-0" data-bs-dismiss="modal" style="padding: 10px 24px;">Cancel</button>
                    <button type="submit" class="btn-agri btn-agri-primary" style="padding: 10px 24px;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
