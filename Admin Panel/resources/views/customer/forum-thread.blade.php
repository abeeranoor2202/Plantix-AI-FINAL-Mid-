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

                    <div class="card-agri mb-4" style="padding: 0; overflow: hidden;">
                        <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <h5 style="margin-bottom: 12px; font-weight: 800; color: var(--agri-text-heading); font-size: 18px; line-height: 1.4;">{{ $thread->title }}</h5>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    @php
                                        $status = $thread->status ?? 'open';
                                        $colors = [
                                            'open'     => ['#D1FAE5', '#065F46'],
                                            'resolved' => ['#E0F2FE', '#0369A1'],
                                            'locked'   => ['#F3F4F6', '#4B5563'],
                                        ];
                                        $c = $colors[$status] ?? ['#F9FAFB', '#6B7280'];
                                    @endphp
                                    <span style="background: {{ $c[0] }}; color: {{ $c[1] }}; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid {{ $c[0] }};">
                                        {{ ucfirst($status) }}
                                    </span>
                                    @if($thread->is_pinned ?? false)
                                        <span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid #FDE68A;">
                                            <i class="fa fa-thumbtack me-1"></i>Pinned
                                        </span>
                                    @endif
                                    @if($thread->category)
                                    <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700;">
                                        {{ $thread->category->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 12px;">
                                <small style="color: var(--agri-text-muted); font-size: 12px; font-weight: 600;">{{ $thread->created_at->format('d M Y, H:i') }}</small>
                                <a href="{{ route('forum') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; font-size: 12px; font-weight: 600; padding: 6px 12px;">
                                    <i class="fas fa-arrow-left me-1"></i> Forum
                                </a>
                            </div>
                        </div>
                        <div style="padding: 28px;">
                            <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.2);">
                                    {{ strtoupper(substr($thread->user->name ?? 'F', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ $thread->user->name ?? 'Farmer' }}</div>
                                    <div style="color: var(--agri-text-muted); font-size: 12px; margin-top: 2px;">Author</div>
                                </div>
                            </div>
                            <div class="thread-content" style="margin: 0; color: var(--agri-text-main); font-size: 15px; line-height: 1.7;">
                                {!! nl2br(e($thread->body)) !!}
                            </div>
                        </div>
                    </div>

                    {{-- Replies Section --}}
                    <div class="card-agri" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
                        <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Replies ({{ $replies->total() }})</h6>
                        </div>
                        <div>
                            @forelse($replies as $reply)
                            <div style="padding: 24px; border-bottom: 1px solid var(--agri-border); {{ $reply->is_official ? 'background: rgba(16, 185, 129, 0.03); border-left: 3px solid var(--agri-primary);' : '' }}">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                    <div style="display: flex; gap: 16px; align-items: center;">
                                        <div style="width: 40px; height: 40px; border-radius: 12px; background: {{ $reply->is_expert_reply ? '#D97706' : ( $reply->user_id === $thread->user_id ? 'rgba(16, 185, 129, 0.1)' : '#F3F4F6' ) }}; color: {{ $reply->is_expert_reply ? 'white' : ( $reply->user_id === $thread->user_id ? 'var(--agri-primary)' : '#4B5563' ) }}; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; flex-shrink: 0; border: 1px solid {{ $reply->is_expert_reply ? 'transparent' : ( $reply->user_id === $thread->user_id ? 'rgba(16, 185, 129, 0.2)' : '#E5E7EB' ) }};">
                                            {{ strtoupper(substr(optional($reply->user)->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px;">{{ optional($reply->user)->name ?? 'Farmer' }}</span>
                                                @if($reply->is_official)
                                                    <span style="background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 800;"><i class="fa fa-check-circle me-1"></i>Official</span>
                                                @endif
                                                @if($reply->is_expert_reply)
                                                    <span style="background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 800;"><i class="fa fa-star me-1"></i>Expert</span>
                                                @endif
                                                @if($reply->user_id === $thread->user_id)
                                                    <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 700; border: 1px solid var(--agri-border);">Author</span>
                                                @endif
                                            </div>
                                            <div style="color: var(--agri-text-muted); font-size: 12px; margin-top: 4px;">{{ $reply->created_at->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    @auth
                                    @can('flag', $reply)
                                    <div>
                                        <form method="POST" action="{{ route('forum.reply.flag', $reply->id) }}" onsubmit="return confirm('Report this reply?')">
                                            @csrf
                                            <input type="hidden" name="reason" value="Inappropriate.">
                                            <button type="submit" class="btn-agri" style="padding: 6px 10px; background: transparent; color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600;" title="Report">
                                                <i class="fa fa-flag"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endcan

                                    @can('update', $reply)
                                    <button
                                        type="button"
                                        class="btn-agri"
                                        style="padding: 6px 10px; background: white; color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600;"
                                        title="Edit"
                                        onclick="(function(){var el=document.getElementById('reply-edit-{{ $reply->id }}'); if(el){el.style.display=(el.style.display==='none'||el.style.display==='')?'block':'none';}})()"
                                    >
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    @endcan

                                    @can('delete', $reply)
                                    <form method="POST" action="{{ route('forum.reply.destroy', $reply->id) }}" onsubmit="return confirm('Delete this reply?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 6px 10px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; font-weight: 600;" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @endauth
                                </div>
                                <div class="reply-content" style="margin: 0 0 0 56px; color: var(--agri-text-main); font-size: 14px; line-height: 1.6;">
                                    {!! nl2br(e($reply->body)) !!}
                                </div>

                                @can('update', $reply)
                                <div id="reply-edit-{{ $reply->id }}" style="display: none; margin: 16px 0 0 56px; padding: 14px; border: 1px solid var(--agri-border); border-radius: 12px; background: white;">
                                    <form method="POST" action="{{ route('forum.reply.edit', $reply->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <textarea name="body" class="form-agri" rows="3" required>{{ old('body', $reply->body) }}</textarea>
                                        <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 8px;">
                                            <button type="button" class="btn-agri btn-agri-outline" style="padding: 8px 14px;" onclick="(function(){var el=document.getElementById('reply-edit-{{ $reply->id }}'); if(el){el.style.display='none';}})()">Cancel</button>
                                            <button type="submit" class="btn-agri btn-agri-primary" style="padding: 8px 14px;">Save</button>
                                        </div>
                                    </form>
                                </div>
                                @endcan
                            </div>
                            @empty
                            <div style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border" style="width: 70px; height: 70px;">
                                    <i class="far fa-comments fs-3 text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">No responses yet</h6>
                                <p class="text-muted small fw-medium mb-0">Be the first to share your knowledge.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    @if($replies->hasPages())
                        <div class="d-flex justify-content-center mt-2 mb-4">
                            {{ $replies->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                    {{-- Reply form --}}
                    @auth
                        @if(($thread->status ?? 'open') !== 'locked')
                        <div class="card-agri" style="padding: 24px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                                <div style="width: 36px; height: 36px; background: rgba(16, 185, 129, 0.1); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-reply"></i></div>
                                <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Leave a Reply</h6>
                            </div>
                            
                            @if($errors->any())
                                <div class="alert mb-4" style="border-radius: 12px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 600; padding: 16px;">
                                    <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('forum.reply', $thread->id) }}">
                                @csrf
                                <div style="margin-bottom: 20px;">
                                    <textarea name="body" class="form-agri focus-primary" rows="4" placeholder="Share your experience, solution, or insight..." required>{{ old('body') }}</textarea>
                                </div>
                                <div style="display: flex; justify-content: flex-end;">
                                    <button class="btn-agri btn-agri-primary" style="font-weight: 700; padding: 10px 24px;">
                                        Post Reply <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        @else
                        <div style="padding: 40px 24px; text-align: center; border: 1px dashed var(--agri-border); background: var(--agri-bg); border-radius: 14px;">
                            <i class="fas fa-lock text-muted fs-3 mb-3"></i>
                            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Thread Locked</h5>
                            <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">This discussion is closed and no longer accepting replies.</p>
                        </div>
                        @endif
                    @else
                        <div style="padding: 40px 24px; text-align: center; border: 1px dashed var(--agri-border); background: white; border-radius: 14px;">
                            <i class="fas fa-user-lock text-muted fs-3 mb-3"></i>
                            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Join the Conversation</h5>
                            <p style="margin: 4px 0 16px 0; font-size: 13px; color: var(--agri-text-muted);">You must be logged in to reply to this thread.</p>
                            <a href="{{ route('login') }}" class="btn-agri btn-agri-primary" style="font-weight: 700; padding: 10px 24px; text-decoration: none;">Sign In</a>
                        </div>
                    @endauth

                </div>
            </div>
        </div>
    </div>
@endsection
