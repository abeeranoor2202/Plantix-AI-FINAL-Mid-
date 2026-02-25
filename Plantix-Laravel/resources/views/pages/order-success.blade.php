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
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/order-success.js') }}"></script>
@endsection

@section('content')
<!-- End Header -->

    <!-- Start Order Success 
    ============================================= -->
    <div class="order-success-area default-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="success-message text-center">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Thank You For Your Order!</h2>
                        <p class="lead">Your order has been successfully placed and is being processed.</p>

                        <div class="order-details">
                            <div class="order-info-box">
                                <h4>Order Information</h4>
                                <table class="table">
                                    <tr>
                                        <td><strong>Order Number:</strong></td>
                                        <td id="order-number">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Order Date:</strong></td>
                                        <td id="order-date">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Method:</strong></td>
                                        <td id="payment-method">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td id="customer-email">-</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="delivery-info">
                                <h5><i class="fas fa-truck"></i> Estimated Delivery</h5>
                                <p><strong id="delivery-date">-</strong></p>
                                <p class="text-muted small">We'll send you a confirmation email with tracking details.
                                </p>
                            </div>

                            <div class="order-items-summary">
                                <h5>Order Summary</h5>
                                <div id="order-items-display"></div>
                                <div class="totals-display">
                                    <div class="total-row">
                                        <span>Subtotal:</span>
                                        <span id="display-subtotal">PKR 0</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Shipping:</span>
                                        <span id="display-shipping">PKR 0</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Tax:</span>
                                        <span id="display-tax">PKR 0</span>
                                    </div>
                                    <div class="total-row grand-total">
                                        <strong>Total:</strong>
                                        <strong id="display-total">PKR 0</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="shipping-address">
                                <h5><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                                <p id="shipping-address-display">-</p>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('shop') }}" class="btn btn-theme btn-md">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                            <button id="download-invoice-btn" class="btn btn-outline-primary btn-md">
                                <i class="fas fa-download"></i> Download Invoice
                            </button>
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

