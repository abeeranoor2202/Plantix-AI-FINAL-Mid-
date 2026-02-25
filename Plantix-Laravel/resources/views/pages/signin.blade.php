@extends('layouts.app')

@section('title', 'Plantix-AI')

@section('header')
@include('partials.header-notopbar')
@endsection

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
  <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default" style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Sign In</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Sign In</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Sign In -->
  <div class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="checkout-form panel-card p-4">
            <h3 class="mb-3">Welcome back</h3>
            <div class="mb-3">
              <label class="form-label me-3">Sign in as</label>
              <div class="btn-group" role="group" aria-label="Role">
                <input type="radio" class="btn-check" name="signinRole" id="roleCustomer" value="customer"
                  autocomplete="off" checked>
                <label class="btn btn-outline-success" for="roleCustomer">Customer</label>
                <input type="radio" class="btn-check" name="signinRole" id="roleExpert" value="expert"
                  autocomplete="off">
                <label class="btn btn-outline-success" for="roleExpert">Expert</label>
              </div>
            </div>
            <form id="signin-form">
              <div class="mb-3">
                <label>Email</label>
                <input id="signinEmail" type="email" class="form-control" placeholder="Enter your email" required data-label="Email address">
              </div>
              <div class="mb-3">
                <label>Password</label>
                <input id="signinPassword" type="password" class="form-control" placeholder="Enter your password"
                  minlength="8" required data-label="Password (min 8 characters)">
              </div>
              <div class="mb-3 d-flex justify-content-between">
                <a id="forgotLink" href="{{ route('password.forgot') }}" class="small">Forgot password?</a>
              </div>
              <button class="btn btn-theme w-100" type="submit">Sign In</button>
            </form>
            <p class="mt-3 mb-0">Don't have an account? <a id="go-to-signup" href="{{ route('signup') }}">Create one</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

