@extends('layouts.app')

@section('title', 'Book Appointment')

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
<div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Book Appointment</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li><a href="{{ route('appointments') }}">Appointments</a></li>
                <li class="active">Book</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div id="appointment-book-page" class="default-padding">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="panel-card p-4">
              <h3 class="mb-3">Appointment Details</h3>
              <form id="appointment-form">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="apptType" class="form-label">Type</label>
                    <select
                      id="apptType"
                      class="form-control"
                      required
                      data-label="Appointment type"
                    >
                      <option value="Consultation">Consultation</option>
                      <option value="Soil Testing">Soil Testing</option>
                      <option value="Field Visit">Field Visit</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="apptExpert" class="form-label">Expert</label>
                    <select
                      id="apptExpert"
                      class="form-control"
                      data-label="Expert"
                    >
                      <option value="">Any available expert</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="apptDate" class="form-label">Date</label>
                    <input
                      type="date"
                      id="apptDate"
                      class="form-control"
                      required
                      data-label="Appointment date"
                    />
                  </div>
                  <div class="col-md-6">
                    <label for="apptTime" class="form-label">Time</label>
                    <input
                      type="time"
                      id="apptTime"
                      class="form-control"
                      required
                      data-label="Appointment time"
                    />
                  </div>
                  <div class="col-md-6">
                    <label for="apptChannel" class="form-label">Channel</label>
                    <select
                      id="apptChannel"
                      class="form-control"
                      required
                      data-label="Appointment channel"
                    >
                      <option value="In-Person">In-Person</option>
                      <option value="Phone">Phone</option>
                      <option value="Video">Video</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label for="apptNotes" class="form-label"
                      >Notes (optional)</label
                    >
                    <textarea
                      id="apptNotes"
                      rows="3"
                      class="form-control"
                      placeholder="Any details or requests"
                      data-label="Appointment notes"
                    ></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Location</label>
                    <input
                      type="text"
                      id="apptAddress1"
                      class="form-control mb-2"
                      placeholder="Address line 1"
                      data-label="Address line 1"
                    />
                    <input
                      type="text"
                      id="apptAddress2"
                      class="form-control"
                      placeholder="Address line 2 (optional)"
                      data-label="Address line 2"
                    />
                  </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" class="btn btn-theme">
                    Book Appointment
                  </button>
                  <a href="{{ route('appointments') }}" class="btn btn-border">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

