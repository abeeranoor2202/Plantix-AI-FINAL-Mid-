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
          <h1>My Account</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Profile</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile -->
  <div id="account-profile-page" class="default-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="list-group">
            <a class="list-group-item list-group-item-action active" href="{{ route('account.profile') }}">Profile</a>
            <a class="list-group-item list-group-item-action" href="{{ route('orders') }}">Orders</a>
            <a class="list-group-item list-group-item-action" href="{{ route('appointments') }}">Appointments</a>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="checkout-form panel-card p-4">
            <h3>Profile</h3>
            <form id="profile-form">
              <div class="row g-3">
                <div class="col-md-6"><label for="profName">Name *</label><input id="profName" type="text"
                    class="form-control" placeholder="Your full name" required></div>
                <div class="col-md-6"><label for="profEmail">Email</label><input id="profEmail" type="email"
                    class="form-control" placeholder="Your email" disabled title="Email cannot be changed"></div>
                <div class="col-md-6"><label for="profPhone">Phone *</label><input id="profPhone" type="tel"
                    class="form-control" placeholder="Your phone" required></div>
              </div>
              <hr class="my-4">
              <h5>Billing / Shipping Address</h5>
              <div class="row g-3">
                <div class="col-md-6"><label for="profFirstName">First Name *</label><input id="profFirstName"
                    type="text" class="form-control" placeholder="First name" required></div>
                <div class="col-md-6"><label for="profLastName">Last Name *</label><input id="profLastName" type="text"
                    class="form-control" placeholder="Last name" required></div>
                <div class="col-md-6"><label for="profCompany">Company (optional)</label><input id="profCompany"
                    type="text" class="form-control" placeholder="Company (optional)"></div>
                <div class="col-md-6"><label for="profCountry">Country *</label><input id="profCountry" type="text"
                    class="form-control" value="Pakistan" placeholder="Country" required></div>
                <div class="col-md-6"><label for="profCity">City *</label><input id="profCity" type="text"
                    class="form-control" placeholder="City" required></div>
                <div class="col-md-6"><label for="profState">State/Province *</label><input id="profState" type="text"
                    class="form-control" placeholder="State or Province" required></div>
                <div class="col-md-6"><label for="profPostal">Postal Code *</label><input id="profPostal" type="text"
                    class="form-control" placeholder="Postal code" required></div>
                <div class="col-md-12"><label for="profAddress1">Address Line 1 *</label><input id="profAddress1"
                    type="text" class="form-control" placeholder="Street address" required></div>
                <div class="col-md-12"><label for="profAddress2">Address Line 2</label><input id="profAddress2"
                    type="text" class="form-control" placeholder="Apartment, suite, unit, etc. (optional)"></div>
              </div>
              <button type="submit" id="saveProfileBtn" class="btn btn-theme mt-3">Save Profile</button>
            </form>
            <a id="viewOrdersLink" class="btn btn-border mt-3" href="{{ route('orders') }}">View Orders</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

