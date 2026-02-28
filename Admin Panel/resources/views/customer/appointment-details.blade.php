@extends('layouts.frontend')

@section('title', 'Appointment Details')


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
@if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h3 class="mb-0">Appointment #{{ $appointment->id }}</h3>
              <div class="d-flex gap-2 flex-wrap">
                @if(in_array($appointment->status, ['pending','confirmed']))
                <form method="POST" action="{{ route('appointment.cancel', $appointment->id) }}">
                  @csrf
                  <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                </form>
                @endif
                <a href="{{ route('appointments') }}" class="btn btn-border btn-sm">Back to Appointments</a>
              </div>
            </div>
            <hr>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>Summary</h5>
                <ul class="list-unstyled mb-0">
                  <li><strong>Date/Time:</strong> {{ $appointment->scheduled_at ? $appointment->scheduled_at->format('d M Y H:i') : '-' }}</li>
                  <li><strong>Expert:</strong> {{ $appointment->expert->user->name ?? 'Any Expert' }}</li>
                  <li><strong>Status:</strong> <span class="badge bg-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($appointment->status) }}</span></li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Customer</h5>
                <address class="mb-0">
                  {{ auth('web')->user()->name }}<br>
                  {{ auth('web')->user()->email }}
                </address>
              </div>
              <div class="col-12">
                <h5>Notes</h5>
                <div class="border rounded p-3 bg-light">{{ $appointment->notes ?: 'No notes provided.' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

