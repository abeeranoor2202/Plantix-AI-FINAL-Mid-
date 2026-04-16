@extends('expert.layouts.app')

@section('title', 'Notifications')

@section('content')
@php
    $iconMap = \App\Services\Expert\ExpertNotificationService::ICON_MAP;
    $actionLabelMap = \App\Services\Expert\ExpertNotificationService::ACTION_LABEL_MAP;
    $typeBadgeMap = [
        'appointment' => ['label' => 'Consultation', 'variant' => 'info'],
        'forum' => ['label' => 'Forum', 'variant' => 'success'],
        'payout' => ['label' => 'Payout', 'variant' => 'warning'],
        'system' => ['label' => 'System', 'variant' => 'secondary'],
    ];
@endphp

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; gap: 16px; flex-wrap: wrap;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('expert.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Notifications</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Notifications</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">System and activity alerts</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <form method="POST" action="{{ route('expert.notifications.read-all') }}" style="margin: 0;">
            @csrf
            <x-button type="submit" variant="outline" icon="fas fa-check-double">Mark all as read</x-button>
        </form>
        <form method="POST" action="{{ route('expert.notifications.clear-all') }}" style="margin: 0;" onsubmit="return confirm('Clear all notifications?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" style="height: 44px; padding: 0 16px; font-weight: 700; border-radius: var(--agri-radius-md);">Clear all</button>
        </form>
    </div>
</div>

<div class="card-agri" style="padding: 20px; margin-bottom: 16px;">
    <form method="GET" action="{{ route('expert.notifications.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">Type</label>
            <select name="type" class="form-agri">
                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All</option>
                <option value="appointments" @selected(($filters['type'] ?? 'all') === 'appointments')>Appointments</option>
                <option value="forum" @selected(($filters['type'] ?? 'all') === 'forum')>Forum</option>
                <option value="payments" @selected(($filters['type'] ?? 'all') === 'payments')>Payments</option>
                <option value="system" @selected(($filters['type'] ?? 'all') === 'system')>System</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">Status</label>
            <select name="status" class="form-agri">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All</option>
                <option value="unread" @selected(($filters['status'] ?? 'all') === 'unread')>Unread</option>
                <option value="read" @selected(($filters['status'] ?? 'all') === 'read')>Read</option>
            </select>
        </div>
        <div class="col-md-4" style="display: flex; gap: 8px;">
            <button type="submit" class="btn-agri btn-agri-primary" style="height: 44px;">Apply</button>
            <a href="{{ route('expert.notifications.index') }}" class="btn-agri btn-agri-outline" style="height: 44px; display: inline-flex; align-items: center;">Clear</a>
        </div>
    </form>
</div>

<form id="bulkReadForm" method="POST" action="{{ route('expert.notifications.bulk-read') }}" style="display:none;">@csrf</form>
<form id="bulkDeleteForm" method="POST" action="{{ route('expert.notifications.bulk-delete') }}" style="display:none;">@csrf</form>

<div class="card-agri" style="padding: 18px 20px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
    <div style="display:flex; align-items:center; gap: 10px;">
        <input type="checkbox" id="selectAllNotifications" style="width: 18px; height: 18px;">
        <label for="selectAllNotifications" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">Select all on page</label>
        <span id="selectedCount" style="font-size: 13px; color: var(--agri-text-muted);">0 selected</span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" id="bulkReadBtn" class="btn-agri btn-agri-outline" style="height: 40px;">Mark selected as read</button>
        <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger" style="height: 40px; font-weight: 700; border-radius: var(--agri-radius-md);">Delete selected</button>
    </div>
</div>

<div id="expertNotificationsList" style="display: grid; gap: 12px;">
    @forelse($notifications as $notif)
        @php
            $categoryKey = explode('.', (string) $notif->type)[0] ?? 'system';
            $category = $typeBadgeMap[$categoryKey] ?? ['label' => 'System', 'variant' => 'secondary'];
            $isUnread = ! $notif->is_read;
            $actionLabel = $actionLabelMap[$notif->type] ?? 'View';
            $message = $notif->message ?? $notif->body ?? '';
            $actionUrl = $notif->action_url ?: data_get($notif->data, 'action_url');
        @endphp
        <div class="card-agri" style="padding: 16px 18px; border: 1px solid {{ $isUnread ? '#BBF7D0' : 'var(--agri-border)' }}; background: {{ $isUnread ? '#F0FDF4' : 'var(--agri-white)' }};">
            <div style="display: flex; gap: 14px; align-items: flex-start;">
                <div style="padding-top: 2px;">
                    <input type="checkbox" class="notification-selector" value="{{ $notif->id }}" style="width: 18px; height: 18px;">
                </div>
                <div style="width: 42px; height: 42px; border-radius: 999px; background: #ECFDF3; color: #065F46; display:flex; align-items:center; justify-content:center; font-size: 20px; flex: 0 0 auto;">
                    <i class="{{ $iconMap[$notif->type] ?? 'mdi mdi-bell-outline' }}"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; margin-bottom: 4px; flex-wrap: wrap;">
                        <div>
                            <a href="{{ route('expert.notifications.open', $notif) }}" style="text-decoration: none; color: var(--agri-text-heading); font-size: 16px; font-weight: {{ $isUnread ? '800' : '700' }};">
                                {{ $notif->title }}
                            </a>
                            <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 2px;">{{ $notif->created_at?->diffForHumans() }}</div>
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <x-badge :variant="$category['variant']">{{ $category['label'] }}</x-badge>
                            <x-badge :variant="$isUnread ? 'warning' : 'secondary'">{{ $isUnread ? 'Unread' : 'Read' }}</x-badge>
                        </div>
                    </div>
                    <p style="margin: 0 0 12px 0; color: {{ $isUnread ? 'var(--agri-text-heading)' : 'var(--agri-text-muted)' }}; font-size: 14px; line-height: 1.45;">{{ \Illuminate\Support\Str::limit($message, 160) }}</p>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        @if($actionUrl)
                            <a href="{{ route('expert.notifications.open', $notif) }}" class="btn-agri btn-agri-primary" style="height: 36px; display: inline-flex; align-items: center; font-size: 13px;">{{ $actionLabel }}</a>
                        @endif
                        @if($isUnread)
                            <form method="POST" action="{{ route('expert.notifications.read', $notif) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-agri btn-agri-outline" style="height: 36px; font-size: 13px;">Mark read</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card-agri" style="padding: 52px 24px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 999px; background: #F0FDF4; color: #16A34A; margin: 0 auto 16px auto; display:flex; align-items:center; justify-content:center; font-size: 32px;">
                <i class="mdi mdi-bell-check-outline"></i>
            </div>
            <h3 style="margin: 0 0 8px 0; color: var(--agri-primary-dark); font-size: 20px; font-weight: 700;">You are all caught up!</h3>
            <p style="margin: 0; color: var(--agri-text-muted); font-size: 14px;">New notifications will appear here.</p>
        </div>
    @endforelse
</div>

<div id="expertNotificationsPagination" style="margin-top: 20px; display: flex; justify-content: center;">
    @if($notifications->hasPages())
        {{ $notifications->links('pagination::bootstrap-5') }}
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const selectAll = document.getElementById('selectAllNotifications');
        const selectedCount = document.getElementById('selectedCount');
        const selectors = () => Array.from(document.querySelectorAll('.notification-selector'));
        const bulkReadForm = document.getElementById('bulkReadForm');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const bulkReadBtn = document.getElementById('bulkReadBtn');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

        function selectedIds() {
            return selectors().filter(el => el.checked).map(el => el.value);
        }

        function syncSelectedLabel() {
            selectedCount.textContent = selectedIds().length + ' selected';
        }

        function submitBulk(form, ids) {
            form.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
            ids.forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'ids[]';
                hidden.value = id;
                form.appendChild(hidden);
            });
            form.submit();
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                selectors().forEach(cb => cb.checked = selectAll.checked);
                syncSelectedLabel();
            });
        }

        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('notification-selector')) {
                syncSelectedLabel();
            }
        });

        bulkReadBtn?.addEventListener('click', function () {
            const ids = selectedIds();
            if (!ids.length) {
                return;
            }
            submitBulk(bulkReadForm, ids);
        });

        bulkDeleteBtn?.addEventListener('click', function () {
            const ids = selectedIds();
            if (!ids.length) {
                return;
            }
            if (!confirm('Delete selected notifications?')) {
                return;
            }
            submitBulk(bulkDeleteForm, ids);
        });

        async function refreshNotificationsPage() {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const nextList = parsed.getElementById('expertNotificationsList');
                const nextPagination = parsed.getElementById('expertNotificationsPagination');

                if (nextList) {
                    document.getElementById('expertNotificationsList').innerHTML = nextList.innerHTML;
                }
                if (nextPagination) {
                    document.getElementById('expertNotificationsPagination').innerHTML = nextPagination.innerHTML;
                }
                selectAll.checked = false;
                syncSelectedLabel();
            } catch (error) {
                // Silent polling fallback.
            }
        }

        setInterval(refreshNotificationsPage, 20000);
        syncSelectedLabel();
    })();
</script>
@endpush
