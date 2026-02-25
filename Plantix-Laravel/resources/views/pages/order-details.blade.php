@extends('layouts.app')

@section('title', 'Plantix-AI')

@section('header')
@include('partials.header-notopbar')
@endsection

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/auth-pages.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<!-- Breadcrumb -->
  <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Order Details</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('orders') }}">Orders</a></li>
              <li class="active">Order</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Details -->
  <div id="order-details-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="panel-card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h3 class="mb-0">Order <span id="od-id">#</span></h3>
              <div>
                <button id="od-cancel" class="btn btn-outline-danger btn-sm me-1 hidden">Cancel</button>
                <button id="od-return" class="btn btn-outline-secondary btn-sm me-2 hidden">Request Return</button>
                <a href="{{ route('orders') }}" class="btn btn-border btn-sm">Back to Orders</a>
                <a href="{{ route('shop') }}" class="btn btn-theme btn-sm">Continue Shopping</a>
              </div>
            </div>
            <hr>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>Summary</h5>
                <ul class="list-unstyled mb-0" id="od-summary">
                  <li><strong>Date:</strong> <span id="od-date">-</span></li>
                  <li><strong>Status:</strong> <span id="od-status">-</span></li>
                  <li><strong>Payment:</strong> <span id="od-payment">-</span></li>
                  <li><strong>Subtotal:</strong> <span id="od-subtotal">-</span></li>
                  <li id="od-discount-row" class="hidden"><strong>Discount:</strong> <span id="od-discount">-</span>
                  </li>
                  <li id="od-promo-row" class="hidden"><strong>Promo:</strong> <span id="od-promo">-</span></li>
                  <li><strong>Shipping:</strong> <span id="od-shipping">-</span></li>
                  <li><strong>Tax:</strong> <span id="od-tax">-</span></li>
                  <li><strong>Total:</strong> <span id="od-total">-</span></li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Ship To</h5>
                <address id="od-address" class="mb-0"></address>
              </div>
              <div class="col-12">
                <h5>Items</h5>
                <div class="table-responsive">
                  <table class="table table-striped align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th width="120">Qty</th>
                        <th width="160">Price</th>
                        <th width="180">Line Total</th>
                      </tr>
                    </thead>
                    <tbody id="od-items">
                      <tr>
                        <td colspan="4">Loading...</td>
                      </tr>
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

  <!-- Footer -->
@endsection

