@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/auth-pages.js') }}"></script>
@endsection

@section('content')
<!-- Breadcrumb -->
  <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Sign Up</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Sign Up</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Sign Up -->
  <div class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="checkout-form panel-card p-4">
            <h3 class="mb-3">Create your account</h3>
            <form id="signup-form">
              <div class="mb-3">
                <label>Name</label>
                <input id="signupName" type="text" class="form-control" placeholder="Your full name" required data-label="Full name">
              </div>
              <div class="mb-3">
                <label>Email</label>
                <input id="signupEmail" type="email" class="form-control" placeholder="Enter your email" required data-label="Email address">
              </div>
              <div class="mb-3">
                <label>Password</label>
                <input id="signupPassword" type="password" class="form-control" placeholder="Choose a password"
                  minlength="8" required data-label="Password (min 8 characters)">
              </div>
              <div class="mb-3">
                <label>Phone</label>
                <input id="signupPhone" type="tel" class="form-control" placeholder="Your phone (optional)" data-label="Phone number (include country code, optional)">
              </div>
              <button class="btn btn-theme w-100" type="submit">Create Account</button>
            </form>
            <p class="mt-3 mb-0">Already have an account? <a id="go-to-signin" href="{{ route('signin') }}">Sign in</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

