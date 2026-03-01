@extends('expert.layouts.app')
@section('title', Str::limit($thread->title, 40))
@section('page-title', 'Community Thread View')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('expert.forum.index') }}" class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; text-decoration: none;">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <h4 class="mb-0 fw-bold text-dark">Discussion Details</h4>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8 d-flex flex-column gap-4">
        {{-- Thread Original Post --}}
        <div class="card-agri p-0 border-0 bg-white">
            <div class="p-4 p-md-5">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <span class="badge-agri border {{ match($thread->category?->name) { 'Crop Diseases' => 'border-danger bg-danger text-danger', 'Pest Control' => 'border-warning bg-warning text-warning', 'Fertilizers' => 'border-info bg-info text-info', default => 'border-success bg-success text-success' } }} bg-opacity-10 border-opacity-25 shadow-sm" style="padding: 0.4em 1em; font-size: 12px;">
                        <i class="fas fa-hashtag me-1"></i>{{ $thread->category?->name ?? 'General Discussion' }}
                    </span>
                    <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="far fa-clock me-1 text-primary"></i>Posted {{ $thread->created_at->diffForHumans() }}
                    </span>
                    @if($thread->is_locked)
                        <span class="badge-agri bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 ms-auto shadow-sm" style="padding: 0.4em 1em; font-size: 12px;">
                            <i class="fas fa-lock me-1"></i>Locked
                        </span>
                    @endif
                </div>
                
                <h3 class="fw-bold text-dark mb-4 pb-4 border-bottom-dashed" style="line-height: 1.4;">{{ $thread->title }}</h3>
                
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded text-dark border border-dashed">
                    <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-weight: bold; font-family: var(--font-heading); font-size: 1.4rem;">
                        {{ strtoupper(substr($thread->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-uppercase small text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Farmer / Author</div>
                        <div class="fw-bold fs-6">{{ $thread->user->name }}</div>
                    </div>
                    <div class="ms-auto pe-2 text-end">
                        <div class="badge-agri bg-white text-dark border shadow-sm d-flex align-items-center gap-2 px-3 py-2">
                            <i class="far fa-eye text-primary"></i> <span class="fw-bold">{{ $thread->views }}</span> Views
                        </div>
                    </div>
                </div>
                
                <div class="fs-6 text-dark fw-medium" style="line-height: 1.8;">
                    {!! nl2br(e($thread->body)) !!}
                </div>
            </div>
        </div>

        {{-- Replies --}}
        <h5 class="fw-bold text-dark mb-0 mt-2"><i class="far fa-comments text-success me-2"></i>Responses & Insights ({{ $replies->total() }})</h5>
        
        <div class="card-agri p-0 border-0 bg-white overflow-hidden shadow-sm">
            <div class="list-group list-group-flush pt-1">
                @forelse($replies as $reply)
                <div class="list-group-item border-bottom-dashed p-4 p-md-5 {{ $reply->is_expert_reply ? 'bg-success bg-opacity-10' : '' }}" style="border-left: {{ $reply->is_expert_reply ? '4px solid var(--agri-primary)' : '4px solid transparent' }};">
                    
                    @if($reply->is_expert_reply)
                        <div class="mb-4">
                            <span class="badge-agri bg-success text-white px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2 border border-success">
                                <i class="fas fa-check-circle"></i> Verified Expert Response
                            </span>
                        </div>
                    @endif

                    <div class="d-flex align-items-start gap-3 gap-md-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm
                            {{ $reply->is_expert_reply ? 'bg-success text-white border-2 border-white' : 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' }}"
                             style="width: 48px; height: 48px; font-size: 1.3rem; font-family: var(--font-heading); font-weight: 700;">
                            {{ strtoupper(substr($reply->user->name ?? '?', 0, 1)) }}
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark fs-6">{{ $reply->user->name }}</span>
                                <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;"><i class="far fa-clock me-1 text-primary opacity-50"></i>{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <div class="text-dark fw-medium mb-0" style="line-height: 1.7;">
                                {!! nl2br(e($reply->body)) !!}
                            </div>

                            @if($reply->is_official)
                                <div class="mt-3">
                                    <span class="badge-agri bg-warning text-dark px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2 border border-warning">
                                        <i class="fas fa-star"></i> Official Answer
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-5 text-center my-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border border-dashed" style="width: 80px; height: 80px;">
                        <i class="far fa-comment-dots fs-2 text-muted opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No Responses Yet</h5>
                    <p class="text-muted small fw-medium mb-0">Be the first expert to respond and provide valuable insights to this farmer.</p>
                </div>
                @endforelse
            </div>
        </div>

        @if($replies->hasPages())
        <div class="card-agri p-3 border-0 bg-white text-center shadow-sm">
            {{ $replies->links('pagination::bootstrap-5') }}
        </div>
        @endif

        {{-- Reply Form --}}
        @if(!$thread->is_locked)
        <div class="card-agri p-0 border-0 shadow-sm mt-2 mb-4">
            <div class="p-4 bg-success bg-opacity-10 border-bottom border-success border-opacity-25 d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;"><i class="fas fa-reply" style="font-size: 12px;"></i></div>
                <h5 class="mb-0 fw-bold text-success">Post Expert Answer</h5>
            </div>
            <div class="p-4 p-md-5 bg-white">
                <form method="POST" action="{{ route('expert.forum.reply', $thread) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small mb-2">Detailed Response <span class="text-danger">*</span></label>
                        <textarea name="body" rows="6" class="form-agri @error('body') is-invalid @enderror"
                                  placeholder="Provide your professional diagnosis or advice here (minimum 20 characters)..."
                                  required minlength="20">{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small mb-2 d-flex align-items-center gap-2">
                            Structured Recommendation
                            <span class="badge bg-light text-muted border fw-normal py-1 px-2">Optional</span>
                        </label>
                        <textarea name="recommendation" rows="3" class="form-agri"
                                  placeholder="If applicable, provide a clear, step-by-step action plan or formal recommendation..."></textarea>
                        <div class="form-text mt-2 small fw-medium text-success d-flex align-items-center gap-1">
                            <i class="fas fa-magic"></i> This will be highlighted uniquely as an authoritative recommendation.
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-light rounded border border-dashed gap-3 pt-3 mt-4 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-shield-alt text-success fs-5"></i>
                            <span class="text-dark fw-bold text-uppercase small" style="letter-spacing: 0.5px;">Posting as Verified Expert</span>
                        </div>
                        <button type="submit" class="btn-agri btn-agri-primary shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-paper-plane m-0"></i> Submit Response
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="bg-light border border-dashed text-center p-5 rounded mt-2 mb-4 d-flex flex-column align-items-center justify-content-center">
            <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mb-3 text-danger" style="width: 60px; height: 60px;">
                <i class="fas fa-lock fs-3"></i>
            </div>
            <h5 class="fw-bold text-dark">Discussion Closed</h5>
            <p class="mb-0 text-muted small fw-medium">This thread has been locked and is no longer accepting new replies.</p>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card-agri p-0 border-0 bg-white position-sticky" style="top: 100px;">
            <div class="p-4 bg-light border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle me-2 text-info"></i>Thread Details</h5>
            </div>
            <div class="p-4">
                <ul class="list-unstyled mb-0 d-grid gap-3">
                    <li class="d-flex flex-column gap-1 bg-light p-3 rounded border border-dashed">
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Topic Category</span>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-tags text-primary opacity-50"></i>
                            <span class="fw-bold text-dark">{{ $thread->category?->name ?? 'General' }}</span>
                        </div>
                    </li>
                    <li class="d-flex flex-column gap-1 bg-light p-3 rounded border border-dashed">
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Original Author</span>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-user-circle text-primary opacity-50"></i>
                            <span class="fw-bold text-dark">{{ $thread->user->name }}</span>
                        </div>
                    </li>
                    <li class="d-flex flex-column gap-1 bg-light p-3 rounded border border-dashed">
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Creation Date</span>
                        <div class="d-flex align-items-center gap-2">
                            <i class="far fa-calendar-alt text-primary opacity-50"></i>
                            <span class="fw-bold text-dark">{{ $thread->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </li>
                    <li class="d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted text-uppercase fw-bold d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="far fa-eye text-primary"></i> Total Views
                        </span>
                        <span class="badge-agri bg-light text-dark border">{{ $thread->views }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center p-3 border-top-dashed">
                        <span class="text-muted text-uppercase fw-bold d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="far fa-comments text-success"></i> Total Replies
                        </span>
                        <span class="badge-agri bg-success text-white shadow-sm">{{ $replies->total() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
