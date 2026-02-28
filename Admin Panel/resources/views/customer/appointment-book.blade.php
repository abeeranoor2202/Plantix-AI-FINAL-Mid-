@extends('layouts.frontend')

@section('title', 'Book Appointment')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

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
              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
              <form method="POST" action="{{ route('appointment.store') }}">
                @csrf
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Expert</label>
                    <select name="expert_id" class="form-control">
                      <option value="">Any available expert</option>
                      @foreach($experts as $expert)
                      <option value="{{ $expert->id }}" {{ old('expert_id') == $expert->id ? 'selected' : '' }}>
                        {{ $expert->user->name ?? 'Expert #'.$expert->id }}
                        @if($expert->specialization) &mdash; {{ $expert->specialization }}@endif
                      </option>
                      @endforeach
                    </select>
                    @error('expert_id')<div class="text-danger small">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Date &amp; Time *</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control"
                           value="{{ old('scheduled_at') }}"
                           min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                    @error('scheduled_at')<div class="text-danger small">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-12">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Describe your concern or any details">{{ old('notes') }}</textarea>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                  </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                  <button type="submit" class="btn btn-theme">Book Appointment</button>
                  <a href="{{ route('appointments') }}" class="btn btn-border">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

