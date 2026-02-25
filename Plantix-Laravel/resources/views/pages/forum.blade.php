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
<div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
      style="background-image: url({{ asset('assets/img/banner7.jpg') }})"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Forum</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="active">Forum</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div id="forum-page" class="default-padding">
      <div class="container">
        <div class="panel-card p-4">
          <div
            class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3"
          >
            <div class="d-flex gap-2">
              <input
                id="forumSearch"
                class="form-control"
                placeholder="Search threads"
                data-label="Search threads"
              />
              <select
                id="forumSort"
                class="form-control"
                title="Sort"
                data-label="Sort threads"
              >
                <option value="new">Newest</option>
                <option value="active">Active</option>
                <option value="votes">Top</option>
              </select>
            </div>
            <a href="{{ route('forum.new') }}" class="btn btn-theme btn-sm"
              ><i class="fas fa-plus"></i> New Thread</a
            >
          </div>
          <div id="forumTags" class="mb-2"></div>
          <div id="forumThreads" class="list-group"></div>
        </div>
      </div>
    </div>
@endsection

