@extends('expert.layouts.app')
@section('title', 'Community Discussions')
@section('page-title', 'Farmer Community')

@section('content')
{{-- Filter/Search --}}
<div class="card-agri p-0 border-0 mb-4 bg-white hover-lift">
    <div class="p-4 bg-light border-bottom">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-search me-2 text-primary"></i>Find Discussions</h5>
    </div>
    <div class="p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8 col-lg-6">
                <label class="form-label text-dark fw-bold small mb-2">Search Threads</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted fw-bold border" style="border-right: none;"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-agri" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                           placeholder="Search by topic, keyword, or crop..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-6 d-flex gap-2">
                <button type="submit" class="btn-agri btn-agri-primary px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                @if(isset($filters['search']) && $filters['search'] != '')
                <a href="{{ route('expert.forum.index') }}" class="btn-agri btn-agri-outline px-4 py-2 bg-white d-flex align-items-center gap-2">
                    <i class="fas fa-times"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Threads List --}}
<div class="card-agri p-0 border-0 bg-white overflow-hidden shadow-sm">
    <div class="p-4 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center"><i class="fas fa-comments me-2 text-success"></i>Recent Questions</h5>
        <span class="badge-agri bg-success bg-opacity-10 text-success border border-success border-opacity-25 shadow-sm">{{ $threads->total() }} Discussions Found</span>
    </div>
    
    <div class="list-group list-group-flush pt-1">
        @forelse($threads->items() as $thread)
        <a href="{{ route('expert.forum.show', $thread) }}"
           class="list-group-item list-group-item-action border-bottom-dashed px-4 py-4 position-relative text-decoration-none" style="transition: all 0.2s; border-left: 4px solid transparent;" onmouseover="this.style.background='#F9FAFB'; this.style.borderLeft='4px solid var(--agri-primary)';" onmouseout="this.style.background='transparent'; this.style.borderLeft='4px solid transparent';">
            
            <div class="d-flex align-items-start gap-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0 border border-primary border-opacity-25"
                     style="width: 56px; height: 56px; font-size: 1.5rem; font-family: var(--font-heading); font-weight: 700;">
                    {{ strtoupper(substr($thread->user->name ?? 'F', 0, 1)) }}
                </div>
                
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <h5 class="mb-0 fw-bold text-dark pe-md-4" style="line-height: 1.4;">{{ $thread->title }}</h5>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <span class="badge-agri bg-light text-dark border shadow-sm d-flex align-items-center gap-1" style="font-size: 12px;">
                                <i class="fas fa-reply text-success"></i> {{ $thread->replies_count }} {{ Str::plural('Reply', $thread->replies_count) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small text-uppercase fw-bold mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                        <span class="text-primary"><i class="fas fa-user-circle me-1"></i>{{ $thread->user->name }}</span>
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
        <div class="p-5 text-center my-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border border-dashed" style="width: 90px; height: 90px;">
                <i class="far fa-comments fs-2 text-muted opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark">No Discussions Found</h4>
            <p class="text-muted small fw-medium mb-0">Try adjusting your search criteria or check back later for new farmer questions.</p>
        </div>
        @endforelse
    </div>
    
    @if($threads->hasPages())
    <div class="p-4 bg-light border-top text-center">
        {{ $threads->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
