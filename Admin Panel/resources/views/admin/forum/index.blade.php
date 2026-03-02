@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Forum Moderation</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-comments text-success me-2"></i> Forum Moderation</h1>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Stats Row --}}
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa fa-list"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Total Threads</p>
                        <h3 style="margin: 4px 0 0 0; font-size: 24px; font-weight: 800; color: var(--agri-text-heading);">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Open</p>
                        <h3 style="margin: 4px 0 0 0; font-size: 24px; font-weight: 800; color: var(--agri-text-heading);">{{ $stats['open'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #F3F4F6; color: #4B5563; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa fa-lock"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Locked</p>
                        <h3 style="margin: 4px 0 0 0; font-size: 24px; font-weight: 800; color: var(--agri-text-heading);">{{ $stats['locked'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #DC2626;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa fa-flag"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Flagged</p>
                        <h3 style="margin: 4px 0 0 0; font-size: 24px; font-weight: 800; color: #DC2626;">{{ $stats['flags'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="mb-4 d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.forum.threads') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 10px 16px;">
                <i class="fa fa-list"></i> All Threads
            </a>
            <a href="{{ route('admin.forum.flags.index') }}" class="btn-agri" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 10px 16px; border-radius: 10px;">
                <i class="fa fa-flag"></i> Flag Reports
                @if($stats['flags'] > 0)<span style="background: #DC2626; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px;">{{ $stats['flags'] }}</span>@endif
            </a>
            <a href="{{ route('admin.forum.threads') }}?is_approved=0" class="btn-agri" style="background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 10px 16px; border-radius: 10px;">
                <i class="fa fa-clock-o"></i> Pending Approval
                @if($stats['pending'] > 0)<span style="background: #D97706; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px;">{{ $stats['pending'] }}</span>@endif
            </a>
            <a href="{{ route('admin.forum.categories.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 10px 16px;">
                <i class="fa fa-tags"></i> Manage Categories
            </a>
            <a href="{{ route('admin.forum.audit-log') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 10px 16px;">
                <i class="fa fa-history"></i> Audit Log
            </a>
        </div>

        <div class="row g-4">

            {{-- Pending Approval --}}
            <div class="col-lg-6">
                <div class="card-agri h-100" style="padding: 0; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <h6 style="margin: 0; font-weight: 800; color: #D97706; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;"><i class="fa fa-clock-o"></i>Pending Approval</h6>
                        <span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $stats['pending'] }}</span>
                    </div>
                    <div style="padding: 0;">
                        @if($pendingThreads->isEmpty())
                            <div style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                                <i class="fa fa-check-circle" style="font-size: 32px; color: #10B981; margin-bottom: 12px; display: block;"></i>
                                <span style="font-weight: 600;">No threads awaiting approval.</span>
                            </div>
                        @else
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                @foreach($pendingThreads->take(10) as $thread)
                                <li style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                                        <div style="min-width: 0; flex: 1;">
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}"
                                               style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; margin-bottom: 4px;">
                                                {{ $thread->title }}
                                            </a>
                                            <span style="font-size: 12px; color: var(--agri-text-muted);">
                                                {{ optional($thread->user)->name ?? '—' }} &middot; {{ $thread->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                            <form method="POST" action="{{ route('admin.forum.threads.approve', $thread->id) }}">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="padding: 6px 10px; background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;" title="Approve"><i class="fa fa-check"></i></button>
                                            </form>
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri btn-agri-primary" style="padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center;"><i class="fa fa-eye"></i></a>
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
                <div class="card-agri h-100" style="padding: 0; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <h6 style="margin: 0; font-weight: 800; color: #DC2626; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;"><i class="fa fa-flag"></i>Recent Flag Reports</h6>
                        <span style="background: #FEE2E2; color: #DC2626; padding: 4px 10px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $stats['flags'] }}</span>
                    </div>
                    <div style="padding: 0;">
                        @if($recentFlags->isEmpty())
                            <div style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                                <i class="fa fa-check-circle" style="font-size: 32px; color: #10B981; margin-bottom: 12px; display: block;"></i>
                                <span style="font-weight: 600;">No pending flag reports.</span>
                            </div>
                        @else
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                @foreach($recentFlags->take(10) as $flag)
                                @php $flagThread = $flag->reply?->thread; @endphp
                                <li style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                                        <div style="min-width: 0; flex: 1;">
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                                <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">{{ $flag->reason }}</span>
                                                <span style="font-size: 12px; color: var(--agri-text-muted);">by {{ optional($flag->reporter)->name ?? '—' }}</span>
                                            </div>
                                            @if($flagThread)
                                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <a href="{{ route('admin.forum.threads.show', $flagThread->id) }}" style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); text-decoration: none;">{{ Str::limit($flagThread->title, 50) }}</a>
                                            </div>
                                            @endif
                                        </div>
                                        <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                            <form method="POST" action="{{ route('admin.forum.flags.confirm', $flag->id) }}">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="padding: 6px 10px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;" title="Confirm"><i class="fa fa-check"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.forum.flags.dismiss', $flag->id) }}">
                                                @csrf
                                                <button type="submit" class="btn-agri" style="padding: 6px 10px; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;" title="Dismiss"><i class="fa fa-times"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @if($recentFlags->count() >= 10)
                            <div style="padding: 16px 24px; border-top: 1px solid var(--agri-border); text-align: center; background: var(--agri-bg);">
                                <a href="{{ route('admin.forum.flags.index') }}" style="font-size: 13px; font-weight: 700; color: var(--agri-primary); text-decoration: none;">View all flag reports <i class="fas fa-arrow-right" style="margin-left: 4px; font-size: 11px;"></i></a>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
