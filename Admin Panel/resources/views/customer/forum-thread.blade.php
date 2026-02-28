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

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Thread header --}}
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h3 class="mb-1">
              {{ $thread->title }}
              @if($thread->is_pinned ?? false)
                <span class="badge bg-warning text-dark ms-2">Pinned</span>
              @endif
              @if($thread->is_solved ?? false)
                <span class="badge bg-success ms-2">Solved</span>
              @endif
            </h3>
            <div class="text-muted small">
              Posted by {{ $thread->user->name ?? 'Unknown' }} &bull; {{ $thread->created_at->format('d M Y H:i') }}
              @if($thread->category) &bull; <span class="badge bg-secondary">{{ $thread->category->name }}</span>@endif
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            @auth
            @if(auth('web')->id() === $thread->user_id)
            <form method="POST" action="{{ route('forum.delete', $thread->id) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this thread?')"><i class="fas fa-trash"></i></button>
            </form>
            @endif
            @endauth
            <a href="{{ route('forum') }}" class="btn btn-border btn-sm">Back to Forum</a>
          </div>
        </div>
        <hr>

        {{-- Thread body --}}
        <div class="mb-4">{!! nl2br(e($thread->body)) !!}</div>

        {{-- Replies --}}
        <h5>Replies ({{ $thread->replies->count() }})</h5>
        @forelse($thread->replies as $reply)
        <div class="border rounded p-3 mb-2 {{ $reply->is_accepted ? 'border-success' : '' }}">
          <div class="d-flex justify-content-between">
            <strong>{{ $reply->user->name ?? 'User' }}</strong>
            <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
          </div>
          <p class="mb-0 mt-1">{{ $reply->body }}</p>
        </div>
        @empty
        <p class="text-muted">No replies yet. Be the first to reply!</p>
        @endforelse

        {{-- Reply form --}}
        @auth
        <div class="border rounded p-3 bg-light mt-3">
          <h6>Leave a Reply</h6>
          @if($errors->any())
            <div class="alert alert-danger py-2">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif
          <form method="POST" action="{{ route('forum.reply', $thread->id) }}">
            @csrf
            <textarea name="body" class="form-control mb-2" rows="4" placeholder="Write a reply..." required>{{ old('body') }}</textarea>
            <button class="btn btn-theme btn-sm">Reply</button>
          </form>
        </div>
        @else
        <div class="alert alert-info mt-3">
          <a href="{{ route('login') }}">Sign in</a> to leave a reply.
        </div>
        @endauth

      </div>
    </div>
  </div>
@endsection

