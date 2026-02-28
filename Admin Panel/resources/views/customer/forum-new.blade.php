@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/forum.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>New Thread</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('forum') }}">Forum</a></li>
              <li class="active">New</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="forum-new-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="panel-card p-4">
            <h3 class="mb-3">Create a Thread</h3>
            <form id="newThreadForm">
              <div class="mb-3"><label class="form-label">Title</label><input id="threadTitle" class="form-control"
                  placeholder="Brief title" required></div>
              <div class="mb-3"><label class="form-label">Details</label><textarea id="threadBody" class="form-control"
                  rows="6" placeholder="Describe the problem or topic" required></textarea></div>
              <div class="mb-3">
                <label class="form-label">Tags (comma separated)</label>
                <input id="threadTags" class="form-control" placeholder="e.g., Wheat, Pest, Irrigation" data-label="Tags (comma-separated)">
                <div id="presetTags" class="mt-2"></div>
                <div class="form-text">Use tags like crop names, issues, or regions. Click chips to toggle.</div>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-theme">Post</button>
                <a href="{{ route('forum') }}" class="btn btn-border">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

