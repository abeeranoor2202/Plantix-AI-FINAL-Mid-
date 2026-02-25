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
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/forum.js') }}"></script>
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<!-- Breadcrumb -->
  <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>My Account</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li class="active">Expert Panel</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Expert Dashboard (aligned to customer UX) -->
  <div id="expert-dashboard" class="default-padding">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="list-group" id="exSideNav">
            <a href="#" class="list-group-item list-group-item-action active" data-target="section-profile">Profile</a>
            <a href="#" class="list-group-item list-group-item-action" data-target="section-appts">Appointments</a>
            <a href="#" class="list-group-item list-group-item-action" data-target="section-forum">Forum Replies</a>
            <a href="#" class="list-group-item list-group-item-action" data-target="section-notif">Notifications</a>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="checkout-form panel-card p-4">
            <div id="section-profile" class="ex-section">
              <h3 class="mb-3">Profile</h3>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input id="exName" type="text" class="form-control" placeholder="Your name" data-label="Name">
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="exEmail">Email (read-only)</label>
                  <input id="exEmail" type="email" class="form-control" placeholder="your@email.com"
                    title="Registered email" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Agency / Organization</label>
                  <input id="exAgency" type="text" class="form-control" placeholder="e.g., PlantixAI" data-label="Agency / Organization">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input id="exPhone" type="text" class="form-control" placeholder="Contact number" data-label="Phone number (include country code)">
                </div>
                <div class="col-12">
                  <label class="form-label">Bio</label>
                  <textarea id="exBio" class="form-control" rows="3" placeholder="Short professional bio" data-label="Short professional bio"></textarea>
                </div>
                <div class="col-12">
                  <button id="exSaveProfile" class="btn btn-theme">Save Profile</button>
                </div>
              </div>
            </div>

            <div id="section-appts" class="ex-section hidden">
              <h3 class="mb-3">Appointments</h3>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>When</th>
                      <th>Farmer</th>
                      <th>Reason</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="exApptsBody"></tbody>
                </table>
              </div>
            </div>

            <div id="section-forum" class="ex-section hidden">
              <h3 class="mb-3">Forum Replies</h3>
              <div class="alert alert-info small">Recent threads. Click reply to answer quickly. For full context, open
                thread.</div>
              <div id="exForumThreads" class="list-group"></div>
            </div>



            <div id="section-notif" class="ex-section hidden">
              <h3 class="mb-3">Notifications</h3>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div></div>
                <button id="exClearNotifs" class="btn btn-sm btn-border">Clear all</button>
              </div>
              <div id="exNotifs" class="list-group"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
@endsection

