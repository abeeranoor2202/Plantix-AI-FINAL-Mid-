@extends('layouts.frontend')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
    <!-- Start Breadcrumb -->
    <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Checkout</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li><a href="{{ route('shop') }}">Shop</a></li>
                            <li><a href="{{ route('cart') }}">Cart</a></li>
                            <li class="active">Checkout</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Checkout -->
    <div class="checkout-area default-padding">
        <div class="container">

            @if($errors->any())
              <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
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
            <div class="row">
                <div class="col-lg-7">
                    <div class="checkout-form">
                        <h3>Billing Details</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" class="form-control" placeholder="First name"
                                        value="{{ old('first_name', $user->name ? explode(' ', $user->name)[0] : '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="last_name" class="form-control" placeholder="Last name"
                                        value="{{ old('last_name', count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Street Address *</label>
                            <input type="text" name="street" class="form-control"
                                placeholder="House number and street name" value="{{ old('street') }}" required>
                            <input type="text" name="street2" class="form-control mt-2"
                                placeholder="Apartment, suite, unit, etc. (optional)" value="{{ old('street2') }}">
                        </div>
                        <div class="form-group">
                            <label>Town / City *</label>
                            <input type="text" name="city" class="form-control" placeholder="City"
                                value="{{ old('city') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>State / Province *</label>
                                    <input type="text" name="state" class="form-control" placeholder="State/Province"
                                        value="{{ old('state') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Country *</label>
                                    <input type="text" name="country" class="form-control" placeholder="Country"
                                        value="{{ old('country', 'Pakistan') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Phone number"
                                value="{{ old('phone', $user->phone ?? '') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Order Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="4"
                                placeholder="Notes about your order, e.g. special notes for delivery">{{ old('notes') }}</textarea>
                        </div>
                        {{-- Hidden combined delivery_address sent to backend --}}
                        {{-- Built server-side on submit via hidden field --}}
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h4>Your Order</h4>
                        @if($items->isEmpty())
                          <p class="text-muted text-center py-3">Your cart is empty. <a href="{{ route('shop') }}">Go shopping</a>.</p>
                        @else
                        <table class="table table-sm mb-3">
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Product' }} <span class="text-muted">× {{ $item->quantity }}</span></td>
                                    <td class="text-end">PKR {{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                        {{-- Coupon --}}
                        @if(session('coupon_code'))
                          <input type="hidden" name="coupon_code" value="{{ session('coupon_code') }}">
                          <div class="mb-3">
                            <span class="badge bg-success">Coupon: {{ session('coupon_code') }}</span>
                          </div>
                        @endif

                        <ul class="summary-list">
                            <li><span>Subtotal:</span><span>PKR {{ number_format($subtotal, 2) }}</span></li>
                            @if($couponDiscount > 0)
                            <li><span>Discount:</span><span>- PKR {{ number_format($couponDiscount, 2) }}</span></li>
                            @endif
                            <li><span>Shipping:</span><span>PKR {{ number_format($shipping, 2) }}</span></li>
                            <li><span>Tax (5%):</span><span>PKR {{ number_format($tax, 2) }}</span></li>
                            <li class="total-row"><strong>Total:</strong><strong>PKR {{ number_format($total, 2) }}</strong></li>
                        </ul>

                        <div class="payment-methods">
                            <h5>Payment Method</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cashOnDelivery"
                                    value="cod" {{ old('payment_method','cod')==='cod'?'checked':'' }}>
                                <label class="form-check-label" for="cashOnDelivery">
                                    <strong>Cash on Delivery</strong>
                                    <p class="small text-muted mb-0">Pay with cash upon delivery.</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="stripePayment"
                                    value="stripe" {{ old('payment_method')==='stripe'?'checked':'' }}>
                                <label class="form-check-label" for="stripePayment">
                                    <strong>Online Payment (Card)</strong>
                                    <p class="small text-muted mb-0">Pay securely using credit/debit card.</p>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-theme btn-md w-100 mt-3">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- End Checkout -->
@endsection

