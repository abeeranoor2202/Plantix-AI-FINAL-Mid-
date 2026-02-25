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
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/auth-pages.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Forgot Password</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Forgot Password</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="password-forgot-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="checkout-form panel-card p-4">
            <h3 class="mb-3">Reset your password</h3>
            <div class="mb-3">
              <label class="form-label me-3">Account type</label>
              <div class="btn-group" role="group" aria-label="Role">
                <input type="radio" class="btn-check" name="forgotRole" id="forgotCustomer" value="customer"
                  autocomplete="off" checked>
                <label class="btn btn-outline-success" for="forgotCustomer">Customer</label>
                <input type="radio" class="btn-check" name="forgotRole" id="forgotExpert" value="expert"
                  autocomplete="off">
                <label class="btn btn-outline-success" for="forgotExpert">Expert</label>
              </div>
            </div>
            <form id="forgot-form">
              <div class="mb-3">
                <label>Email</label>
                <input id="forgotEmail" type="email" class="form-control" placeholder="Enter your registered email"
                  required data-label="Email address">
              </div>
              <button class="btn btn-theme w-100" type="submit">Send reset code</button>
            </form>
            <p class="mt-3 mb-0">Remembered it? <a href="{{ route('signin') }}">Back to Sign In</a></p>
            <div id="demoTokenHint" class="alert alert-info mt-3 hidden"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

