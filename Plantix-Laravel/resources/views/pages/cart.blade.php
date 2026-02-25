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
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
@endsection

@section('content')
<!-- End Header -->

    <!-- Start Breadcrumb 
    ============================================= -->
    <div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
      style="background-image: url({{ asset('assets/img/banner7.jpg') }})"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Shopping Cart</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li><a href="{{ route('shop') }}">Shop</a></li>
                <li class="active">Cart</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Cart 
    ============================================= -->
    <div class="cart-area default-padding">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="cart-table-area">
              <h3>Your Cart</h3>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Price</th>
                      <th>Quantity</th>
                      <th>Subtotal</th>
                      <th>Remove</th>
                    </tr>
                  </thead>
                  <tbody id="cart-items-body">
                    <!-- Cart items will be inserted here by JavaScript -->
                  </tbody>
                </table>
              </div>
              <div class="cart-actions">
                <a href="{{ route('shop') }}" class="btn btn-secondary">
                  <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
                <button id="clear-cart-btn" class="btn btn-outline-danger">
                  <i class="fas fa-trash"></i> Clear Cart
                </button>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="cart-summary">
              <h4>Cart Summary</h4>
              <ul class="summary-list">
                <li>
                  <span>Subtotal:</span>
                  <span id="cart-subtotal">PKR 0</span>
                </li>
                <li id="cart-discount-row" class="hidden">
                  <span>Discount:</span>
                  <span id="cart-discount">- PKR 0</span>
                </li>
                <li>
                  <span>Shipping:</span>
                  <span id="cart-shipping">PKR 500</span>
                </li>
                <li>
                  <span>Tax (5%):</span>
                  <span id="cart-tax">PKR 0</span>
                </li>
                <li class="total-row">
                  <strong>Total:</strong>
                  <strong id="cart-total">PKR 0</strong>
                </li>
              </ul>
              <div class="promo-code">
                <input
                  type="text"
                  id="promo-input"
                  class="form-control"
                  placeholder="Promo Code"
                />
                <button
                  id="apply-promo-btn"
                  class="btn btn-sm btn-outline-secondary"
                >
                  Apply
                </button>
                <button
                  id="remove-promo-btn"
                  class="btn btn-sm btn-outline-danger hidden"
                >
                  Remove
                </button>
              </div>
              <small id="cart-promo-help" class="text-muted"></small>
              <a href="{{ route('checkout') }}" class="btn btn-theme btn-md w-100">
                <i class="fas fa-lock"></i> Proceed to Checkout
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Cart -->
@endsection

