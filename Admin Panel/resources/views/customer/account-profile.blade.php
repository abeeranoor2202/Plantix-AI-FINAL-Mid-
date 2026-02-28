@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

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

            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif

            <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data">
              @csrf @method('PUT')
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name *</label>
                  <input name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required>
                  @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" value="{{ $user->email }}" disabled title="Email cannot be changed">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input name="phone" type="tel" class="form-control" value="{{ old('phone', $user->phone) }}">
                  @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Profile Photo</label>
                  <input name="profile_photo" type="file" class="form-control" accept="image/*">
                  @error('profile_photo')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
              </div>
              <button type="submit" class="btn btn-theme mt-3">Save Profile</button>
            </form>

            <hr class="my-4">
            <h5>Change Password</h5>
            <form method="POST" action="{{ route('account.password') }}">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Current Password *</label>
                  <input name="current_password" type="password" class="form-control" required>
                  @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">New Password *</label>
                  <input name="password" type="password" class="form-control" required>
                  @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Confirm Password *</label>
                  <input name="password_confirmation" type="password" class="form-control" required>
                </div>
              </div>
              <button type="submit" class="btn btn-border mt-3">Update Password</button>
            </form>

            <div class="mt-3">
              <a href="{{ route('orders') }}" class="btn btn-border">View Orders</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

