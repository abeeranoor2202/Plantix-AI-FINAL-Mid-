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
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Audit Log</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-history text-success me-2"></i> Forum Audit Log</h1>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Filters --}}
        {{-- Filters --}}
        <div class="card-agri mb-4" style="padding: 24px;">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Action</label>
                    <select name="action" class="form-agri">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">User ID</label>
                    <input type="number" name="user_id" class="form-agri" placeholder="e.g. 42" value="{{ request('user_id') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary" style="padding-left: 24px; padding-right: 24px;">Filter</button>
                    <a href="{{ route('admin.forum.audit-log') }}" class="btn-agri btn-agri-outline" style="padding-left: 24px; padding-right: 24px; text-decoration: none;">Reset</a>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('admin.forum.index') }}" class="btn-agri btn-agri-outline" style="display: inline-flex; text-decoration: none;">
                        <i class="fa fa-arrow-left"></i> Forum
                    </a>
                </div>
            </form>
        </div>

        {{-- Audit Log Table --}}
        {{-- Audit Log Table --}}
        <div class="card-agri" style="padding: 0; overflow: hidden;">
            <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-bg); color: var(--agri-text-muted); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-history"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Action Log</h6>
                </div>
                <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $logs->total() }} entries</span>
            </div>
            <div class="table-responsive">
                @if($logs->isEmpty())
                    <div style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fa fa-history" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                        <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No audit log entries found.</p>
                    </div>
                @else
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; width: 60px;">#</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Action</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Performed By</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Thread</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reply</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Meta</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            @php
                                $badgeVariant = 'secondary';
                                if (str_contains($log->action, 'delete'))  { $badgeVariant = 'danger'; }
                                elseif (str_contains($log->action, 'lock') || str_contains($log->action, 'flag'))  { $badgeVariant = 'warning'; }
                                elseif (str_contains($log->action, 'resolve') || str_contains($log->action, 'official') || str_contains($log->action, 'unban'))  { $badgeVariant = 'success'; }
                                elseif (str_contains($log->action, 'pin'))  { $badgeVariant = 'info'; }
                            @endphp
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 18px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">{{ $log->id }}</td>
                                <td style="padding: 18px 24px;">
                                    <x-badge :variant="$badgeVariant" style="font-family: monospace; font-size: 11px;">{{ $log->action }}</x-badge>
                                </td>
                                <td style="padding: 18px 24px;">
                                    @if($log->user)
                                        <div style="font-size: 14px; font-weight: 700; color: var(--agri-text-main);">{{ $log->user->name }}</div>
                                        <div style="font-size: 12px; color: var(--agri-text-muted);">#{{ $log->user_id }}</div>
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted); font-weight: 600;">#{{ $log->user_id }}</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; max-width:200px;">
                                    @if($log->thread)
                                        <a href="{{ route('admin.forum.threads.show', $log->thread_id) }}" style="font-size: 13px; font-weight: 700; color: var(--agri-primary); text-decoration: none;" title="{{ $log->thread->title }}">
                                            {{ Str::limit($log->thread->title, 50) }}
                                        </a>
                                    @elseif($log->thread_id)
                                        <span style="font-size: 13px; color: var(--agri-text-muted);">#{{ $log->thread_id }} <em style="font-size: 11px; opacity: 0.7;">(deleted)</em></span>
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px;">
                                    @if($log->reply)
                                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-main);">#{{ $log->reply_id }}</span>
                                    @elseif($log->reply_id)
                                        <span style="font-size: 13px; color: var(--agri-text-muted);">#{{ $log->reply_id }} <em style="font-size: 11px; opacity: 0.7;">(deleted)</em></span>
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; max-width:200px;">
                                    @if($log->meta)
                                        @php $meta = is_array($log->meta) ? $log->meta : json_decode($log->meta, true); @endphp
                                        @if($meta)
                                            <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px;">
                                                @foreach($meta as $k => $v)
                                                    @if(!is_null($v) && $v !== '')
                                                    <div style="display: flex; align-items: flex-start;">
                                                        <strong style="color: var(--agri-text-muted); margin-right: 6px; white-space: nowrap;">{{ $k }}:</strong>
                                                        <span style="color: var(--agri-text-main); word-break: break-all;">{{ is_bool($v) ? ($v ? 'yes' : 'no') : Str::limit((string) $v, 40) }}</span>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="font-size: 13px; color: var(--agri-text-muted);">—</span>
                                        @endif
                                    @else
                                        <span style="font-size: 13px; color: var(--agri-text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding: 18px 24px; color: var(--agri-text-muted); font-size: 12px; white-space: nowrap;">
                                    {{ $log->created_at->format('d M Y') }}<br>
                                    {{ $log->created_at->format('H:i:s') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if($logs->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $logs->links() }}
            </div>
            @endif
        </div>

        {{-- Action Legend --}}
        <div style="margin-top: 24px; display: flex; flex-wrap: wrap; gap: 8px;">
            <span style="background: #E0E7FF; color: #3730A3; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #C7D2FE; font-family: monospace;">thread.*</span>
            <span style="background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #FECACA; font-family: monospace;">thread.delete / reply.delete / user.ban</span>
            <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #FDE68A; font-family: monospace;">thread.lock / reply.flag</span>
            <span style="background: #D1FAE5; color: #065F46; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #A7F3D0; font-family: monospace;">thread.resolve / reply.official / user.unban</span>
            <span style="background: #E0F2FE; color: #0369A1; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #BAE6FD; font-family: monospace;">thread.pin / reply.edit</span>
        </div>

    </div>

@endsection
