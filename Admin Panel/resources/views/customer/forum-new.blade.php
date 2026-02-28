@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

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

            @if($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif

            <form method="POST" action="{{ route('forum.store') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Title *</label>
                <input name="title" class="form-control" placeholder="Brief title" value="{{ old('title') }}" required>
                @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="forum_category_id" class="form-control">
                  <option value="">-- Select Category --</option>
                  @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ old('forum_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                  @endforeach
                </select>
                @error('forum_category_id')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Details *</label>
                <textarea name="body" class="form-control" rows="6" placeholder="Describe the problem or topic" required>{{ old('body') }}</textarea>
                @error('body')<div class="text-danger small">{{ $message }}</div>@enderror
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

