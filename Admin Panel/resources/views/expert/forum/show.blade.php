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

@if(session('success'))
    <div class="card-agri mb-4" style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 12px 20px; color: #166534; font-weight: 700;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="card-agri mb-4" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px; color: #991b1b; font-weight: 700;">
        {{ $errors->first('error') ?? $errors->first('body') ?? 'Please correct the highlighted issues and try again.' }}
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-8 d-flex flex-column gap-4">
        {{-- Thread Original Post --}}
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

        {{-- Replies --}}
        <div class="card-agri" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
            <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Replies ({{ $replies->total() }})</h6>
            </div>
            <div>
                @forelse($replies as $reply)
                <div style="padding: 24px; border-bottom: 1px solid var(--agri-border); {{ $reply->is_expert_reply ? 'background: rgba(16, 185, 129, 0.03); border-left: 3px solid var(--agri-primary);' : '' }}">
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
                        @if(!$reply->is_official && $reply->is_expert_reply && $reply->user_id === auth('expert')->id())
                        <div>
                            <form method="POST" action="{{ route('expert.forum.replies.official', $reply) }}" onsubmit="return confirm('Mark this as the official answer?');">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-agri" style="padding: 6px 12px; background: white; color: #065F46; border: 1px solid #D1FAE5; font-size: 12px; font-weight: 700; border-radius: 100px;"><i class="fas fa-check-circle me-1"></i> Mark Official</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    <div class="reply-content" style="margin: 0 0 0 56px; color: var(--agri-text-main); font-size: 14px; line-height: 1.6;">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
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
            
            @if($replies->hasPages())
            <div style="padding: 20px; border-top: 1px solid var(--agri-border);">
                {{ $replies->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>

        {{-- Reply Form --}}
        @if(!$thread->isLocked())
        <div class="card-agri" style="padding: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                <div style="width: 36px; height: 36px; background: rgba(16, 185, 129, 0.1); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-reply"></i></div>
                <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Post Expert Answer</h6>
            </div>
            
            <form method="POST" action="{{ route('expert.forum.reply', $thread) }}">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Detailed Response <span style="color: #DC2626;">*</span></label>
                    <textarea name="body" rows="6" class="form-agri focus-primary" placeholder="Provide your professional diagnosis or advice here (minimum 20 characters)..." required minlength="20">{{ old('body') }}</textarea>
                    @error('body')<div style="color: #DC2626; font-size: 12px; margin-top: 6px; font-weight: 600;"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Structured Recommendation <span style="background: rgba(107, 114, 128, 0.1); color: var(--agri-text-muted); padding: 2px 8px; border-radius: 100px; font-size: 10px; margin-left: 8px;">Optional</span></label>
                    <textarea name="recommendation" rows="3" class="form-agri" placeholder="If applicable, provide a clear, step-by-step action plan..."></textarea>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid var(--agri-border);">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: var(--agri-primary);"><i class="fas fa-shield-alt"></i> Posting as Verified Expert</div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="font-weight: 700; padding: 10px 24px;">Submit Response <i class="fas fa-paper-plane ms-2"></i></button>
                </div>
            </form>
        </div>
        @else
        <div style="padding: 40px 24px; text-align: center; border: 1px dashed var(--agri-border); background: var(--agri-bg); border-radius: 14px;">
            <i class="fas fa-lock text-muted fs-3 mb-3"></i>
            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Discussion Closed</h5>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">This thread has been locked and is no longer accepting new replies.</p>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card-agri" style="padding: 0; position: sticky; top: 100px;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: var(--agri-bg);">
                <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Thread Details</h6>
            </div>
            <div style="padding: 24px;">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                    <li style="display: flex; flex-direction: column; gap: 4px; padding-bottom: 16px; border-bottom: 1px solid var(--agri-border);">
                        <span style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Topic Category</span>
                        <div style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-tags" style="color: var(--agri-primary); opacity: 0.7;"></i>
                            {{ $thread->category?->name ?? 'General' }}
                        </div>
                    </li>
                    <li style="display: flex; flex-direction: column; gap: 4px; padding-bottom: 16px; border-bottom: 1px solid var(--agri-border);">
                        <span style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Original Author</span>
                        <div style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-user-circle" style="color: var(--agri-primary); opacity: 0.7;"></i>
                            {{ $thread->user->name }}
                        </div>
                    </li>
                    <li style="display: flex; flex-direction: column; gap: 4px; padding-bottom: 16px; border-bottom: 1px solid var(--agri-border);">
                        <span style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Creation Date</span>
                        <div style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); display: flex; align-items: center; gap: 8px;">
                            <i class="far fa-calendar-alt" style="color: var(--agri-primary); opacity: 0.7;"></i>
                            {{ $thread->created_at->format('d M Y, H:i') }}
                        </div>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid var(--agri-border);">
                        <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                            <i class="far fa-eye" style="color: var(--agri-primary); font-size: 14px;"></i> Total Views
                        </span>
                        <span style="background: var(--agri-bg); color: var(--agri-text-heading); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800; border: 1px solid var(--agri-border);">{{ $thread->views }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                            <i class="far fa-comments" style="color: #059669; font-size: 14px;"></i> Total Replies
                        </span>
                        <span style="background: #D1FAE5; color: #065F46; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800; border: 1px solid #A7F3D0;">{{ $replies->total() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
