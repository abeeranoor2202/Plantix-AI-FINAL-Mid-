@extends('layouts.frontend')

@section('title', 'Community Forum | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.01));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Community Forum</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Forum</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div id="forum-page" class="py-5" style="background: var(--agri-bg); min-height: 70vh;">
        <div class="container-agri pb-5">
            
            <div class="row align-items-center mb-4">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <h2 class="fw-bold text-dark mb-2">Farmer's Discussion Board</h2>
                    <p class="text-muted m-0">Connect with other farmers and experts, share knowledge, and solve agricultural issues together.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('forum.new') }}" class="btn-agri btn-agri-primary shadow-sm px-4">
                        <i class="fas fa-plus-circle me-2"></i> Start a Discussion
                    </a>
                </div>
            </div>

            <div class="card-agri p-4 border-0 shadow-sm">
                <!-- Forum Controls -->
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4 bg-light p-3 rounded-3 border">
                    <form method="GET" action="{{ route('forum') }}" class="d-flex flex-wrap gap-2 flex-grow-1" style="max-width: 600px;">
                        <div class="input-group" style="flex: 1; min-width: 250px;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-agri border-start-0 ps-0"
                                placeholder="Search threads, topics, or keywords..."
                                style="border-radius: 0 0.375rem 0.375rem 0;"
                            />
                        </div>
                        <select
                            name="category"
                            class="form-select text-dark shadow-none"
                            onchange="this.form.submit()"
                            style="width: 150px; font-weight: 500; border-color: #dee2e6;"
                        >
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="d-none"></button>
                    </form>
                </div>

                <!-- Forum Threads List -->
                <div id="forumThreads" class="list-group list-group-flush border rounded-3 overflow-hidden">
                    @forelse($threads->items() as $thread)
                    <a href="{{ route('forum.thread', $thread->id) }}"
                       class="list-group-item list-group-item-action px-4 py-4 position-relative text-decoration-none" style="transition: all 0.2s; border-left: 4px solid transparent; border-bottom: 1px solid var(--agri-border);" onmouseover="this.style.background='#F9FAFB'; this.style.borderLeft='4px solid var(--agri-primary)';" onmouseout="this.style.background='white'; this.style.borderLeft='4px solid transparent';">
                        
                        <div class="d-flex align-items-start gap-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 56px; height: 56px; font-size: 1.5rem; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: var(--agri-primary); border: 1px solid rgba(16, 185, 129, 0.2);">
                                {{ strtoupper(substr($thread->user->name ?? 'F', 0, 1)) }}
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                    <h5 class="mb-0 fw-bold text-dark pe-md-4" style="line-height: 1.4;">{{ $thread->title }}</h5>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        <span class="badge bg-light text-dark border shadow-sm d-flex align-items-center gap-1" style="font-size: 12px;">
                                            <i class="fas fa-reply text-success"></i> {{ $thread->replies->count() }} {{ Str::plural('Reply', $thread->replies->count()) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small text-uppercase fw-bold mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <span class="text-primary"><i class="fas fa-user-circle me-1"></i>{{ $thread->user->name ?? 'Farmer' }}</span>
                                    <span><i class="fas fa-hashtag me-1 opacity-50"></i>{{ $thread->category?->name ?? 'General' }}</span>
                                    <span><i class="far fa-clock me-1 opacity-50"></i>{{ $thread->created_at->diffForHumans() }}</span>
                                </div>
                                
                                <p class="text-dark fw-medium mb-0" style="line-height: 1.6; font-size: 14px; opacity: 0.8;">
                                    {{ Str::limit($thread->body, 180) }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="position-absolute text-muted opacity-25" style="top: 50%; right: 20px; transform: translateY(-50%); font-size: 1.5rem;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    @empty
                    <div class="p-5 text-center my-4 border-0">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border" style="width: 90px; height: 90px;">
                            <i class="far fa-comments fs-2 text-muted opacity-50"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No Discussions Found</h4>
                        <p class="text-muted small fw-medium mb-0">Try adjusting your search criteria or start a new discussion.</p>
                    </div>
                    @endforelse
                </div>

                @if($threads->hasPages())
                <div class="mt-4 pt-4 border-top">
                    {{ $threads->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
