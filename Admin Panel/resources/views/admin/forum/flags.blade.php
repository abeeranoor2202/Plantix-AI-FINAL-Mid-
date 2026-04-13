@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Forum</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Flags</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Flagged Replies</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and resolve forum reports in one unified moderation table.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 12px 20px; color: #166534; font-weight: 700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px; color: #991b1b; font-weight: 700;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Flag Report List</h4>
            <form method="GET" action="{{ route('admin.forum.flags.index') }}" style="display: flex; align-items: center; gap: 10px;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 180px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    <option value="ignored" @selected(request('status') === 'ignored')>Ignored</option>
                </select>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                <a href="{{ route('admin.forum.flags.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none;">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">ID</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reply</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Thread</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reporter</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reason</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flags as $flag)
                        @php $thread = $flag->reply?->thread; @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $flag->id }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit(strip_tags($flag->reply->body ?? 'Reply deleted'), 45) }}</td>
                            <td class="px-4 py-3">{{ $thread ? \Illuminate\Support\Str::limit($thread->title, 35) : 'Deleted thread' }}</td>
                            <td class="px-4 py-3">{{ optional($flag->reporter)->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($flag->reason, 30) }}</td>
                            <td class="px-4 py-3">
                                @php($st = strtolower((string) $flag->status))
                                @php
                                    $isResolved = in_array($st, ['resolved', 'reviewed'], true);
                                    $isIgnored = in_array($st, ['ignored', 'dismissed'], true);
                                    $label = $isResolved ? 'RESOLVED' : ($isIgnored ? 'IGNORED' : 'PENDING');
                                @endphp
                                <span class="badge rounded-pill {{ $label === 'PENDING' ? 'bg-warning text-dark' : ($label === 'RESOLVED' ? 'bg-success' : 'bg-secondary') }}">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    @if($thread)
                                        <a href="{{ route('admin.forum.threads.show', $thread->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if($st === 'pending')
                                        <form method="POST" action="{{ route('admin.forum.flags.confirm', $flag->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #ecfdf5; color: #059669; border-radius: 999px; border: none;" title="Approve / Keep Reply"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.forum.flags.delete-reply', $flag->id) }}" class="d-inline" onsubmit="return confirm('Delete this reply and resolve report?')">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete Reply"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.forum.flags.dismiss', $flag->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #f3f4f6; color: #4b5563; border-radius: 999px; border: none;" title="Ignore Report"><i class="fas fa-times"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No flag reports found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($flags->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $flags->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
