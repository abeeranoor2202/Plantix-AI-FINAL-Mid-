@extends('layouts.frontend')

@section('title', $thread->title . ' | Plantix-AI Forum')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.01));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Community Forum</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('forum') }}" class="text-success text-decoration-none">Forum</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Thread Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div id="forum-thread-page" class="py-5" style="background: var(--agri-bg); min-height: 70vh;">
        <div class="container-agri">
            <div class="row">
                <div class="col-lg-9 mx-auto">
                    
                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4 bg-success bg-opacity-10 border-success border-opacity-25" role="alert">
                            <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                            <div class="text-dark fw-medium">{{ session('success') }}</div>
                        </div>
                    @endif

                    <div class="card-agri p-4 p-md-5 border-0 shadow-sm mb-4">
                        {{-- Thread header --}}
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div class="flex-grow-1">
                                <h2 class="fw-bold text-dark mb-2" style="line-height: 1.3;">
                                    {{ $thread->title }}
                                </h2>
                                <div class="d-flex align-items-center flex-wrap gap-2 text-muted small">
                                    <span class="d-flex align-items-center fw-medium text-dark">
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        {{ $thread->user->name ?? 'Unknown Farmer' }}
                                    </span>
                                    <span class="text-muted px-1">&bull;</span>
                                    <span><i class="far fa-clock me-1"></i> {{ $thread->created_at->format('d M Y H:i') }}</span>
                                    
                                    @if($thread->category) 
                                        <span class="text-muted px-1">&bull;</span>
                                        <span class="badge bg-light text-secondary border px-2 py-1"><i class="fas fa-folder me-1"></i> {{ $thread->category->name }}</span>
                                    @endif

                                    @if($thread->is_pinned ?? false)
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2 py-1 ms-2"><i class="fas fa-thumbtack me-1"></i> Pinned</span>
                                    @endif
                                    
                                    @if($thread->is_solved ?? false)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 ms-2"><i class="fas fa-check-double me-1"></i> Solved</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2 shrink-0">
                                
                                <a href="{{ route('forum') }}" class="btn-agri btn-agri-outline btn-sm shadow-sm py-2 px-3 text-dark">
                                    <i class="fas fa-arrow-left me-1"></i> Forum
                                </a>
                            </div>
                        </div>

                        <hr class="mb-4 opacity-10">

                        {{-- Thread body --}}
                        <div class="thread-content" style="font-size: 16px; line-height: 1.8; color: var(--bs-gray-800);">
                            {!! nl2br(e($thread->body)) !!}
                        </div>
                    </div>

                    {{-- Replies Section --}}
                    <div class="mb-4 d-flex align-items-center gap-3">
                        <h4 class="fw-bold text-dark m-0">Responses <span class="badge bg-light text-muted border ms-2 border-pill fs-6">{{ $replies->total() }}</span></h4>
                        <div class="flex-grow-1 border-top"></div>
                    </div>

                    <div class="replies-list d-flex flex-column gap-3 mb-5">
                        @forelse($replies as $reply)
                            <div class="card-agri p-4 border-0 shadow-sm {{ $reply->is_official ? 'border border-success border-opacity-50 border-2' : '' }}" style="{{ $reply->is_official ? 'background-color: rgba(16, 185, 129, 0.02);' : '' }}">
                                @if($reply->is_official)
                                    <div class="badge bg-success text-white position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> Official Answer
                                    </div>
                                @endif
                                
                                <div class="d-flex align-items-start mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shrink-0" style="width: 48px; height: 48px; font-size: 18px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark m-0">{{ $reply->user->name ?? 'Farmer' }}</h6>
                                        <span class="text-muted small"><i class="far fa-clock me-1"></i> {{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                
                                <div class="reply-content text-dark" style="font-size: 15px; line-height: 1.7; padding-left: 64px;">
                                    {!! nl2br(e($reply->body)) !!}
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5 bg-white rounded-3 border border-dashed">
                                <div class="mb-3">
                                    <i class="far fa-comments text-muted fs-1 opacity-50"></i>
                                </div>
                                <h5 class="fw-bold text-dark">No responses yet</h5>
                                <p class="text-muted mb-0">Be the first to share your knowledge and help answer this question.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($replies->hasPages())
                        <div class="d-flex justify-content-center mt-2 mb-4">
                            {{ $replies->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                    {{-- Reply form --}}
                    @auth
                        <div class="card-agri p-4 border-0 shadow-sm" style="border-top: 4px solid var(--agri-primary) !important;">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-reply text-muted me-2"></i> Leave a Reply</h5>
                            
                            @if($errors->any())
                                <div class="alert alert-danger py-2 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger rounded-3">
                                    <ul class="mb-0 small fw-medium">
                                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('forum.reply', $thread->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="body" class="form-agri focus-primary" rows="4" placeholder="Share your experience, solution, or insight..." required>{{ old('body') }}</textarea>
                                </div>
                                <div class="text-end">
                                    <button class="btn-agri btn-agri-primary shadow-sm px-4">
                                        Post Reply <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card bg-light border-0 p-4 text-center rounded-3">
                            <i class="fas fa-lock text-muted fs-3 mb-3"></i>
                            <h5 class="fw-bold text-dark">Join the Conversation</h5>
                            <p class="text-muted mb-3">You must be logged in to reply to this thread.</p>
                            <div>
                                <a href="{{ route('login') }}" class="btn-agri btn-agri-primary px-4 shadow-sm">Sign In</a>
                            </div>
                        </div>
                    @endauth

                </div>
            </div>
        </div>
    </div>
@endsection
