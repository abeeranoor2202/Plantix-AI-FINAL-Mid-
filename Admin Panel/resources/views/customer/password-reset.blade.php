@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/auth-pages.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Reset Password</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Reset Password</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="password-reset-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="checkout-form panel-card p-4">
            <h3 class="mb-3">Choose a new password</h3>
            <div class="mb-3">
              <label class="form-label me-3">Account type</label>
              <div class="btn-group" role="group" aria-label="Role">
                <input type="radio" class="btn-check" name="resetRole" id="resetCustomer" value="customer"
                  autocomplete="off" checked>
                <label class="btn btn-outline-success" for="resetCustomer">Customer</label>
                <input type="radio" class="btn-check" name="resetRole" id="resetExpert" value="expert"
                  autocomplete="off">
                <label class="btn btn-outline-success" for="resetExpert">Expert</label>
              </div>
            </div>
            <form id="reset-form">
              <div class="mb-3">
                <label>Email</label>
                <input id="resetEmail" type="email" class="form-control" placeholder="Enter your email" required data-label="Email address">
              </div>
              <div class="mb-3">
                <label>Reset Code</label>
                <input id="resetToken" type="text" class="form-control" placeholder="Enter the code sent to your email"
                  required>
              </div>
              <div class="mb-3">
                <label>New Password</label>
                <input id="resetNewPassword" type="password" class="form-control" placeholder="Enter a new password"
                  minlength="8" required data-label="New password (min 8 characters)">
              </div>
              <button class="btn btn-theme w-100" type="submit">Reset Password</button>
            </form>
            <p class="mt-3 mb-0">Back to <a href="{{ route('signin') }}">Sign In</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

