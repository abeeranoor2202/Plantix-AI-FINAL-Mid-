@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-flag text-danger me-2"></i> Flagged Replies</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.forum.index') }}">Forum</a></li>
                <li class="breadcrumb-item active">Flags</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success rounded-3 border-0 shadow-sm mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm rounded-pill border-0 bg-light">
                            <option value="">All Statuses</option>
                            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed"  {{ request('status') === 'reviewed'  ? 'selected' : '' }}>Reviewed</option>
                            <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">Filter</button>
                        <a href="{{ route('admin.forum.flags.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Reset</a>
                    </div>
                    <div class="col-md-5 text-end">
                        <a href="{{ route('admin.forum.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Back to Forum
                        </a>
                        <a href="{{ route('admin.forum.audit-log') }}" class="btn btn-outline-secondary btn-sm rounded-pill ms-2">
                            <i class="fa fa-history me-1"></i> Audit Log
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Flags Table --}}
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa fa-flag me-2 text-danger"></i>Flag Reports</h5>
                <span class="text-muted small">{{ $flags->total() }} total</span>
            </div>
            <div class="card-body p-0">
                @if($flags->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-check-circle fa-3x text-success mb-3 d-block"></i>
                        No flag reports found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Flagged Reply</th>
                                    <th>Thread</th>
                                    <th>Reporter</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Flagged At</th>
                                    <th>Reviewed By</th>
                                    <th style="min-width:160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($flags as $flag)
                                @php
                                    $reply  = $flag->reply;
                                    $thread = $reply?->thread;
                                    $statusColors = [
                                        'pending'   => 'warning',
                                        'reviewed'  => 'success',
                                        'dismissed' => 'secondary',
                                    ];
                                    $sc = $statusColors[$flag->status] ?? 'light';
                                @endphp
                                <tr>
                                    <td class="text-muted small">{{ $flag->id }}</td>
                                    <td style="max-width:220px;">
                                        @if($reply)
                                            <p class="mb-0 small text-truncate text-dark" title="{{ strip_tags($reply->body) }}">
                                                {{ Str::limit(strip_tags($reply->body), 80) }}
                                            </p>
                                            <span class="text-muted smaller">by {{ optional($reply->user)->name ?? '—' }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">Reply deleted</span>
                                        @endif
                                    </td>
                                    <td style="max-width:200px;">
                                        @if($thread)
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}"
                                               class="text-dark fw-semibold text-decoration-none small"
                                               title="{{ $thread->title }}">
                                                {{ Str::limit($thread->title, 55) }}
                                            </a>
                                        @else
                                            <span class="text-muted small fst-italic">Thread deleted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold small">{{ optional($flag->reporter)->name ?? '—' }}</span>
                                        @if($flag->reporter?->email)
                                            <br><span class="text-muted" style="font-size:11px;">{{ $flag->reporter->email }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border" title="{{ $flag->reason }}">
                                            {{ Str::limit($flag->reason, 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sc }}">{{ ucfirst($flag->status) }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $flag->created_at->format('d M Y') }}<br>{{ $flag->created_at->format('H:i') }}</td>
                                    <td class="text-muted small">
                                        @if($flag->reviewer)
                                            {{ $flag->reviewer->name }}
                                            @if($flag->reviewed_at)
                                                <br><span style="font-size:11px;">{{ $flag->reviewed_at->format('d M Y') }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($flag->status === 'pending')
                                            {{-- Confirm: keep reply flagged, mark reviewed --}}
                                            <form method="POST" action="{{ route('admin.forum.flags.confirm', $flag->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger me-1"
                                                        title="Confirm flag — reply stays flagged">
                                                    <i class="fa fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            {{-- Dismiss: restore reply to visible --}}
                                            <form method="POST" action="{{ route('admin.forum.flags.dismiss', $flag->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-secondary"
                                                        title="Dismiss — reply restored to visible">
                                                    <i class="fa fa-times"></i> Dismiss
                                                </button>
                                            </form>
                                        @elseif($flag->status === 'reviewed')
                                            <span class="text-success small"><i class="fa fa-check-circle me-1"></i>Confirmed</span>
                                        @else
                                            <span class="text-muted small"><i class="fa fa-ban me-1"></i>Dismissed</span>
                                        @endif

                                        @if($thread)
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}"
                                               class="btn btn-xs btn-outline-primary mt-1 d-block">
                                                <i class="fa fa-eye"></i> View Thread
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3">
                        {{ $flags->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Legend --}}
        <div class="mt-3 small text-muted">
            <span class="badge bg-warning me-1">Pending</span> Awaiting admin review &nbsp;|&nbsp;
            <span class="badge bg-success me-1">Reviewed</span> Flag confirmed, reply remains flagged &nbsp;|&nbsp;
            <span class="badge bg-secondary me-1">Dismissed</span> Flag dismissed, reply restored to visible
        </div>

    </div>
</div>
@endsection
