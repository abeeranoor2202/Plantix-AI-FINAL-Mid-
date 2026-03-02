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
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Flags</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-flag text-danger me-2"></i> Flagged Replies</h1>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert mb-4" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-times-circle" style="font-size: 18px;"></i> {{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filter Bar --}}
        <div class="card-agri mb-4" style="padding: 24px;">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
                    <select name="status" class="form-agri">
                        <option value="">All Statuses</option>
                        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed"  {{ request('status') === 'reviewed'  ? 'selected' : '' }}>Reviewed</option>
                        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary" style="padding-left: 24px; padding-right: 24px;">Filter</button>
                    <a href="{{ route('admin.forum.flags.index') }}" class="btn-agri btn-agri-outline" style="padding-left: 24px; padding-right: 24px; text-decoration: none;">Reset</a>
                </div>
                <div class="col-md-5 text-end">
                    <a href="{{ route('admin.forum.index') }}" class="btn-agri btn-agri-outline" style="display: inline-flex; text-decoration: none;">
                        <i class="fa fa-arrow-left"></i> Back to Forum
                    </a>
                    <a href="{{ route('admin.forum.audit-log') }}" class="btn-agri" style="display: inline-flex; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); margin-left: 8px; text-decoration: none;">
                        <i class="fa fa-history"></i> Audit Log
                    </a>
                </div>
            </form>
        </div>

        {{-- Flags Table --}}
        <div class="card-agri" style="padding: 0; overflow: hidden;">
            <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: #FEE2E2; color: #DC2626; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-flag"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Flag Reports</h6>
                </div>
                <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $flags->total() }} total</span>
            </div>
            <div class="table-responsive">
                @if($flags->isEmpty())
                    <div style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fa fa-check-circle" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px; color: #10B981;"></i>
                        <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No flag reports found.</p>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">Great job keeping the forum clean!</p>
                    </div>
                @else
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; width: 60px;">#</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Flagged Reply</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Thread</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reporter</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reason</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Flagged / Reviewed</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end; min-width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flags as $flag)
                            @php
                                $reply  = $flag->reply;
                                $thread = $reply?->thread;
                                $statusColors = [
                                    'pending'   => ['#FEF3C7', '#92400E'],
                                    'reviewed'  => ['#D1FAE5', '#065F46'],
                                    'dismissed' => ['#F3F4F6', '#4B5563'],
                                ];
                                $sc = $statusColors[$flag->status] ?? ['#F9FAFB', '#6B7280'];
                            @endphp
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 18px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">{{ $flag->id }}</td>
                                <td style="padding: 18px 24px; max-width:260px;">
                                    @if($reply)
                                        <div style="font-size: 14px; color: var(--agri-text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 4px;" title="{{ strip_tags($reply->body) }}">
                                            {{ Str::limit(strip_tags($reply->body), 80) }}
                                        </div>
                                        <span style="font-size: 12px; color: var(--agri-text-muted);">by {{ optional($reply->user)->name ?? '—' }}</span>
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted); font-style: italic;">Reply deleted</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; max-width:200px;">
                                    @if($thread)
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" style="font-size: 13px; font-weight: 700; color: var(--agri-primary); text-decoration: none;" title="{{ $thread->title }}">
                                            {{ Str::limit($thread->title, 55) }}
                                        </a>
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted); font-style: italic;">Thread deleted</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px;">
                                    <div style="font-size: 14px; font-weight: 700; color: var(--agri-text-main);">{{ optional($flag->reporter)->name ?? '—' }}</div>
                                    @if($flag->reporter?->email)
                                        <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 2px;">{{ $flag->reporter->email }}</div>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px;">
                                    <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;" title="{{ $flag->reason }}">
                                        {{ Str::limit($flag->reason, 30) }}
                                    </span>
                                </td>
                                <td style="padding: 18px 24px;">
                                    <span style="background: {{ $sc[0] }}; color: {{ $sc[1] }}; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid {{ $sc[0] }};">
                                        {{ ucfirst($flag->status) }}
                                    </span>
                                </td>
                                <td style="padding: 18px 24px;">
                                    <div style="font-size: 12px; color: var(--agri-text-muted); margin-bottom: 4px;">{{ $flag->created_at->format('d M Y, H:i') }}</div>
                                    @if($flag->reviewer)
                                        <div style="font-size: 11px; color: var(--agri-text-main); font-weight: 600;">
                                            <i class="fa fa-user" style="font-size: 9px; margin-right: 4px;"></i> {{ $flag->reviewer->name }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; text-align: end;">
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                        @if($flag->status === 'pending')
                                            <div style="display: flex; gap: 6px;">
                                                <form method="POST" action="{{ route('admin.forum.flags.confirm', $flag->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-agri" style="padding: 6px 10px; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; font-size: 11px; font-weight: 700;" title="Confirm flag — reply stays flagged">
                                                        <i class="fa fa-check"></i> Confirm
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.forum.flags.dismiss', $flag->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-agri" style="padding: 6px 10px; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border); font-size: 11px; font-weight: 700;" title="Dismiss — reply restored to visible">
                                                        <i class="fa fa-times"></i> Dismiss
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($flag->status === 'reviewed')
                                            <span style="font-size: 12px; font-weight: 700; color: #059669;"><i class="fa fa-check-circle me-1"></i>Confirmed</span>
                                        @else
                                            <span style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted);"><i class="fa fa-ban me-1"></i>Dismissed</span>
                                        @endif

                                        @if($thread)
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri btn-agri-primary" style="padding: 4px 10px; font-size: 11px; font-weight: 600; text-decoration: none;">
                                                <i class="fa fa-eye"></i> View Thread
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if($flags->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $flags->links() }}
            </div>
            @endif
        </div>

        {{-- Legend --}}
        <div style="margin-top: 24px; font-size: 13px; color: var(--agri-text-muted); display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">Pending</span>
                <span>Awaiting admin review</span>
            </div>
            <div style="width: 1px; height: 14px; background: var(--agri-border);"></div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">Reviewed</span>
                <span>Flag confirmed, reply remains flagged</span>
            </div>
            <div style="width: 1px; height: 14px; background: var(--agri-border);"></div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="background: #F3F4F6; color: #4B5563; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">Dismissed</span>
                <span>Flag dismissed, reply restored to visible</span>
            </div>
        </div>

    </div>

@endsection
