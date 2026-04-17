@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-card>
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h2 style="margin: 0;">Platform Activity Stream</h2>
                    <p style="margin: 6px 0 0 0;">Unified feed for forum, orders, appointments, and moderation.</p>
                </div>
                <div style="display:flex; align-items:center; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                    <a href="{{ route('admin.activity.export.csv', request()->query()) }}" class="btn-agri btn-agri-outline">Export CSV</a>
                    <form method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                        <x-search-filter
                            :embedded="true"
                            name="q"
                            :value="request('q', '')"
                            placeholder="Search action, entity, or context"
                        />
                        <input type="text" name="action" value="{{ request('action') }}" class="form-control" placeholder="Action" style="min-width: 180px;">
                        <input type="text" name="entity_type" value="{{ request('entity_type') }}" class="form-control" placeholder="Entity" style="min-width: 160px;">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="min-width: 150px;">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="min-width: 150px;">
                        <select name="actor_role" class="form-select" style="min-width: 140px;">
                        <option value="">All Roles</option>
                        @foreach(['admin', 'user', 'expert', 'vendor', 'system'] as $role)
                            <option value="{{ $role }}" @selected(request('actor_role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                        </select>
                        <button type="submit" class="btn-agri btn-agri-primary">Filter</button>
                    </form>
                </div>
            </div>

            <div style="display:flex; gap: 12px; flex-wrap: wrap; margin-top: 14px;">
                <span class="badge bg-light text-dark">Total: {{ $summary['total'] ?? 0 }}</span>
                <span class="badge bg-success">Today: {{ $summary['today'] ?? 0 }}</span>
                <span class="badge bg-danger">Critical: {{ $summary['critical'] ?? 0 }}</span>
            </div>
        </div>

        <x-table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Context</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at?->format('d M Y h:i A') }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $activity->actor?->name ?? 'System' }}</div>
                            <small class="text-muted">{{ $activity->actor_role ?? 'system' }}</small>
                        </td>
                        <td><span class="badge bg-info text-dark">{{ $activity->action }}</span></td>
                        <td>{{ $activity->entity_type ?? 'n/a' }} #{{ $activity->entity_id ?? '-' }}</td>
                        <td>
                            <small class="text-muted" style="display: block; white-space: pre-wrap;">{{ json_encode($activity->context ?? [], JSON_UNESCAPED_SLASHES) }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">No platform activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <div style="padding: 16px 24px; border-top: 1px solid var(--agri-border);">
            {{ $activities->links() }}
        </div>
    </x-card>
</div>
@endsection
