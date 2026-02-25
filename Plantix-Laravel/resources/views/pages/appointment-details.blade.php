@extends('layouts.app')

@section('title', 'Appointment Details')

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
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Appointment Details</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('appointments') }}">Appointments</a></li>
              <li class="active">Details</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="appointment-details-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="panel-card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h3 class="mb-0">Appointment <span id="ad-id">#</span></h3>
              <div>
                <button id="ad-cancel" class="btn btn-outline-danger btn-sm me-1 hidden">Cancel</button>
                <button id="ad-reschedule" class="btn btn-outline-secondary btn-sm me-2 hidden">Reschedule</button>
                <a href="{{ route('appointments') }}" class="btn btn-border btn-sm">Back to Appointments</a>
                <a href="{{ route('shop') }}" class="btn btn-theme btn-sm">Continue Shopping</a>
              </div>
            </div>
            <hr>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>Summary</h5>
                <ul class="list-unstyled mb-0">
                  <li><strong>Date/Time:</strong> <span id="ad-datetime">-</span></li>
                  <li><strong>Type:</strong> <span id="ad-type">-</span></li>
                  <li><strong>Channel:</strong> <span id="ad-channel">-</span></li>
                  <li><strong>Expert:</strong> <span id="ad-expert">-</span></li>
                  <li><strong>Status:</strong> <span id="ad-status">-</span></li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Contact</h5>
                <address id="ad-address" class="mb-0"></address>
              </div>
              <div class="col-12">
                <h5>Notes</h5>
                <div id="ad-notes" class="border rounded p-3 bg-light">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

