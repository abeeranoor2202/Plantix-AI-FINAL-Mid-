@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 14px;">
                <a href="{{ url('/dashboard') }}" style="color: var(--agri-text-muted); text-decoration: none;">Dashboard</a>
                <span style="color: var(--agri-text-muted);">/</span>
                <a href="{{ route('admin.ai.dashboard') }}" style="color: var(--agri-text-muted); text-decoration: none;">AI Modules</a>
                <span style="color: var(--agri-text-muted);">/</span>
                <span style="color: var(--agri-primary); font-weight: 700;">Chat Monitor</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 800; margin: 0; color: var(--agri-primary-dark);">AI Chat Monitoring</h1>
            <p style="margin: 6px 0 0 0; color: var(--agri-text-muted);">Track fallback usage, escalation queue, and expert assignments.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card-agri p-3"><div class="small text-muted">Total Sessions</div><div class="h4 mb-0">{{ number_format($stats['total_sessions']) }}</div></div>
        </div>
        <div class="col-md-3">
            <div class="card-agri p-3"><div class="small text-muted">Messages Today</div><div class="h4 mb-0">{{ number_format($stats['messages_today']) }}</div></div>
        </div>
        <div class="col-md-3">
            <div class="card-agri p-3"><div class="small text-muted">Fallbacks Today</div><div class="h4 mb-0">{{ number_format($stats['fallbacks_today']) }}</div></div>
        </div>
        <div class="col-md-3">
            <div class="card-agri p-3"><div class="small text-muted">Open Escalations</div><div class="h4 mb-0">{{ number_format($stats['pending_escalations'] + $stats['assigned_escalations']) }}</div></div>
        </div>
    </div>

    <div class="card-agri mb-4" style="padding: 0; overflow: hidden;">
        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center" style="background: white;">
            <h4 class="mb-0" style="font-size: 17px; font-weight: 800;">Escalation Queue</h4>
            <form method="GET" action="{{ route('admin.ai.chat-monitor') }}" class="d-flex" style="gap: 10px;">
                <select name="status" class="form-agri" style="height: 40px; min-width: 150px; margin: 0;">
                    <option value="">All Statuses</option>
                    @foreach(['pending','assigned','resolved','closed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ strtoupper($status) }}</option>
                    @endforeach
                </select>
                <button class="btn-agri btn-agri-primary" style="height: 40px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th class="px-4 py-3">Ticket</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Expert</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escalations as $ticket)
                        <tr>
                            <td class="px-4 py-3">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">{{ $ticket->session?->user?->name ?? 'Guest/Unknown' }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($ticket->reason ?: 'No reason provided.', 70) }}</td>
                            <td class="px-4 py-3"><span class="badge bg-secondary">{{ strtoupper($ticket->status) }}</span></td>
                            <td class="px-4 py-3">{{ $ticket->assignedExpert?->user?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-end">
                                @if(in_array($ticket->status, ['pending', 'assigned'], true))
                                    <form method="POST" action="{{ route('admin.ai.chat-escalations.assign', $ticket->id) }}" class="d-inline-flex" style="gap: 8px; align-items: center;">
                                        @csrf
                                        <select name="expert_id" class="form-agri" style="height: 36px; min-width: 170px; margin: 0;">
                                            @foreach($experts as $expert)
                                                <option value="{{ $expert->id }}" @selected((int) $ticket->assigned_expert_id === (int) $expert->id)>
                                                    {{ $expert->user?->name ?? ('Expert #' . $expert->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Assign</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.ai.chat-escalations.resolve', $ticket->id) }}" class="d-inline-flex ms-2" style="gap: 8px; align-items: center;">
                                        @csrf
                                        <input type="hidden" name="final_status" value="resolved">
                                        <input type="text" name="resolution_notes" class="form-control form-control-sm" placeholder="Resolution notes" required style="min-width: 170px;">
                                        <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                    </form>
                                @else
                                    <span class="text-muted small">{{ $ticket->resolution_notes ?: 'No notes' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No escalation tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($escalations->hasPages())
            <div class="p-3 border-top bg-white d-flex justify-content-center">
                {{ $escalations->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="px-4 py-3 border-bottom" style="background: white;">
            <h4 class="mb-0" style="font-size: 17px; font-weight: 800;">Recent Sessions</h4>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th class="px-4 py-3">Session Key</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Context</th>
                        <th class="px-4 py-3">Messages</th>
                        <th class="px-4 py-3">Open Escalations</th>
                        <th class="px-4 py-3">Last Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($session->session_key, 24) }}</td>
                            <td class="px-4 py-3">{{ $session->user?->name ?? 'Guest/Unknown' }}</td>
                            <td class="px-4 py-3">{{ strtoupper($session->context_type) }}</td>
                            <td class="px-4 py-3">{{ $session->messages_count }}</td>
                            <td class="px-4 py-3">{{ $session->open_escalations_count }}</td>
                            <td class="px-4 py-3">{{ optional($session->last_active_at)->format('M d, Y H:i') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No chat sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
            <div class="p-3 border-top bg-white d-flex justify-content-center">
                {{ $sessions->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
