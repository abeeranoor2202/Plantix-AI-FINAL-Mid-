@extends('layouts.frontend')

@section('title', 'My Appointments')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default">
    <div class="container">
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
@if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($appointments->isEmpty())
              <p class="text-muted">No appointments yet. <a href="{{ route('appointment.book') }}">Book one now</a>.</p>
            @else
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>Expert</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($appointments as $appt)
                  <tr>
                    <td><a href="{{ route('appointment.details', $appt->id) }}">#{{ $appt->id }}</a></td>
                    <td>{{ $appt->scheduled_at ? $appt->scheduled_at->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $appt->expert->user->name ?? 'Any Expert' }}</td>
                    <td><span class="badge bg-{{ $appt->status === 'completed' ? 'success' : ($appt->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($appt->status) }}</span></td>
                    <td class="d-flex gap-1">
                      <a href="{{ route('appointment.details', $appt->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                      @if(in_array($appt->status, ['pending','confirmed']))
                      <form method="POST" action="{{ route('appointment.cancel', $appt->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel?')">Cancel</button>
                      </form>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            {{ $appointments->links() }}
            @endif
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

