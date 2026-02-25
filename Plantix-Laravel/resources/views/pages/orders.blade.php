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
          <h1>My Orders</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Orders</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders -->
  <div id="orders-page" class="default-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="list-group">
            <a class="list-group-item list-group-item-action" href="{{ route('account.profile') }}">Profile</a>
            <a class="list-group-item list-group-item-action active" href="{{ route('orders') }}">Orders</a>
            <a class="list-group-item list-group-item-action" href="{{ route('appointments') }}">Appointments</a>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="panel-card p-4">
            <h3 class="mb-3">Order History</h3>
            <div id="ordersListTable" class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="ordersTableBody">
                  <tr>
                    <td colspan="6">Loading...</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-3 d-flex gap-2">
              <a href="{{ route('shop') }}" class="btn btn-border">Continue Shopping</a>
              <a href="{{ route('account.profile') }}" class="btn btn-theme">Go to Profile</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

