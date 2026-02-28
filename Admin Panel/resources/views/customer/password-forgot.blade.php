@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

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

            @if(session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
              @csrf
              <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                  placeholder="Enter your registered email" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <button class="btn btn-theme w-100" type="submit">Send Reset Link</button>
            </form>
            <p class="mt-3 mb-0">Remembered it? <a href="{{ route('signin') }}">Back to Sign In</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

