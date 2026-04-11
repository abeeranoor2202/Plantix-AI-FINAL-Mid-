@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Forum</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Forum Moderation</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage threads, moderation flags, and discussion categories.</p>
        </div>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden; margin-bottom: 22px;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Forum Overview</h4>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                <a href="{{ route('admin.forum.threads') }}" class="btn-agri btn-agri-outline" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">All Threads</a>
                <a href="{{ route('admin.forum.flags.index') }}" class="btn-agri btn-agri-outline" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">Flags</a>
                <a href="{{ route('admin.forum.categories.index') }}" class="btn-agri btn-agri-outline" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">Categories</a>
                <a href="{{ route('admin.forum.audit-log') }}" class="btn-agri btn-agri-outline" style="height: 40px; display: inline-flex; align-items: center; text-decoration: none;">Audit Log</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Metric</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Value</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">Total Threads</td>
                        <td class="px-4 py-3"><span class="badge rounded-pill bg-secondary">{{ (int) ($stats['total'] ?? 0) }}</span></td>
                        <td class="px-4 py-3"><a href="{{ route('admin.forum.threads') }}" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">View Threads</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">Open Threads</td>
                        <td class="px-4 py-3"><span class="badge rounded-pill bg-success">{{ (int) ($stats['open'] ?? 0) }}</span></td>
                        <td class="px-4 py-3"><a href="{{ route('admin.forum.threads') }}?status=open" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">Filter Open</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">Locked Threads</td>
                        <td class="px-4 py-3"><span class="badge rounded-pill bg-dark">{{ (int) ($stats['locked'] ?? 0) }}</span></td>
                        <td class="px-4 py-3"><a href="{{ route('admin.forum.threads') }}?is_locked=1" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">Filter Locked</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">Pending Approval</td>
                        <td class="px-4 py-3"><span class="badge rounded-pill bg-warning text-dark">{{ (int) ($stats['pending'] ?? 0) }}</span></td>
                        <td class="px-4 py-3"><a href="{{ route('admin.forum.threads') }}?is_approved=0" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">Review Pending</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">Flag Reports</td>
                        <td class="px-4 py-3"><span class="badge rounded-pill bg-danger">{{ (int) ($stats['flags'] ?? 0) }}</span></td>
                        <td class="px-4 py-3"><a href="{{ route('admin.forum.flags.index') }}" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">Review Flags</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white; display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--agri-text-heading);">Pending Threads</h5>
                    <span class="badge rounded-pill bg-warning text-dark">{{ $pendingThreads->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Title</th>
                                <th style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Author</th>
                                <th class="text-end" style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingThreads->take(6) as $thread)
                                <tr>
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($thread->title, 50) }}</td>
                                    <td class="px-4 py-3">{{ optional($thread->user)->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4" style="color: var(--agri-text-muted);">No pending threads.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white; display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--agri-text-heading);">Recent Flags</h5>
                    <span class="badge rounded-pill bg-danger">{{ $recentFlags->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reason</th>
                                <th style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Thread</th>
                                <th class="text-end" style="padding: 14px 20px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFlags->take(6) as $flag)
                                @php $thread = $flag->reply?->thread; @endphp
                                <tr>
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($flag->reason, 26) }}</td>
                                    <td class="px-4 py-3">{{ $thread ? \Illuminate\Support\Str::limit($thread->title, 40) : 'Deleted thread' }}</td>
                                    <td class="px-4 py-3 text-end">
                                        @if($thread)
                                            <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;"><i class="fas fa-eye"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4" style="color: var(--agri-text-muted);">No flag reports.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
