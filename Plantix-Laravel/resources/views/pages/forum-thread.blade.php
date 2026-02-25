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
    <script src="{{ asset('assets/js/forum.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Forum</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('forum') }}">Forum</a></li>
              <li class="active">Thread</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div id="forum-thread-page" class="default-padding">
    <div class="container">
      <div class="panel-card p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h3 class="mb-1"><span id="th-title-text">Thread</span> <span id="th-pinned"
                class="badge bg-warning text-dark ms-2 hidden">Pinned</span></h3>
            <div class="text-muted small" id="th-meta">-</div>
            <div class="mt-2" id="th-tags"></div>
          </div>
          <div class="text-end">
            <div class="btn-group">
              <button id="th-up" class="btn btn-sm btn-outline-secondary" title="Upvote"><i
                  class="fas fa-thumbs-up"></i></button>
              <button id="th-down" class="btn btn-sm btn-outline-secondary" title="Downvote"><i
                  class="fas fa-thumbs-down"></i></button>
            </div>
            <div class="mt-1 small">Votes: <span id="th-votes">0</span></div>
            <button id="th-solved" class="btn btn-sm btn-outline-success mt-2 hidden">Mark Solved</button>
            <button id="th-pin" class="btn btn-sm btn-outline-warning mt-2 hidden">Pin</button>
            <button id="th-delete" class="btn btn-sm btn-outline-danger mt-2 hidden">Delete</button>
            <div id="th-pin-status" class="small text-muted mt-1"></div>
          </div>
        </div>
        <hr>
        <div id="th-body" class="mb-4"></div>
        <h5>Replies</h5>
        <div id="th-posts" class="mb-3"></div>
        <div id="th-reply-box" class="border rounded p-3 bg-light">
          <textarea id="replyBody" class="form-control mb-2" rows="4" placeholder="Write a reply..." required data-label="Reply message"></textarea>
          <button id="replyBtn" class="btn btn-theme btn-sm">Reply</button>
        </div>
      </div>
    </div>
  </div>
@endsection

