@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Forum
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.threads') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Threads
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Review</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-comments text-success me-2"></i> Thread Review</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row g-4">

            {{-- Thread Details --}}
            <div class="col-lg-8">
                <div class="card-agri mb-4" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: flex-start; justify-content: space-between;">
                        <div>
                            <h5 style="margin-bottom: 12px; font-weight: 800; color: var(--agri-text-heading); font-size: 18px;">{{ $thread->title }}</h5>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @php
                                    $colors = [
                                        'open'     => ['#D1FAE5', '#065F46'],
                                        'closed'   => ['#F3F4F6', '#4B5563'],
                                        'flagged'  => ['#FEE2E2', '#991B1B'],
                                        'pending'  => ['#FEF3C7', '#92400E'],
                                    ];
                                    $c = $colors[$thread->status] ?? ['#F9FAFB', '#6B7280'];
                                @endphp
                                <span style="background: {{ $c[0] }}; color: {{ $c[1] }}; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid {{ $c[0] }};">
                                    {{ ucfirst($thread->status) }}
                                </span>
                                @if($thread->is_pinned)
                                    <span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid #FDE68A;">
                                        <i class="fa fa-thumbtack me-1"></i>Pinned
                                    </span>
                                @endif
                                <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700;">
                                    {{ optional($thread->category)->name ?? 'Uncategorised' }}
                                </span>
                            </div>
                        </div>
                        <small style="color: var(--agri-text-muted); font-size: 12px; font-weight: 600;">{{ $thread->created_at->format('d M Y, H:i') }}</small>
                    </div>
                    <div style="padding: 28px;">
                        <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                            <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--agri-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                                {{ strtoupper(substr(optional($thread->user)->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ optional($thread->user)->name ?? 'Unknown' }}</div>
                                <div style="color: var(--agri-text-muted); font-size: 13px;">{{ optional($thread->user)->email }}</div>
                            </div>
                        </div>
                        <p style="margin: 0; white-space: pre-wrap; color: var(--agri-text-main); font-size: 15px; line-height: 1.6;">{{ $thread->body }}</p>
                    </div>
                </div>

                {{-- Replies --}}
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Replies ({{ $thread->allReplies->count() }})</h6>
                    </div>
                    <div>
                        @forelse($thread->allReplies as $reply)
                        <div style="padding: 24px; border-bottom: 1px solid var(--agri-border); {{ $reply->parent_id ? 'margin-left: 48px; background: var(--agri-bg); border-left: 2px solid var(--agri-primary-light);' : '' }}" id="reply-{{ $reply->id }}">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                <div style="display: flex; gap: 16px; align-items: center;">
                                    <div style="width: 40px; height: 40px; border-radius: 12px; background: {{ $reply->is_expert_reply ? '#D97706' : '#6B7280' }}; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 10px {{ $reply->is_expert_reply ? 'rgba(217, 119, 6, 0.2)' : 'rgba(107, 114, 128, 0.2)' }};">
                                        {{ strtoupper(substr(optional($reply->user)->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px;">{{ optional($reply->user)->name ?? 'Unknown' }}</span>
                                            @if($reply->is_official)
                                                <span style="background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 800;"><i class="fa fa-check-circle me-1"></i>Official Answer</span>
                                            @endif
                                            @if($reply->is_expert_reply)
                                                <span style="background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 800;"><i class="fa fa-star me-1"></i>Expert</span>
                                            @endif
                                            @if($reply->status === 'flagged')
                                                <span style="background: #FEE2E2; color: #991B1B; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 800;"><i class="fa fa-flag me-1"></i>Flagged</span>
                                            @endif
                                            @if($reply->parent_id)
                                                <span style="background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 700;">Nested</span>
                                            @endif
                                        </div>
                                        <div style="color: var(--agri-text-muted); font-size: 12px; margin-top: 4px;">{{ $reply->created_at->format('d M Y, H:i') }}
                                            @if($reply->edited_at)<span style="margin-left: 4px; font-style: italic; opacity: 0.8;">(edited)</span>@endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <form method="POST" action="{{ route('admin.forum.replies.destroy', $reply->id) }}" onsubmit="return confirm('Delete this reply?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 6px 10px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; font-weight: 600;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p style="margin: 0 0 0 56px; white-space: pre-wrap; color: var(--agri-text-main); font-size: 14px; line-height: 1.6;">{{ strip_tags($reply->body) }}</p>
                        </div>
                        @empty
                        <div style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">No replies yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Moderation Actions Panel --}}
            <div class="col-lg-4">
                <div class="card-agri mb-4" style="padding: 24px;">
                    <h6 style="margin-bottom: 20px; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Moderation Actions</h6>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">

                        {{-- Approve --}}
                        @if(!$thread->is_approved)
                        <form method="POST" action="{{ route('admin.forum.threads.approve', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn-agri btn-agri-primary w-100" style="justify-content: center; font-weight: 700; padding: 12px;">
                                <i class="fa fa-check"></i> Approve Thread
                            </button>
                        </form>
                        @endif

                        {{-- Lock / Unlock --}}
                        @if($thread->status === 'locked')
                        <form method="POST" action="{{ route('admin.forum.threads.unlock', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn-agri btn-agri-outline w-100" style="justify-content: center; font-weight: 700; padding: 12px;">
                                <i class="fa fa-unlock"></i> Unlock Thread
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.forum.threads.lock', $thread->id) }}">
                            @csrf
                            <div style="margin-bottom: 8px;">
                                <input type="text" name="reason" class="form-agri" placeholder="Lock reason (optional)">
                            </div>
                            <button type="submit" class="btn-agri w-100" style="justify-content: center; font-weight: 700; padding: 12px; background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">
                                <i class="fa fa-lock"></i> Lock Thread
                            </button>
                        </form>
                        @endif

                        {{-- Archive --}}
                        @if($thread->status !== 'archived')
                        <form method="POST" action="{{ route('admin.forum.threads.archive', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn-agri btn-agri-outline w-100" style="justify-content: center; font-weight: 700; padding: 12px; color: var(--agri-text-muted);">
                                <i class="fa fa-archive"></i> Archive Thread
                            </button>
                        </form>
                        @endif

                        <div style="height: 1px; background: var(--agri-border); margin: 8px 0;"></div>

                        {{-- Pin / Unpin --}}
                        <form method="POST" action="{{ route('admin.forum.threads.pin', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn-agri w-100" style="justify-content: center; font-weight: 700; padding: 12px; background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A;">
                                <i class="fa fa-thumbtack"></i>
                                {{ $thread->is_pinned ? 'Unpin Thread' : 'Pin Thread' }}
                            </button>
                        </form>

                        <div style="height: 1px; background: var(--agri-border); margin: 8px 0;"></div>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.forum.threads.destroy', $thread->id) }}" onsubmit="return confirm('Permanently delete this thread and all its replies?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-agri w-100" style="justify-content: center; font-weight: 700; padding: 12px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
                                <i class="fa fa-trash"></i> Delete Thread
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Thread Meta --}}
                <div class="card-agri mb-4" style="padding: 24px;">
                    <h6 style="margin-bottom: 20px; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Thread Info</h6>
                    @php
                        $statusColors = [
                            'open'     => ['#D1FAE5', '#065F46'],
                            'locked'   => ['#FEF3C7', '#92400E'],
                            'resolved' => ['#E0F2FE', '#0369A1'],
                            'archived' => ['#F3F4F6', '#4B5563'],
                        ];
                        $sc = $statusColors[$thread->status] ?? ['#F9FAFB', '#6B7280'];
                    @endphp
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">ID</strong> <span style="font-weight: 700; color: var(--agri-text-heading);">#{{ $thread->id }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Slug</strong> <span style="color: var(--agri-text-main); word-break: break-all; max-width: 60%; text-align: right;">{{ $thread->slug }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Status</strong> <span style="background: {{ $sc[0] }}; color: {{ $sc[1] }}; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid {{ $sc[0] }};">{{ ucfirst($thread->status) }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Approved</strong> <span>{{ $thread->is_approved ? 'Yes' : 'No' }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Author</strong> <span>{{ optional($thread->user)->name }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Email</strong> <span style="word-break: break-all; max-width: 60%; text-align: right;">{{ optional($thread->user)->email }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Category</strong> <span>{{ optional($thread->category)->name ?? '—' }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Replies</strong> <span style="font-weight: 800; color: var(--agri-primary-dark);">{{ $thread->replies_count }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Views</strong> <span>{{ $thread->views }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Created</strong> <span>{{ $thread->created_at->format('d M Y') }}</span></div>
                        <div style="display: flex; justify-content: space-between;"><strong style="color: var(--agri-text-muted);">Updated</strong> <span>{{ $thread->updated_at->diffForHumans() }}</span></div>
                    </div>
                </div>

                {{-- Audit Log --}}
                @if($logs->isNotEmpty())
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: var(--agri-bg); color: var(--agri-text-muted); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-history"></i></div>
                            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Audit Log</h6>
                        </div>
                    </div>
                    <div>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            @foreach($logs as $log)
                            <li style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">
                                <div style="display: flex; gap: 12px; align-items: flex-start;">
                                    <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; white-space: nowrap;">{{ $log->action }}</span>
                                    <div>
                                        <div style="font-weight: 700; color: var(--agri-text-main); font-size: 13px;">{{ optional($log->user)->name ?? '#'.$log->user_id }}</div>
                                        <div style="color: var(--agri-text-muted); font-size: 11px; margin-top: 2px;">{{ $log->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        <div style="padding: 16px 24px; text-align: center;">
                            <a href="{{ route('admin.forum.audit-log') }}?action=&user_id=" style="font-size: 13px; font-weight: 700; color: var(--agri-primary); text-decoration: none;">Full audit log <i class="fas fa-arrow-right" style="margin-left: 4px; font-size: 10px;"></i></a>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
@endsection
