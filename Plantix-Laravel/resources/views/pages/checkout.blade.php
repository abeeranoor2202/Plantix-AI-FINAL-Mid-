@extends('layouts.app')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/jquery.appear.js') }}"></script>
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/progress-bar.min.js') }}"></script>
    <script src="{{ asset('assets/js/circle-progress.js') }}"></script>
    <script src="{{ asset('assets/js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/count-to.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.scrolla.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollOnReveal.js') }}"></script>
    <script src="{{ asset('assets/js/YTPlayer.min.js') }}"></script>
    <script src="{{ asset('assets/js/gsap.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollTrigger.min.js') }}"></script>
    <script src="{{ asset('assets/js/SplitText.min.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/checkout-flow.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<!-- End Header -->

    <!-- Start Breadcrumb 
    ============================================= -->
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

    <!-- Start Checkout 
    ============================================= -->
    <div class="checkout-area default-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="checkout-form">
                        <h3>Billing Details</h3>
                        <form id="checkout-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name *</label>
                                        <input type="text" class="form-control" id="firstName" placeholder="First name"
                                            required data-label="First name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name *</label>
                                        <input type="text" class="form-control" id="lastName" placeholder="Last name"
                                            required data-label="Last name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Company Name (optional)</label>
                                <input type="text" class="form-control" id="companyName"
                                    placeholder="Company (optional)">
                            </div>
                            <div class="form-group">
                                <label>Country / Region *</label>
                                    <select class="form-control" id="country" title="Country" required data-label="Country">
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="India">India</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Street Address *</label>
                                <input type="text" class="form-control" id="address1"
                                    placeholder="House number and street name" required data-label="Street address">
                                <input type="text" class="form-control mt-2" id="address2"
                                    placeholder="Apartment, suite, unit, etc. (optional)">
                            </div>
                            <div class="form-group">
                                <label>Town / City *</label>
                                <input type="text" class="form-control" id="city" placeholder="City" required data-label="City">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State / Province *</label>
                                        <input type="text" class="form-control" id="state" placeholder="State/Province"
                                            required data-label="State or province">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Postal Code *</label>
                                        <input type="text" class="form-control" id="postalCode"
                                            placeholder="Postal code" required data-label="Postal code">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone *</label>
                                        <input type="tel" class="form-control" id="phone" placeholder="Phone number"
                                            required data-label="Phone number (include country code)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" class="form-control" id="email" placeholder="Email address"
                                            required data-label="Email address">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Order Notes (optional)</label>
                                <textarea class="form-control" id="orderNotes" rows="4"
                                    placeholder="Notes about your order, e.g. special notes for delivery"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h4>Your Order</h4>
                        <div class="order-items" id="order-items-list">
                            <!-- Order items will be inserted here -->
                        </div>
                        <div id="promo-section" class="mb-3">
                            <label for="promoCodeInput" class="form-label">Have a promo code?</label>
                            <div class="input-group">
                                <input type="text" id="promoCodeInput" class="form-control"
                                    placeholder="Enter promo code">
                                <button class="btn btn-outline-secondary" type="button"
                                    id="applyPromoBtn">Apply</button>
                                <button class="btn btn-outline-danger hidden" type="button"
                                    id="removePromoBtn">Remove</button>
                            </div>
                            <small id="promoHelp" class="text-muted"></small>
                        </div>
                        <ul class="summary-list">
                            <li>
                                <span>Subtotal:</span>
                                <span id="checkout-subtotal">PKR 0</span>
                            </li>
                            <li id="discount-row" class="hidden">
                                <span>Discount:</span>
                                <span id="checkout-discount">- PKR 0</span>
                            </li>
                            <li>
                                <span>Shipping:</span>
                                <span id="checkout-shipping">PKR 500</span>
                            </li>
                            <li>
                                <span>Tax (5%):</span>
                                <span id="checkout-tax">PKR 0</span>
                            </li>
                            <li class="total-row">
                                <strong>Total:</strong>
                                <strong id="checkout-total">PKR 0</strong>
                            </li>
                        </ul>

                        <div class="payment-methods">
                            <h5>Payment Method</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="cashOnDelivery"
                                    value="cod" checked>
                                <label class="form-check-label" for="cashOnDelivery">
                                    <strong>Cash on Delivery</strong>
                                    <p class="small text-muted">Pay with cash upon delivery.</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="bankTransfer"
                                    value="bank">
                                <label class="form-check-label" for="bankTransfer">
                                    <strong>Direct Bank Transfer</strong>
                                    <p class="small text-muted">Make payment directly to our bank account.</p>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="onlinePayment"
                                    value="online">
                                <label class="form-check-label" for="onlinePayment">
                                    <strong>Online Payment</strong>
                                    <p class="small text-muted">Pay securely using credit/debit card.</p>
                                </label>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="termsCheckbox" required data-label="Agree to terms and conditions">
                            <label class="form-check-label" for="termsCheckbox">
                                I have read and agree to the website <a href="#">terms and conditions</a> *
                            </label>
                        </div>

                        <button type="button" class="btn btn-theme btn-md w-100 mt-3" id="place-order-btn">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Checkout -->
@endsection

