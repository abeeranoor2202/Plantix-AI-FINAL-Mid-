@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-comments text-success me-2"></i> Forum Moderation</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Forum</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Stats Row --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-2"><i class="fa fa-list fa-2x text-primary"></i></div>
                        <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total Threads</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-2"><i class="fa fa-check-circle fa-2x text-success"></i></div>
                        <h3 class="fw-bold mb-0">{{ $stats['open'] }}</h3>
                        <small class="text-muted">Open</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-2"><i class="fa fa-lock fa-2x text-secondary"></i></div>
                        <h3 class="fw-bold mb-0">{{ $stats['locked'] }}</h3>
                        <small class="text-muted">Locked</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius:16px; border-left:4px solid #f44 !important;">
                    <div class="card-body text-center py-4">
                        <div class="mb-2"><i class="fa fa-flag fa-2x text-danger"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ $stats['flags'] }}</h3>
                        <small class="text-muted">Flagged</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="mb-4 d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.forum.threads') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-list me-1"></i> All Threads
            </a>
            <a href="{{ route('admin.forum.flags.index') }}" class="btn btn-outline-danger btn-sm">
                <i class="fa fa-flag me-1"></i> Flag Reports
                @if($stats['flags'] > 0)<span class="badge bg-danger ms-1">{{ $stats['flags'] }}</span>@endif
            </a>
            <a href="{{ route('admin.forum.threads') }}?is_approved=0" class="btn btn-outline-warning btn-sm">
                <i class="fa fa-clock-o me-1"></i> Pending Approval
                @if($stats['pending'] > 0)<span class="badge bg-warning text-dark ms-1">{{ $stats['pending'] }}</span>@endif
            </a>
            <a href="{{ route('admin.forum.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-tags me-1"></i> Manage Categories
            </a>
            <a href="{{ route('admin.forum.audit-log') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-history me-1"></i> Audit Log
            </a>
        </div>

        <div class="row g-4">

            {{-- Pending Approval --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-warning"><i class="fa fa-clock-o me-2"></i>Pending Approval</h6>
                        <span class="badge bg-warning text-dark">{{ $stats['pending'] }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($pendingThreads->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa fa-check-circle fa-2x text-success mb-2 d-block"></i>No threads awaiting approval.
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($pendingThreads->take(10) as $thread)
                                <li class="list-group-item px-4 py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div style="min-width:0;">
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}"
                                               class="fw-semibold text-dark text-decoration-none small d-block text-truncate">
                                                {{ $thread->title }}
                                            </a>
                                            <span class="text-muted" style="font-size:11px;">
                                                {{ optional($thread->user)->name ?? '—' }} &middot; {{ $thread->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <form method="POST" action="{{ route('admin.forum.threads.approve', $thread->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-success" title="Approve"><i class="fa fa-check"></i></button>
                                            </form>
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn btn-xs btn-outline-primary"><i class="fa fa-eye"></i></a>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Flag Reports --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fa fa-flag me-2"></i>Recent Flag Reports</h6>
                        <span class="badge bg-danger">{{ $stats['flags'] }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($recentFlags->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa fa-check-circle fa-2x text-success mb-2 d-block"></i>No pending flag reports.
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($recentFlags->take(10) as $flag)
                                @php $flagThread = $flag->reply?->thread; @endphp
                                <li class="list-group-item px-4 py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div style="min-width:0;">
                                            <span class="badge bg-light text-dark border small me-1">{{ $flag->reason }}</span>
                                            <span class="text-muted small">by {{ optional($flag->reporter)->name ?? '—' }}</span>
                                            @if($flagThread)
                                            <div class="text-truncate small mt-1">
                                                <a href="{{ route('admin.forum.threads.show', $flagThread->id) }}" class="text-dark text-decoration-none">{{ Str::limit($flagThread->title, 50) }}</a>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <form method="POST" action="{{ route('admin.forum.flags.confirm', $flag->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Confirm"><i class="fa fa-check"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.forum.flags.dismiss', $flag->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-secondary" title="Dismiss"><i class="fa fa-times"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @if($recentFlags->count() >= 10)
                            <div class="px-4 py-2 border-top text-center">
                                <a href="{{ route('admin.forum.flags.index') }}" class="small text-primary">View all flag reports…</a>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
