@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-comments text-success me-2"></i> Thread Review</h3>
        </div>
        <div class="col-md-6 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.forum.index') }}">Forum</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.forum.threads') }}">Threads</a></li>
                <li class="breadcrumb-item active">Review</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row g-4">

            {{-- Thread Details --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $thread->title }}</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                @php
                                    $colors = ['open'=>'success','closed'=>'secondary','flagged'=>'danger','pending'=>'warning'];
                                    $c = $colors[$thread->status] ?? 'light';
                                @endphp
                                <span class="badge bg-{{ $c }}">{{ ucfirst($thread->status) }}</span>
                                @if($thread->is_pinned)
                                    <span class="badge bg-warning text-dark"><i class="fa fa-thumbtack me-1"></i>Pinned</span>
                                @endif
                                <span class="badge bg-light text-dark border">{{ optional($thread->category)->name ?? 'Uncategorised' }}</span>
                            </div>
                        </div>
                        <small class="text-muted">{{ $thread->created_at->format('d M Y, H:i') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 mb-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;font-size:16px;flex-shrink:0;">
                                {{ strtoupper(substr(optional($thread->user)->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ optional($thread->user)->name ?? 'Unknown' }}</div>
                                <div class="text-muted small">{{ optional($thread->user)->email }}</div>
                            </div>
                        </div>
                        <p class="mb-0" style="white-space:pre-wrap;">{{ $thread->body }}</p>
                    </div>
                </div>

                {{-- Replies --}}
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0">Replies ({{ $thread->allReplies->count() }})</h6>
                    </div>
                    <div class="card-body p-0">
                        @forelse($thread->allReplies as $reply)
                        <div class="p-4 border-bottom {{ $reply->parent_id ? 'ms-5 bg-light' : '' }}" id="reply-{{ $reply->id }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="rounded-circle {{ $reply->is_expert_reply ? 'bg-warning' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(substr(optional($reply->user)->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-semibold">{{ optional($reply->user)->name ?? 'Unknown' }}</span>
                                        @if($reply->is_official)
                                            <span class="badge bg-success ms-1 small"><i class="fa fa-check-circle me-1"></i>Official Answer</span>
                                        @endif
                                        @if($reply->is_expert_reply)
                                            <span class="badge bg-warning text-dark ms-1 small"><i class="fa fa-star me-1"></i>Expert</span>
                                        @endif
                                        @if($reply->status === 'flagged')
                                            <span class="badge bg-danger ms-1 small"><i class="fa fa-flag me-1"></i>Flagged</span>
                                        @endif
                                        @if($reply->parent_id)
                                            <span class="badge bg-light text-muted border ms-1 small">Nested</span>
                                        @endif
                                        <div class="text-muted small">{{ $reply->created_at->format('d M Y, H:i') }}
                                            @if($reply->edited_at)<span class="ms-1 fst-italic">(edited)</span>@endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('admin.forum.replies.destroy', $reply->id) }}"
                                          onsubmit="return confirm('Delete this reply?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="mb-0 ms-5 ps-3" style="white-space:pre-wrap;">{{ strip_tags($reply->body) }}</p>
                        </div>
                        @empty
                        <div class="py-4 text-center text-muted">No replies yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Moderation Actions Panel --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0">Moderation Actions</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">

                        {{-- Approve --}}
                        @if(!$thread->is_approved)
                        <form method="POST" action="{{ route('admin.forum.threads.approve', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                <i class="fa fa-check me-1"></i> Approve Thread
                            </button>
                        </form>
                        @endif

                        {{-- Lock / Unlock --}}
                        @if($thread->status === 'locked')
                        <form method="POST" action="{{ route('admin.forum.threads.unlock', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                                <i class="fa fa-unlock me-1"></i> Unlock Thread
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.forum.threads.lock', $thread->id) }}">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="reason" class="form-control form-control-sm border-0 bg-light rounded-pill"
                                       placeholder="Lock reason (optional)">
                            </div>
                            <button type="submit" class="btn btn-outline-warning btn-sm w-100 rounded-pill text-dark">
                                <i class="fa fa-lock me-1"></i> Lock Thread
                            </button>
                        </form>
                        @endif

                        {{-- Archive --}}
                        @if($thread->status !== 'archived')
                        <form method="POST" action="{{ route('admin.forum.threads.archive', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                                <i class="fa fa-archive me-1"></i> Archive Thread
                            </button>
                        </form>
                        @endif

                        <hr>

                        {{-- Pin / Unpin --}}
                        <form method="POST" action="{{ route('admin.forum.threads.pin', $thread->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm w-100 rounded-pill text-dark">
                                <i class="fa fa-thumbtack me-1"></i>
                                {{ $thread->is_pinned ? 'Unpin Thread' : 'Pin Thread' }}
                            </button>
                        </form>

                        <hr>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.forum.threads.destroy', $thread->id) }}"
                              onsubmit="return confirm('Permanently delete this thread and all its replies?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                                <i class="fa fa-trash me-1"></i> Delete Thread
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Thread Meta --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0">Thread Info</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $statusColors = ['open'=>'success','locked'=>'warning','resolved'=>'info','archived'=>'secondary'];
                            $sc = $statusColors[$thread->status] ?? 'light';
                        @endphp
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted">ID</td><td class="fw-semibold">#{{ $thread->id }}</td></tr>
                            <tr><td class="text-muted">Slug</td><td class="text-break small">{{ $thread->slug }}</td></tr>
                            <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $sc }}">{{ ucfirst($thread->status) }}</span></td></tr>
                            <tr><td class="text-muted">Approved</td><td>{{ $thread->is_approved ? 'Yes' : 'No' }}</td></tr>
                            <tr><td class="text-muted">Author</td><td>{{ optional($thread->user)->name }}</td></tr>
                            <tr><td class="text-muted">Email</td><td class="text-break small">{{ optional($thread->user)->email }}</td></tr>
                            <tr><td class="text-muted">Category</td><td>{{ optional($thread->category)->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Replies</td><td>{{ $thread->replies_count }}</td></tr>
                            <tr><td class="text-muted">Views</td><td>{{ $thread->views }}</td></tr>
                            <tr><td class="text-muted">Created</td><td>{{ $thread->created_at->format('d M Y') }}</td></tr>
                            <tr><td class="text-muted">Updated</td><td>{{ $thread->updated_at->diffForHumans() }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Audit Log --}}
                @if($logs->isNotEmpty())
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0"><i class="fa fa-history me-2 text-muted"></i>Audit Log</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($logs as $log)
                            <li class="list-group-item px-4 py-2">
                                <div class="d-flex gap-2 align-items-start">
                                    <span class="badge bg-light text-dark border" style="font-size:10px;white-space:nowrap;">{{ $log->action }}</span>
                                    <div>
                                        <span class="small fw-semibold">{{ optional($log->user)->name ?? '#'.$log->user_id }}</span>
                                        <span class="text-muted small ms-1">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        <div class="px-4 py-2 text-center border-top">
                            <a href="{{ route('admin.forum.audit-log') }}?action=&user_id=" class="small text-primary">Full audit log →</a>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
