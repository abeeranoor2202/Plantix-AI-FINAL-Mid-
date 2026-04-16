@php
    $unread = 0;
    if (Auth::guard('expert')->check()) {
        $expert = Auth::guard('expert')->user()->expert;
        $unread = $expert ? $expert->notificationLogs()->where('is_read', false)->count() : 0;
    }

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'mdi-view-dashboard-outline', 'active' => request()->routeIs('expert.dashboard'), 'route' => route('expert.dashboard')],
        ['label' => 'Appointments', 'icon' => 'mdi-calendar-clock', 'active' => request()->routeIs('expert.appointments.*'), 'route' => route('expert.appointments.index'), 'section' => 'Consultations'],
        ['label' => 'Forum', 'icon' => 'mdi-forum-outline', 'active' => request()->routeIs('expert.forum.*'), 'route' => route('expert.forum.index'), 'section' => 'Community'],
        ['label' => 'Notifications', 'icon' => 'mdi-bell-outline', 'active' => request()->routeIs('expert.notifications.*'), 'route' => route('expert.notifications.index'), 'section' => 'Account', 'badge' => $unread],
        ['label' => 'Payouts', 'icon' => 'mdi-wallet-outline', 'active' => request()->routeIs('expert.payouts.*'), 'route' => route('expert.payouts.index')],
        ['label' => 'My Profile', 'icon' => 'mdi-account-circle-outline', 'active' => request()->routeIs('expert.profile.*'), 'route' => route('expert.profile.show')],
    ];
@endphp

<nav class="sidebar-nav sidebar-agri">
    <div class="admin-side-nav-wrap">
        <ul id="sidebarnav" class="admin-side-nav-list">
            @php $currentSection = null; @endphp
            @foreach($navItems as $item)
                @if(isset($item['section']) && $item['section'] !== $currentSection)
                    @php $currentSection = $item['section']; @endphp
                    <li class="admin-side-nav-item" style="padding: 10px 16px 4px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--agri-text-muted);">{{ $currentSection }}</span>
                    </li>
                @endif

                <li class="admin-side-nav-item">
                    <a class="nav-link-agri {{ $item['active'] ? 'active' : '' }}" href="{{ $item['route'] }}">
                        <i class="mdi {{ $item['icon'] }} admin-side-nav-icon" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                        @if(!empty($item['badge']))
                            <span class="badge rounded-pill bg-danger" style="margin-left: auto; font-size: 10px;">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach

            <li class="admin-side-nav-item" style="margin-top: 12px;">
                <form action="{{ route('expert.logout') }}" method="POST" style="padding: 0 12px;">
                    @csrf
                    <button type="submit" class="btn-agri" style="width: 100%; background: #fef2f2; color: var(--agri-error); border: 1px solid #fecaca;">
                        <i class="mdi mdi-logout me-2"></i>Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
