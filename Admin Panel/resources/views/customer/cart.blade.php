@extends('layouts.frontend')

@section('title', 'Shopping Cart | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border);">
        <div class="container-agri">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-success text-decoration-none">Shop</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Shopping Cart</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Cart -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 48px; height: 48px; background: white; color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: var(--agri-shadow-sm);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="fw-bold mb-0 text-dark">Your Cart</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @php
                $items    = $cart->items ?? collect();
                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                $couponDiscount = session('coupon_discount', 0);
                $shipping = 500;
                $tax      = round(($subtotal - $couponDiscount) * 0.05);
                $total    = max(0, $subtotal - $couponDiscount) + $shipping + $tax;
            @endphp

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-agri p-4 border-0">
                        @if($items->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-basket text-muted mb-3" style="font-size: 48px; opacity: 0.5;"></i>
                                <h5 class="fw-bold text-dark">Your cart is empty</h5>
                                <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                                <a href="{{ route('shop') }}" class="btn-agri btn-agri-primary">Browse Shop</a>
                            </div>
                        @else
                            @if(!empty($globalCoupons) && $globalCoupons->isNotEmpty())
                                <div class="bg-white border rounded-3 p-3 mb-4">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="text-dark fw-bold"><i class="fas fa-ticket-alt text-success me-2"></i>Available coupons</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($globalCoupons as $coupon)
                                                <span class="badge bg-success-subtle text-success border border-success">{{ $coupon->code }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table align-middle" style="border-collapse: separate; border-spacing: 0;">
                                    <thead style="background: var(--agri-bg);">
                                        <tr>
                                            <th class="border-0 py-3 px-4 rounded-start" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Product</th>
                                            <th class="border-0 py-3 text-center" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Quantity</th>
                                            <th class="border-0 py-3 text-end" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Subtotal</th>
                                            <th class="border-0 py-3 rounded-end text-center" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase; width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                        @php $product = $item->product; @endphp
                                        <tr style="border-bottom: 1px solid var(--agri-border);">
                                            <td class="py-4 px-4 border-bottom-0">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($product->primaryImage)
                                                        <img src="{{ Storage::url($product->primaryImage->path) }}" alt="{{ $product->name }}" style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid var(--agri-border);">
                                                    @else
                                                        <div style="width: 64px; height: 64px; background: var(--agri-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-seedling text-muted fs-4"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><a href="{{ route('shop.single', $product->id) }}" class="text-dark text-decoration-none">{{ $product->name }}</a></h6>
                                                        <span class="text-muted small">PKR {{ number_format($item->unit_price, 2) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 border-bottom-0 text-center">
                                                <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-flex align-items-center justify-content-center gap-2">
                                                    @csrf @method('PATCH')
                                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="form-agri text-center p-1" style="width: 60px; height: 36px; -moz-appearance: textfield;">
                                                    <button class="btn btn-sm text-secondary bg-light border" style="height: 36px; padding: 0 10px;" type="submit" title="Update Quantity"><i class="fas fa-sync-alt fw-normal"></i></button>
                                                </form>
                                            </td>
                                            <td class="py-4 border-bottom-0 text-end fw-bold text-dark">
                                                PKR {{ number_format($item->unit_price * $item->quantity, 2) }}
                                            </td>
                                            <td class="py-4 border-bottom-0 text-center">
                                                <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm text-danger bg-light border-0 rounded-circle" style="width: 32px; height: 32px;" type="submit" title="Remove Item"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <a href="{{ route('shop') }}" class="btn-agri btn-agri-outline text-dark"><i class="fas fa-arrow-left me-2"></i> Continue Shopping</a>
                                <form method="POST" action="{{ route('cart.clear') }}">
                                    @csrf @method('DELETE')
                                    <button class="btn-agri text-danger bg-transparent" style="border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 16px;" type="submit"><i class="fas fa-trash me-2"></i> Clear Cart</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-agri p-4 border-0 position-sticky" style="top: 20px;">
                        <h4 class="fw-bold text-dark mb-4 fs-5">Order Summary</h4>
                        
                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex justify-content-between text-muted">
                                <span>Subtotal</span>
                                <span class="fw-medium text-dark">PKR {{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            @if($couponDiscount > 0)
                                <div class="d-flex justify-content-between text-success">
                                    <span>Discount ({{ session('coupon_code') }})</span>
                                    <span class="fw-bold">- PKR {{ number_format($couponDiscount, 2) }}</span>
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between text-muted">
                                <span>Shipping Estimate</span>
                                <span class="fw-medium text-dark">PKR {{ number_format($shipping, 2) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between text-muted pb-3 border-bottom">
                                <span>Tax (5%)</span>
                                <span class="fw-medium text-dark">PKR {{ number_format($tax, 2) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="fw-bold text-dark fs-5">Total</span>
                                <span class="fw-bold text-success fs-4">PKR {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- Coupon form --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            @if(session('coupon_code'))
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2 text-success fw-medium">
                                        <i class="fas fa-tags"></i> <span class="text-uppercase">{{ session('coupon_code') }}</span> applied
                                    </div>
                                    <form method="POST" action="{{ route('cart.coupon.remove') }}" class="m-0">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm text-danger p-0 border-0" title="Remove Coupon" style="background: none;"><i class="fas fa-times-circle fs-5"></i></button>
                                    </form>
                                </div>
                            @else
                                @if(!empty($globalCoupons) && $globalCoupons->isNotEmpty())
                                    <div class="mb-3 small text-muted">
                                        <div class="fw-bold text-dark mb-2"><i class="fas fa-ticket-alt text-success me-1"></i>Suggested coupons</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($globalCoupons as $coupon)
                                                <span class="badge bg-success-subtle text-success border border-success">{{ $coupon->code }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('cart.coupon.apply') }}" class="m-0 d-flex gap-2">
                                    @csrf
                                    <div class="position-relative flex-grow-1">
                                        <i class="fas fa-ticket-alt position-absolute text-muted" style="top: 50%; left: 12px; transform: translateY(-50%);"></i>
                                        <input type="text" name="code" class="form-agri m-0 ps-5" placeholder="Promo Code" style="height: 42px;">
                                    </div>
                                    <button class="btn-agri text-dark bg-white border" style="height: 42px; padding: 0 16px;" type="submit">Apply</button>
                                </form>
                                @error('coupon')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror
                            @endif
                        </div>

                        <a href="{{ route('checkout') }}" class="btn-agri btn-agri-primary w-100 {{ $items->isEmpty() ? 'disabled opacity-50' : '' }}" style="padding: 14px 24px; font-size: 16px;">
                            Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        
                        <div class="text-center mt-3 text-muted small">
                            <i class="fas fa-lock me-1"></i> Secure End-to-End Encryption
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Cart -->
@endsection
