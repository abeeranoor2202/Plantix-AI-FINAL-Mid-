@php
    use App\Models\Notification;
    use App\Services\Notifications\NotificationCenterService;

    $user = Auth::user();
    $is_logged_in = Auth::check();
    $logout_route = route('logout');
    $profile_route = '#';
    $notificationFeed = collect();
    $notificationUnreadCount = 0;
    $notificationIndexUrl = '#';
    $notificationReadAllUrl = '#';
    $notificationClearAllUrl = null;
    $notificationFeedUrl = null;
    $notificationReadUrlBase = null;
    $notificationRole = 'user';
    
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
        $is_logged_in = true;
        $logout_route = route('admin.logout'); 
        $profile_route = route('admin.users.profile');
        $notificationRole = 'admin';
        $notificationIndexUrl = route('admin.notifications.index');
        $notificationReadAllUrl = route('admin.notifications.read-all');
        $notificationClearAllUrl = route('admin.notifications.clear-all');
        $notificationFeedUrl = route('admin.notifications.feed', ['limit' => 5, 'grouped' => 1]);
        $notificationReadUrlBase = route('admin.notifications.read', ['notification' => 0]);

        $center = app(NotificationCenterService::class);
        $notificationUnreadCount = $center->unreadCount($user);
        $notificationFeed = $center->latestForUser($user, 5);
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
        $is_logged_in = true;
        $logout_route = route('expert.logout'); // Assuming expert.logout exists
        $profile_route = route('expert.profile.show');
        $notificationRole = 'expert';
        $notificationIndexUrl = route('expert.notifications.index');
        $notificationReadAllUrl = route('expert.notifications.read-all');
        $notificationClearAllUrl = route('expert.notifications.clear-all');
        $notificationFeedUrl = route('expert.notifications.feed', ['limit' => 5, 'grouped' => 1]);
        $notificationReadUrlBase = route('expert.notifications.read', ['notification' => 0]);

        $expertId = $user->expert?->id;
        if ($expertId) {
            $center = app(NotificationCenterService::class);
            $notificationUnreadCount = $center->unreadCountForExpert($user->expert);
            $notificationFeed = $center->latestForExpert($user->expert, 5);
        }
    } elseif(Auth::guard('vendor')->check()){
        $user = Auth::guard('vendor')->user();
        $is_logged_in = true;
        $logout_route = route('vendor.logout');
        $profile_route = route('vendor.profile');
        $notificationRole = 'vendor';
        $notificationIndexUrl = route('vendor.notifications.index');
        $notificationReadAllUrl = route('vendor.notifications.read-all');
        $notificationClearAllUrl = route('vendor.notifications.clear-all');
        $notificationFeedUrl = route('vendor.notifications.feed', ['limit' => 5, 'grouped' => 1]);
        $notificationReadUrlBase = route('vendor.notifications.read', ['notification' => 0]);

        $center = app(NotificationCenterService::class);
        $notificationUnreadCount = $center->unreadCount($user);
        $notificationFeed = $center->latestForUser($user, 5);
    } elseif(Auth::check()) {
        // Default customer or generic web guard
        if (Route::has('account.profile')) {
            $profile_route = route('account.profile');
        }

        $notificationRole = 'user';
        $notificationIndexUrl = route('notifications.index');
        $notificationReadAllUrl = route('notifications.read-all');
        $notificationFeedUrl = route('notifications.feed', ['limit' => 5, 'grouped' => 1]);
        $notificationReadUrlBase = route('notifications.read', ['notification' => 0]);

        if ($user) {
            $center = app(NotificationCenterService::class);
            $notificationUnreadCount = $center->unreadCount($user);
            $notificationFeed = $center->latestForUser($user, 5);
        }
    }

    $roleLabel = session()->get('user_role', 'Explorer');
    if (Auth::guard('vendor')->check()) {
        $roleLabel = 'Vendor';
    } elseif (Auth::guard('admin')->check()) {
        $roleLabel = 'Admin';
    } elseif (Auth::guard('expert')->check()) {
        $roleLabel = 'Expert';
    }
@endphp

<div class="navbar-header" style="background: var(--agri-white) !important; border-bottom: 1px solid var(--agri-border);">
    <a class="navbar-brand admin-brand" href="{{ URL::to('/') }}">
        <div class="admin-brand-icon-wrap">
            <i class="mdi mdi-leaf admin-brand-icon" aria-hidden="true"></i>
        </div>
        <span class="admin-brand-name">Plantix AI</span>
    </a>
</div>

<div class="navbar-collapse" style="background: var(--agri-white) !important; padding: 0 25px;">
    <ul class="navbar-nav mr-auto mt-md-0">
        <li class="nav-item">
            <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)">
                <i class="mdi mdi-menu" style="font-size: 22px;" aria-hidden="true"></i>
            </a>
        </li>
        <li class="nav-item m-l-10">
            <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)">
                <i class="mdi mdi-menu" style="font-size: 20px;" aria-hidden="true"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav my-lg-0 align-items-center">
        <!-- AI Status Indicator -->
        <li class="nav-item m-r-20 hidden-sm-down">
            <div class="admin-status-pill">
                <div class="ai-pulse admin-status-dot"></div>
                <span>AI Engine Online</span>
            </div>
        </li>

        @if($is_logged_in)
            <li class="nav-item m-r-20 hidden-sm-down">
                <div class="admin-status-pill" style="gap: 10px;">
                    <i class="mdi mdi-shield-check" style="font-size: 16px; color: var(--agri-primary);"></i>
                    <span>Reputation {{ $platformReputation['score'] ?? 0 }} ({{ strtoupper($platformReputation['level'] ?? 'neutral') }})</span>
                </div>
            </li>
        @endif

        @if($is_logged_in)
            <li class="nav-item dropdown me-3" id="sharedNotificationBellRoot">
                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative; padding: 6px 10px;">
                    <i class="mdi mdi-bell-outline" style="font-size: 24px;"></i>
                    <span id="sharedNotificationBadge" class="badge rounded-pill bg-danger" style="position:absolute; top:0; right:0; min-width: 20px; {{ $notificationUnreadCount > 0 ? '' : 'display:none;' }}">{{ $notificationUnreadCount }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right scale-up" style="border-radius: var(--agri-radius-md); box-shadow: var(--agri-shadow-lg); border: 1px solid var(--agri-border); padding: 0; min-width: 360px; margin-top: 15px;">
                    <div style="padding: 12px 14px; border-bottom: 1px solid var(--agri-border); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight: 800; color: var(--agri-text-heading);">Notifications</div>
                            <div style="font-size: 12px; color: var(--agri-text-muted);">Latest updates</div>
                        </div>
                        <a href="{{ $notificationIndexUrl }}" style="font-size: 12px; font-weight: 700; color: var(--agri-primary); text-decoration:none;">View all</a>
                    </div>
                    <div id="sharedNotificationBellList" style="max-height: 360px; overflow-y: auto;">
                        @forelse($notificationFeed as $note)
                            <a href="{{ $note['action_url'] ?? $note['open_url'] ?? '#' }}" style="display:block; padding: 12px 14px; text-decoration:none; border-bottom: 1px solid var(--agri-border); background: {{ !empty($note['is_read']) ? 'var(--agri-white)' : '#F0FDF4' }};">
                                <div style="display:flex; justify-content:space-between; gap: 10px; margin-bottom:4px;">
                                    <div style="font-size: 13px; font-weight: {{ !empty($note['is_read']) ? '700' : '800' }}; color: var(--agri-text-heading);">{{ $note['title'] ?? 'Notification' }}</div>
                                    <div style="font-size: 11px; color: var(--agri-text-muted); white-space: nowrap;">{{ $note['created_at_human'] ?? '' }}</div>
                                </div>
                                <div style="font-size: 12px; color: var(--agri-text-muted);">{{ \Illuminate\Support\Str::limit($note['message'] ?? '', 72) }}</div>
                            </a>
                        @empty
                            <div style="padding: 18px 14px; text-align:center; color: var(--agri-text-muted); font-size: 13px;">
                                You are all caught up.
                            </div>
                        @endforelse
                    </div>
                </div>
            </li>
        @endif

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 0;">
                <div class="admin-profile-chip">
                    <div class="admin-profile-meta hidden-sm-down">
                        <p>{{ $is_logged_in ? $user->name : 'Guest' }}</p>
                        <p>{{ $roleLabel }}</p>
                    </div>
                    <img src="{{ asset('/images/users/user-new.png') }}" alt="user" class="profile-pic admin-profile-avatar">
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right scale-up" style="border-radius: var(--agri-radius-md); box-shadow: var(--agri-shadow-lg); border: 1px solid var(--agri-border); padding: 10px; min-width: 200px; margin-top: 15px;">
                <ul class="dropdown-user" style="padding: 0; list-style: none;">
                    <li>
                        <a href="{{ $profile_route }}" class="nav-link-agri">
                            <i class="mdi mdi-account-outline"></i> {!! trans('lang.user_profile') !!}
                        </a>
                    </li>
                    <li role="separator" class="divider" style="height: 1px; background: var(--agri-border); margin: 8px 0;"></li>
                    @if($is_logged_in)
                        <li>
                            <a href="#" class="nav-link-agri" style="color: var(--agri-error);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="mdi mdi-power"></i> {{ __('Logout') }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ url('signin') }}" class="nav-link-agri">
                                <i class="mdi mdi-login"></i> Sign In
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
    <form id="logout-form" action="{{ $logout_route }}" method="POST" class="d-none">@csrf</form>
</div>

<script>
    (function () {
        const feedUrl = "{{ $notificationFeedUrl ?? '' }}";
        const badgeId = 'sharedNotificationBadge';
        const listId = 'sharedNotificationBellList';

        function renderBellItems(items) {
            const root = document.getElementById(listId);
            if (!root) {
                return;
            }

            const esc = function (value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            if (!items.length) {
                root.innerHTML = '<div style="padding: 18px 14px; text-align:center; color: var(--agri-text-muted); font-size: 13px;">You are all caught up.</div>';
                return;
            }

            root.innerHTML = items.map(function (item) {
                const weight = item.is_read ? '700' : '800';
                const bg = item.is_read ? 'var(--agri-white)' : '#F0FDF4';
                const text = esc((item.message || '').slice(0, 72));
                const title = esc(item.title || 'Notification');
                const time = esc(item.created_at_human || '');
                const openUrl = esc(item.open_url || '#');

                return ''
                    + '<a href="' + openUrl + '" style="display:block; padding: 12px 14px; text-decoration:none; border-bottom: 1px solid var(--agri-border); background:' + bg + ';">'
                    + '<div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:4px;">'
                    + '<div style="font-size:13px; font-weight:' + weight + '; color: var(--agri-text-heading);">' + title + '</div>'
                    + '<div style="font-size:11px; color: var(--agri-text-muted); white-space:nowrap;">' + time + '</div>'
                    + '</div>'
                    + '<div style="font-size:12px; color: var(--agri-text-muted);">' + text + '</div>'
                    + '</a>';
            }).join('');
        }

        function updateBadge(count) {
            const badge = document.getElementById(badgeId);
            if (!badge) {
                return;
            }

            if (count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = String(count);
            } else {
                badge.style.display = 'none';
            }
        }

        async function syncBell() {
            if (!feedUrl) {
                return;
            }

            try {
                const response = await fetch(feedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                updateBadge(Number(data.count || 0));
                renderBellItems(Array.isArray(data.items) ? data.items : []);
            } catch (error) {
                // Silent fallback on temporary network errors.
            }
        }

        setInterval(syncBell, 15000);
        syncBell();
    })();
</script>
