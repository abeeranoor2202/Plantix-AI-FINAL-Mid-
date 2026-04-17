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
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Threads</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-comments text-success me-2"></i> Forum Threads</h1>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Filters --}}
        <div class="card-agri mb-4" style="padding: 24px;">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Search</label>
                    <div style="position: relative;">
                        <i class="fa fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                        <input type="text" name="search" class="form-agri" style="padding-left: 40px;" placeholder="Title or content…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
                    <select name="status" class="form-agri">
                        <option value="">All Statuses</option>
                        <option value="open"     {{ request('status') === 'open'     ? 'selected' : '' }}>Open</option>
                        <option value="locked"   {{ request('status') === 'locked'   ? 'selected' : '' }}>Locked</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Category</label>
                    <select name="forum_category_id" class="form-agri">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('forum_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Approval</label>
                    <select name="approved" class="form-agri">
                        <option value="">All</option>
                        <option value="1" @selected(request('approved') === '1')>Approved</option>
                        <option value="0" @selected(request('approved') === '0')>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Sort</label>
                    <select name="sort_by" class="form-agri">
                        <option value="latest" @selected(request('sort_by') === 'latest')>Latest</option>
                        <option value="popular" @selected(request('sort_by') === 'popular')>Most Replies</option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>Oldest</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">From</label>
                    <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">To</label>
                    <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary w-50" style="justify-content: center;">Filter</button>
                    <a href="{{ route('admin.forum.threads') }}" class="btn-agri btn-agri-outline w-50" style="justify-content: center; text-decoration: none;">Reset</a>
                </div>
            </form>
        </div>

        {{-- Threads Table --}}
        <div class="card-agri" style="padding: 0; overflow: visible;">
            <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-comments"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">All Threads</h6>
                </div>
                <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $threads->total() }} total</span>
            </div>
            <div class="table-responsive">
                @if($threads->isEmpty())
                    <div style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fa fa-comments" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                        <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No threads found.</p>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">Try adjusting your filters.</p>
                    </div>
                @else
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; width: 60px;">#</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Title</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Author</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Category</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Replies</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Pinned</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Created</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end; min-width: 280px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($threads as $thread)
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 18px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">{{ $thread->id }}</td>
                                <td style="padding: 18px 24px;">
                                    <a href="{{ route('admin.forum.threads.show', $thread->id) }}" style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading); text-decoration: none; display: block; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ Str::limit($thread->title, 55) }}
                                    </a>
                                </td>
                                <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-main);">{{ optional($thread->user)->name ?? '—' }}</td>
                                <td style="padding: 18px 24px;">
                                    <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">{{ optional($thread->category)->name ?? '—' }}</span>
                                </td>
                                <td style="padding: 18px 24px; text-align: center; font-size: 14px; font-weight: 800; color: var(--agri-primary-dark);">{{ $thread->replies_count }}</td>
                                <td style="padding: 18px 24px;">
                                    <x-platform.status-badge domain="forum" :status="$thread->status" />
                                </td>
                                <td style="padding: 18px 24px; text-align: center;">
                                    @if($thread->is_pinned)
                                        <i class="fa fa-thumbtack" style="color: #D97706;" title="Pinned"></i>
                                    @else
                                        <span style="color: var(--agri-text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $thread->created_at->format('d M Y') }}</td>
                                <td style="padding: 18px 24px; text-align: end; min-width: 280px;">
                                    <div style="display: inline-flex; justify-content: flex-end; align-items: center; gap: 8px; flex-wrap: nowrap; white-space: nowrap;">
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri btn-agri-primary" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; text-decoration: none; border-radius: 999px;">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        {{-- Moderation actions --}}
                                        @if($thread->status === 'locked')
                                            <form method="POST" action="{{ route('admin.forum.threads.unlock', $thread->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600; border-radius: 999px;" title="Unlock">
                                                    <i class="fa fa-unlock"></i>
                                                </button>
                                            </form>
                                        @elseif($thread->status === 'archived')
                                            <button type="button" class="btn-agri" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: #F3F4F6; color: #9CA3AF; border: 1px solid #E5E7EB; font-size: 12px; font-weight: 600; border-radius: 999px; cursor: default;" title="Archived" disabled>
                                                <i class="fa fa-lock"></i>
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.forum.threads.lock', $thread->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600; border-radius: 999px;" title="Lock">
                                                    <i class="fa fa-lock"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($thread->status !== 'archived')
                                            <form method="POST" action="{{ route('admin.forum.threads.archive', $thread->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600; border-radius: 999px;" title="Archive">
                                                    <i class="fa fa-archive"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.forum.threads.unarchive', $thread->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 12px; font-weight: 600; border-radius: 999px;" title="Unarchive">
                                                    <i class="fa fa-archive"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Pin toggle --}}
                                        <form method="POST" action="{{ route('admin.forum.threads.pin', $thread->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-agri btn-agri-outline" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; border-radius: 999px;" title="{{ $thread->is_pinned ? 'Unpin' : 'Pin' }}">
                                                <i class="fa fa-thumbtack"></i>
                                            </button>
                                        </form>
                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.forum.threads.destroy', $thread->id) }}" class="d-inline" onsubmit="return confirm('Delete thread permanently?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-agri btn-agri-danger" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; border-radius: 999px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if($threads->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $threads->links() }}
            </div>
            @endif
        </div>

    </div>

@endsection
