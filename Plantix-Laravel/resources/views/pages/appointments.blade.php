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
    <script src="{{ asset('assets/js/appointments.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default >
    <div class=" container">
    <div class="row">
      <div class="col-lg-8 offset-lg-2">
        <h1>My Appointments</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
            <li class="active">Appointments</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
  </div>

  <div id="appointments-page" class="default-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="list-group">
            <a class="list-group-item list-group-item-action" href="{{ route('account.profile') }}">Profile</a>
            <a class="list-group-item list-group-item-action" href="{{ route('orders') }}">Orders</a>
            <a class="list-group-item list-group-item-action active" href="{{ route('appointments') }}">Appointments</a>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="panel-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="mb-0">Appointments</h3>
              <a href="{{ route('appointment.book') }}" class="btn btn-theme btn-sm"><i class="fas fa-plus"></i> Book
                Appointment</a>
            </div>
            <div id="appointmentsTableWrap" class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>Type</th>
                    <th>Expert</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="appointmentsTableBody">
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
@endsection

