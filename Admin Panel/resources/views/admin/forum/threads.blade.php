@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-comments text-success me-2"></i> Forum Threads</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.forum.index') }}">Forum</a></li>
                <li class="breadcrumb-item active">Threads</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill border-0 bg-light"
                               placeholder="Title or content…" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm rounded-pill border-0 bg-light">
                            <option value="">All Statuses</option>
                            <option value="open"     {{ request('status') === 'open'     ? 'selected' : '' }}>Open</option>
                            <option value="locked"   {{ request('status') === 'locked'   ? 'selected' : '' }}>Locked</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm rounded-pill border-0 bg-light">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm w-50 rounded-pill">Filter</button>
                        <a href="{{ route('admin.forum.threads') }}" class="btn btn-outline-secondary btn-sm w-50 rounded-pill">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Threads Table --}}
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">All Threads</h5>
                <span class="text-muted small">{{ $threads->total() }} total</span>
            </div>
            <div class="card-body p-0">
                @if($threads->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-comments fa-3x mb-3 d-block"></i>No threads found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Replies</th>
                                    <th>Status</th>
                                    <th>Pinned</th>
                                    <th>Created</th>
                                    <th style="min-width:160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($threads as $thread)
                                <tr>
                                    <td class="text-muted small">{{ $thread->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="text-dark fw-semibold text-decoration-none">
                                            {{ Str::limit($thread->title, 55) }}
                                        </a>
                                    </td>
                                    <td>{{ optional($thread->user)->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ optional($thread->category)->name ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">{{ $thread->replies_count }}</td>
                                    <td>
                                        @php
                                            $colors = ['open'=>'success','locked'=>'secondary','resolved'=>'info','archived'=>'dark'];
                                            $c = $colors[$thread->status] ?? 'light';
                                        @endphp
                                        <span class="badge bg-{{ $c }}">{{ ucfirst($thread->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($thread->is_pinned)
                                            <i class="fa fa-thumbtack text-warning" title="Pinned"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $thread->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn btn-xs btn-outline-primary me-1">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        {{-- Action drop-down --}}
                                        <div class="btn-group btn-group-sm me-1">
                                            <button type="button" class="btn btn-xs btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if(!$thread->is_approved)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.forum.threads.approve', $thread->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success"><i class="fa fa-check me-1"></i>Approve</button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($thread->status !== 'locked')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.forum.threads.lock', $thread->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item"><i class="fa fa-lock me-1"></i>Lock</button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($thread->status === 'locked')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.forum.threads.unlock', $thread->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item"><i class="fa fa-unlock me-1"></i>Unlock</button>
                                                    </form>
                                                </li>
                                                @endif
                                                @if($thread->status !== 'archived')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.forum.threads.archive', $thread->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-secondary"><i class="fa fa-archive me-1"></i>Archive</button>
                                                    </form>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                        {{-- Pin toggle --}}
                                        <form method="POST" action="{{ route('admin.forum.threads.pin', $thread->id) }}" class="d-inline me-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-warning" title="{{ $thread->is_pinned ? 'Unpin' : 'Pin' }}">
                                                <i class="fa fa-thumbtack"></i>
                                            </button>
                                        </form>
                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.forum.threads.destroy', $thread->id) }}" class="d-inline"
                                              onsubmit="return confirm('Delete thread permanently?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3">
                        {{ $threads->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
