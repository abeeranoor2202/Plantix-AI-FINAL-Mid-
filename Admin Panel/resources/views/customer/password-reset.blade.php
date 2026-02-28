@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

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

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
              @csrf
              <input type="hidden" name="token" value="{{ $token }}">
              <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                  placeholder="Enter your email" value="{{ old('email', request('email')) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                  placeholder="Enter a new password (min 8 characters)" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                  placeholder="Repeat new password" required>
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

