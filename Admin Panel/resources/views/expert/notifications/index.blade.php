@extends('expert.layouts.app')

@section('title', 'Notifications')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('expert.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Notifications</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Notifications</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">System and activity alerts.</p>
    </div>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('expert.notifications.read-all') }}" style="margin: 0;">
            @csrf
            <x-button type="submit" variant="primary" icon="fas fa-check-double">Mark All Read</x-button>
        </form>
    @endif
</div>

<div class="card-agri" style="padding: 0; overflow: hidden;">
    <x-table>
        <thead style="background: var(--agri-bg);">
            <tr>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TITLE</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">BODY</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TYPE</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TIME</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">STATUS</th>
                <th class="text-end" style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notifications->items() as $notif)
                <tr style="{{ !$notif->is_read ? 'background:#f9fafb;' : '' }}">
                    <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">{{ $notif->title }}</td>
                    <td class="px-4 py-3">{{ Str::limit($notif->body ?? '-', 90) }}</td>
                    <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $notif->type)) }}</td>
                    <td class="px-4 py-3">{{ $notif->created_at->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <x-badge :variant="$notif->is_read ? 'secondary' : 'warning'">{{ $notif->is_read ? 'Read' : 'Unread' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-end">
                        <div style="display: inline-flex; gap: 8px;">
                            @if(!$notif->is_read)
                                <form method="POST" action="{{ route('expert.notifications.read', $notif) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px; border: none;" title="Mark Read"><i class="fas fa-check"></i></button>
                                </form>
                            @else
                                <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #9ca3af; border-radius: 999px; border: none;" disabled title="Already read"><i class="fas fa-check"></i></button>
                            @endif
                            <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #9ca3af; border-radius: 999px; border: none;" title="Edit unavailable" disabled><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #fca5a5; border-radius: 999px; border: none;" title="Delete unavailable" disabled><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">
                        <i class="mdi mdi-bell-outline" style="font-size: 28px; display:block; margin-bottom: 8px; opacity: .5;"></i>
                        No notifications.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>

@if($notifications->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center;">
        {{ $notifications->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
