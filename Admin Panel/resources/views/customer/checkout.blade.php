@extends('layouts.frontend')

@section('title', 'Checkout | Plantix-AI')

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
                    <li class="breadcrumb-item"><a href="{{ route('cart') }}" class="text-success text-decoration-none">Cart</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Checkout -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 48px; height: 48px; background: white; color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: var(--agri-shadow-sm);">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 class="fw-bold mb-0 text-dark">Secure Checkout</h2>
            </div>

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
                $user     = auth('web')->user();
            @endphp

            <form method="POST" action="{{ route('checkout.place') }}">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card-agri p-4 border-0 mb-4">
                            <h4 class="fw-bold text-dark mb-4 fs-5"><i class="far fa-address-card text-success me-2"></i> Shipping Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-agri" placeholder="Enter first name"
                                        value="{{ old('first_name', $user->name ? explode(' ', $user->name)[0] : '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-agri" placeholder="Enter last name"
                                        value="{{ old('last_name', count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : '') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">Street Address <span class="text-danger">*</span></label>
                                    <input type="text" name="street" class="form-agri mb-2"
                                        placeholder="House number and street name" value="{{ old('street') }}" required>
                                    <input type="text" name="street2" class="form-agri"
                                        placeholder="Apartment, suite, unit, etc. (optional)" value="{{ old('street2') }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">Town / City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-agri" placeholder="e.g. Lahore"
                                        value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">State / Province <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-agri" placeholder="e.g. Punjab"
                                        value="{{ old('state') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">Country <span class="text-danger">*</span></label>
                                    <input type="text" name="country" class="form-agri" placeholder="Country"
                                        value="{{ old('country', 'Pakistan') }}" required readonly style="background: var(--agri-bg);">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark" style="font-size: 13px;">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-agri" placeholder="Valid mobile number"
                                        value="{{ old('phone', $user->phone ?? '') }}" required>
                                </div>
                                <div class="col-12 mt-4 text-muted small"><i class="fas fa-info-circle me-1"></i> Delivery address is saved for future orders automatically.</div>
                            </div>
                        </div>

                        <div class="card-agri p-4 border-0">
                            <h4 class="fw-bold text-dark mb-4 fs-5"><i class="far fa-sticky-note text-success me-2"></i> Additional Information</h4>
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">Order Notes (Optional)</label>
                                <textarea name="notes" class="form-agri" rows="3"
                                    placeholder="Notes about your order, e.g. special delivery instructions.">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card-agri p-4 border-0 position-sticky" style="top: 20px;">
                            <h4 class="fw-bold text-dark mb-4 fs-5">Order Summary</h4>
                            
                            @if($items->isEmpty())
                                <p class="text-muted text-center py-4 bg-light rounded-3">Your cart is empty. <a href="{{ route('shop') }}" class="text-success fw-bold text-decoration-none">Go shopping</a>.</p>
                            @else
                                <div class="bg-light p-3 rounded-3 mb-4 border">
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                        @foreach($items as $item)
                                        <li class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark">{{ $item->product->name ?? 'Product' }}</span>
                                                <span class="badge bg-white text-muted border">x{{ $item->quantity }}</span>
                                            </div>
                                            <span class="text-muted fw-medium small">PKR {{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Coupon --}}
                            @if(session('coupon_code'))
                                <input type="hidden" name="coupon_code" value="{{ session('coupon_code') }}">
                                <div class="mb-4">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fs-6 w-100 text-start"><i class="fas fa-tags me-2"></i> Coupon {{ session('coupon_code') }} applied successfully.</span>
                                </div>
                            @endif

                            <div class="d-flex flex-column gap-3 mb-4 px-2">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Subtotal</span>
                                    <span class="fw-medium text-dark">PKR {{ number_format($subtotal, 2) }}</span>
                                </div>
                                @if($couponDiscount > 0)
                                <div class="d-flex justify-content-between text-success small">
                                    <span>Discount</span>
                                    <span class="fw-bold">- PKR {{ number_format($couponDiscount, 2) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Shipping</span>
                                    <span class="fw-medium text-dark">PKR {{ number_format($shipping, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted pb-3 border-bottom small">
                                    <span>Tax (5%)</span>
                                    <span class="fw-medium text-dark">PKR {{ number_format($tax, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="fw-bold text-dark fs-5">Total to Pay</span>
                                    <span class="fw-bold text-success fs-4">PKR {{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <div class="payment-methods mt-4 mb-4">
                                <h5 class="fw-bold text-dark fs-6 mb-3">Payment Method</h5>
                                
                                <div class="card border mb-2 cursor-pointer position-relative overflow-hidden" style="border-radius: var(--agri-radius-sm);">
                                    <input class="form-check-input position-absolute" type="radio" name="payment_method" id="cashOnDelivery"
                                        value="cod" {{ old('payment_method','cod')==='cod'?'checked':'' }} style="top: 20px; left: 15px; z-index: 5;">
                                    <label class="form-check-label w-100 p-3 ps-5 m-0 cursor-pointer text-dark" for="cashOnDelivery" style="cursor: pointer;">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery (COD)
                                        </div>
                                        <p class="small text-muted mb-0 mt-1">Pay with cash upon delivery of your items.</p>
                                    </label>
                                </div>
                                
                                <div class="card border cursor-pointer position-relative overflow-hidden" style="border-radius: var(--agri-radius-sm);">
                                    <input class="form-check-input position-absolute" type="radio" name="payment_method" id="stripePayment"
                                        value="stripe" {{ old('payment_method')==='stripe'?'checked':'' }} style="top: 20px; left: 15px; z-index: 5;">
                                    <label class="form-check-label w-100 p-3 ps-5 m-0 cursor-pointer text-dark d-flex flex-column" for="stripePayment" style="cursor: pointer;">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="far fa-credit-card text-primary"></i> Online Payment
                                        </div>
                                        <p class="small text-muted mb-0 mt-1">Pay securely using credit/debit card.</p>
                                        <div class="d-flex gap-1 mt-2">
                                            <i class="fab fa-cc-visa fs-4 text-muted"></i>
                                            <i class="fab fa-cc-mastercard fs-4 text-muted"></i>
                                            <i class="fab fa-cc-stripe fs-4 text-muted"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn-agri btn-agri-primary w-100 {{ $items->isEmpty() ? 'disabled opacity-50' : '' }}" style="padding: 14px 24px; font-size: 16px;">
                                Confirm &amp; Place Order <i class="fas fa-check-circle ms-2"></i>
                            </button>
                            
                            <p class="text-center text-muted small mt-3 mb-0">
                                By placing your order, you agree to our <a href="#" class="text-success text-decoration-none">Terms of Service</a> &amp; <a href="#" class="text-success text-decoration-none">Privacy Policy</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- End Checkout -->
@endsection
