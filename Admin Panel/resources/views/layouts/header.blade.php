@php
    use App\Models\ExpertNotificationLog;

    $user = Auth::user();
    $is_logged_in = Auth::check();
    $logout_route = route('logout');
    $profile_route = '#';
    $expertNotifications = collect();
    $expertUnreadCount = 0;
    
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
        $is_logged_in = true;
        $logout_route = route('admin.logout'); 
        $profile_route = route('admin.users.profile');
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
        $is_logged_in = true;
        $logout_route = route('expert.logout'); // Assuming expert.logout exists
        $profile_route = route('expert.profile.show');

        $expertId = $user->expert?->id;
        if ($expertId) {
            $expertUnreadCount = ExpertNotificationLog::query()
                ->where('expert_id', $expertId)
                ->where('is_read', false)
                ->count();

            $expertNotifications = ExpertNotificationLog::query()
                ->where('expert_id', $expertId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }
    } elseif(Auth::guard('vendor')->check()){
        $user = Auth::guard('vendor')->user();
        $is_logged_in = true;
        $logout_route = route('vendor.logout');
        $profile_route = route('vendor.profile');
    } elseif(Auth::check()) {
        // Default customer or generic web guard
        if (Route::has('account.profile')) {
            $profile_route = route('account.profile');
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

        @if(Auth::guard('expert')->check())
            <li class="nav-item dropdown me-3" id="expertNotificationBellRoot">
                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative; padding: 6px 10px;">
                    <i class="mdi mdi-bell-outline" style="font-size: 24px;"></i>
                    <span id="expertNotificationBadge" class="badge rounded-pill bg-danger" style="position:absolute; top:0; right:0; min-width: 20px; {{ $expertUnreadCount > 0 ? '' : 'display:none;' }}">{{ $expertUnreadCount }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right scale-up" style="border-radius: var(--agri-radius-md); box-shadow: var(--agri-shadow-lg); border: 1px solid var(--agri-border); padding: 0; min-width: 360px; margin-top: 15px;">
                    <div style="padding: 12px 14px; border-bottom: 1px solid var(--agri-border); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight: 800; color: var(--agri-text-heading);">Notifications</div>
                            <div style="font-size: 12px; color: var(--agri-text-muted);">Latest updates</div>
                        </div>
                        <a href="{{ route('expert.notifications.index') }}" style="font-size: 12px; font-weight: 700; color: var(--agri-primary); text-decoration:none;">View all</a>
                    </div>
                    <div id="expertNotificationBellList" style="max-height: 360px; overflow-y: auto;">
                        @forelse($expertNotifications as $note)
                            <a href="{{ route('expert.notifications.open', $note) }}" style="display:block; padding: 12px 14px; text-decoration:none; border-bottom: 1px solid var(--agri-border); background: {{ $note->is_read ? 'var(--agri-white)' : '#F0FDF4' }};">
                                <div style="display:flex; justify-content:space-between; gap: 10px; margin-bottom:4px;">
                                    <div style="font-size: 13px; font-weight: {{ $note->is_read ? '700' : '800' }}; color: var(--agri-text-heading);">{{ $note->title }}</div>
                                    <div style="font-size: 11px; color: var(--agri-text-muted); white-space: nowrap;">{{ $note->created_at?->diffForHumans() }}</div>
                                </div>
                                <div style="font-size: 12px; color: var(--agri-text-muted);">{{ \Illuminate\Support\Str::limit($note->message ?? $note->body ?? '', 72) }}</div>
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

@if(Auth::guard('expert')->check())
<script>
    (function () {
        const feedUrl = "{{ route('expert.notifications.feed', ['limit' => 5]) }}";

        function renderBellItems(items) {
            const root = document.getElementById('expertNotificationBellList');
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
            const badge = document.getElementById('expertNotificationBadge');
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
    })();
</script>
@endif
