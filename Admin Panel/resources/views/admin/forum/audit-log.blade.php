@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-history text-success me-2"></i> Forum Audit Log</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.forum.index') }}">Forum</a></li>
                <li class="breadcrumb-item active">Audit Log</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Action</label>
                        <select name="action" class="form-select form-select-sm rounded-pill border-0 bg-light">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">User ID</label>
                        <input type="number" name="user_id" class="form-control form-control-sm rounded-pill border-0 bg-light"
                               placeholder="e.g. 42" value="{{ request('user_id') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">Filter</button>
                        <a href="{{ route('admin.forum.audit-log') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Reset</a>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="{{ route('admin.forum.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Forum
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Audit Log Table --}}
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa fa-history me-2"></i>Action Log</h5>
                <span class="text-muted small">{{ $logs->total() }} entries</span>
            </div>
            <div class="card-body p-0">
                @if($logs->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-history fa-3x mb-3 d-block"></i>No audit log entries found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Action</th>
                                    <th>Performed By</th>
                                    <th>Thread</th>
                                    <th>Reply</th>
                                    <th>Meta</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                @php
                                    // Colour-code actions by category
                                    $actionColor = 'secondary';
                                    if (str_starts_with($log->action, 'thread.')) {
                                        $actionColor = match(true) {
                                            str_contains($log->action, 'delete')  => 'danger',
                                            str_contains($log->action, 'lock')    => 'warning',
                                            str_contains($log->action, 'resolve') => 'success',
                                            str_contains($log->action, 'archive') => 'secondary',
                                            str_contains($log->action, 'pin')     => 'info',
                                            default                               => 'primary',
                                        };
                                    } elseif (str_starts_with($log->action, 'reply.')) {
                                        $actionColor = match(true) {
                                            str_contains($log->action, 'delete')   => 'danger',
                                            str_contains($log->action, 'flag')     => 'warning',
                                            str_contains($log->action, 'official') => 'success',
                                            default                                => 'info',
                                        };
                                    } elseif (str_starts_with($log->action, 'user.')) {
                                        $actionColor = str_contains($log->action, 'ban') ? 'danger' : 'success';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted small">{{ $log->id }}</td>
                                    <td>
                                        <span class="badge bg-{{ $actionColor }}">{{ $log->action }}</span>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <span class="fw-semibold small">{{ $log->user->name }}</span>
                                            <br><span class="text-muted" style="font-size:11px;">#{{ $log->user_id }}</span>
                                        @else
                                            <span class="text-muted small">#{{ $log->user_id }}</span>
                                        @endif
                                    </td>
                                    <td style="max-width:200px;">
                                        @if($log->thread)
                                            <a href="{{ route('admin.forum.threads.show', $log->thread_id) }}"
                                               class="text-dark small text-decoration-none"
                                               title="{{ $log->thread->title }}">
                                                {{ Str::limit($log->thread->title, 50) }}
                                            </a>
                                        @elseif($log->thread_id)
                                            <span class="text-muted small">#{{ $log->thread_id }} <em>(deleted)</em></span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->reply)
                                            <span class="small text-muted">#{{ $log->reply_id }}</span>
                                        @elseif($log->reply_id)
                                            <span class="text-muted small">#{{ $log->reply_id }} <em>(deleted)</em></span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width:200px;">
                                        @if($log->meta)
                                            @php $meta = is_array($log->meta) ? $log->meta : json_decode($log->meta, true); @endphp
                                            @if($meta)
                                                <ul class="list-unstyled mb-0 small text-muted">
                                                    @foreach($meta as $k => $v)
                                                        @if(!is_null($v) && $v !== '')
                                                        <li>
                                                            <span class="fw-semibold">{{ $k }}:</span>
                                                            {{ is_bool($v) ? ($v ? 'yes' : 'no') : Str::limit((string) $v, 40) }}
                                                        </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small" style="white-space:nowrap;">
                                        {{ $log->created_at->format('d M Y') }}<br>
                                        {{ $log->created_at->format('H:i:s') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Action Legend --}}
        <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge bg-primary">thread.*</span>
            <span class="badge bg-danger">thread.delete / reply.delete</span>
            <span class="badge bg-warning text-dark">thread.lock / reply.flag</span>
            <span class="badge bg-success">thread.resolve / reply.official</span>
            <span class="badge bg-info">thread.pin / reply.edit</span>
            <span class="badge bg-danger">user.ban</span>
            <span class="badge bg-success">user.unban</span>
        </div>

    </div>
</div>
@endsection
