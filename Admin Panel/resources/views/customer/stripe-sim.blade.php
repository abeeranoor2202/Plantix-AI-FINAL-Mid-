@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Secure Payment</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('checkout') }}">Checkout</a></li>
              <li class="active">Payment</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="stripe-sim-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="panel-card p-4">
            <h3 class="mb-3">Card details (Demo)</h3>
            <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
              <i class="fas fa-lock me-2" aria-hidden="true"></i>
              <span>
                Test mode — payments are simulated and processed via <span class="stripe-wordmark"
                  aria-label="Stripe">Stripe</span>.
              </span>
            </div>
            <div id="summary" class="mb-3 small text-muted"></div>
            <form id="pay-form">
              <div class="mb-3">
                <label>Name on Card</label>
                <input id="cardName" class="form-control" placeholder="Full name" required data-label="Cardholder name">
              </div>
              <div class="mb-3">
                <label>Card Number</label>
                <input id="cardNumber" class="form-control" placeholder="4242 4242 4242 4242" required data-label="Card number">
                <div class="form-text">Use 4242 4242 4242 4242 for success, 4000 0000 0000 0002 for failure (demo).
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label>Expiry</label>
                  <input id="cardExp" class="form-control" placeholder="MM/YY" required data-label="Expiry date">
                </div>
                <div class="col-md-6 mb-3">
                  <label>CVC</label>
                  <input id="cardCvc" class="form-control" placeholder="CVC" required data-label="CVC / security code">
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-theme">Pay now</button>
                <a href="{{ route('checkout') }}" id="cancelPay" class="btn btn-border">Cancel</a>
              </div>
              <div class="text-center mt-3 small text-muted">
                <span class="me-1">Powered by</span>
                <span class="stripe-wordmark" aria-label="Stripe">Stripe</span>
                <span class="ms-2" aria-hidden="true">
                  <i class="fab fa-cc-visa me-1"></i>
                  <i class="fab fa-cc-mastercard me-1"></i>
                  <i class="fab fa-cc-amex"></i>
                </span>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

