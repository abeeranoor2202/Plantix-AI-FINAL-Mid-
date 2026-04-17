@props([
    'title' => 'Notifications',
    'subtitle' => 'Latest updates and actions',
    'notifications' => collect(),
    'unreadCount' => 0,
    'filters' => [],
    'indexUrl' => '#',
    'readAllUrl' => '#',
    'clearAllUrl' => null,
    'markReadUrlBase' => null,
    'openRoutePrefix' => null,
    'typeOptions' => [],
    'statusOptions' => ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'],
])

<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 28px; gap:16px; flex-wrap:wrap;">
    <div>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
            <a href="{{ $indexUrl }}" style="text-decoration:none; color:var(--agri-text-muted); font-size:14px; font-weight:600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px; color:var(--agri-text-muted);"></i>
            <span style="color:var(--agri-primary); font-size:14px; font-weight:600;">{{ $title }}</span>
        </div>
        <h1 style="font-size:28px; font-weight:700; color:var(--agri-primary-dark); margin:0;">{{ $title }}</h1>
        <p style="color:var(--agri-text-muted); margin:4px 0 0 0;">{{ $subtitle }}</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        @if($unreadCount > 0)
            <span class="badge bg-danger" style="padding: 10px 14px;">{{ $unreadCount }} unread</span>
        @endif
        <form method="POST" action="{{ $readAllUrl }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-agri btn-agri-primary" style="height:44px; padding:0 16px;">Mark all read</button>
        </form>
        @if($clearAllUrl)
            <form method="POST" action="{{ $clearAllUrl }}" style="margin:0;" data-confirm="Clear all notifications?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" style="height:44px; padding:0 16px; font-weight:700; border-radius:var(--agri-radius-md);">Clear all</button>
            </form>
        @endif
    </div>
</div>

<div class="card-agri" style="padding:20px; margin-bottom:16px;">
    <form method="GET" action="{{ $indexUrl }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" style="font-size:12px; font-weight:700; color:var(--agri-text-muted);">Type</label>
            <select name="type" class="form-agri">
                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All</option>
                @foreach($typeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" style="font-size:12px; font-weight:700; color:var(--agri-text-muted);">Status</label>
            <select name="status" class="form-agri">
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" style="display:flex; gap:8px;">
            <button type="submit" class="btn-agri btn-agri-primary" style="height:44px;">Apply</button>
            <a href="{{ $indexUrl }}" class="btn-agri btn-agri-outline" style="height:44px; display:inline-flex; align-items:center;">Reset</a>
        </div>
    </form>
</div>

<div id="notificationCenterList" style="display:grid; gap:12px;">
    @forelse($notifications as $notification)
        @php
            $isUnread = ! (bool) (data_get($notification, 'status') === 'read' || data_get($notification, 'read') || data_get($notification, 'is_read'));
            $actionUrl = data_get($notification, 'action_url') ?? '#';
        @endphp
        <div class="card-agri" style="padding:16px 18px; border:1px solid {{ $isUnread ? '#BBF7D0' : 'var(--agri-border)' }}; background: {{ $isUnread ? '#F0FDF4' : 'var(--agri-white)' }};">
            <div style="display:flex; gap:14px; align-items:flex-start;">
                <div style="width:42px; height:42px; border-radius:999px; background:#ECFDF3; color:#065F46; display:flex; align-items:center; justify-content:center; font-size:20px; flex:0 0 auto;">
                    <i class="mdi mdi-bell-outline"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:4px; flex-wrap:wrap;">
                        <div>
                            <a href="{{ $actionUrl !== '#' ? $actionUrl : ($markReadUrlBase ? rtrim($markReadUrlBase, '/') . '/' . data_get($notification, 'id') . '/read' : '#') }}" style="text-decoration:none; color:var(--agri-text-heading); font-size:16px; font-weight: {{ $isUnread ? '800' : '700' }};">
                                {{ data_get($notification, 'title', 'Notification') }}
                            </a>
                            <div style="font-size:13px; color:var(--agri-text-muted); margin-top:2px;">{{ data_get($notification, 'created_at_human') ?: optional(data_get($notification, 'created_at'))?->diffForHumans() }}</div>
                        </div>
                        <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                            <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_', ' ', (string) data_get($notification, 'type', 'general'))) }}</span>
                            <span class="badge {{ $isUnread ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $isUnread ? 'Unread' : 'Read' }}</span>
                        </div>
                    </div>
                    <p style="margin:0 0 12px 0; color: {{ $isUnread ? 'var(--agri-text-heading)' : 'var(--agri-text-muted)' }}; font-size:14px; line-height:1.45;">{{ \Illuminate\Support\Str::limit((string) data_get($notification, 'message', ''), 160) }}</p>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        @if($actionUrl !== '#')
                            <a href="{{ $actionUrl }}" class="btn-agri btn-agri-primary" style="height:36px; display:inline-flex; align-items:center; font-size:13px;">Open</a>
                        @endif
                        @if($isUnread && $markReadUrlBase)
                            <form method="POST" action="{{ rtrim($markReadUrlBase, '/') . '/' . data_get($notification, 'id') . '/read' }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-agri btn-agri-outline" style="height:36px; font-size:13px;">Mark read</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card-agri" style="padding:52px 24px; text-align:center;">
            <div style="width:64px; height:64px; border-radius:999px; background:#F0FDF4; color:#16A34A; margin:0 auto 16px auto; display:flex; align-items:center; justify-content:center; font-size:32px;">
                <i class="mdi mdi-bell-check-outline"></i>
            </div>
            <h3 style="margin:0 0 8px 0; color:var(--agri-primary-dark); font-size:20px; font-weight:700;">You are all caught up!</h3>
            <p style="margin:0; color:var(--agri-text-muted); font-size:14px;">New notifications will appear here.</p>
        </div>
    @endforelse
</div>

<div style="margin-top:20px; display:flex; justify-content:center;">
    @if(method_exists($notifications, 'links') && $notifications->hasPages())
        {{ $notifications->links('pagination::bootstrap-5') }}
    @endif
</div>
