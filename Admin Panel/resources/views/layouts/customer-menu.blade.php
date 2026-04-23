@php
    $menuRoute = static function (string $name, string $fallback = '#'): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;
    };

    $items = [
        ['label' => 'Dashboard', 'icon' => 'fas fa-gauge-high', 'route' => $menuRoute('customer.dashboard', url('/dashboard')), 'active' => request()->routeIs('customer.dashboard')],
        ['label' => 'Orders', 'icon' => 'fas fa-box', 'route' => $menuRoute('orders', url('/orders')), 'active' => request()->routeIs('orders*') || request()->routeIs('order.*')],
        ['label' => 'Appointments', 'icon' => 'fas fa-calendar-check', 'route' => $menuRoute('appointments', url('/appointments')), 'active' => request()->routeIs('appointments*') || request()->routeIs('appointment.*')],
        ['label' => 'Forum', 'icon' => 'fas fa-comments', 'route' => $menuRoute('forum', url('/forum')), 'active' => request()->routeIs('forum*')],
        ['label' => 'Experts', 'icon' => 'fas fa-user-tie', 'route' => $menuRoute('experts.index', url('/experts')), 'active' => request()->routeIs('experts.*')],
        ['label' => 'Notifications', 'icon' => 'fas fa-bell', 'route' => $menuRoute('notifications.index', url('/notifications')), 'active' => request()->routeIs('notifications.*')],
        ['label' => 'Account', 'icon' => 'fas fa-user', 'route' => $menuRoute('account.profile', url('/account/profile')), 'active' => request()->routeIs('account.profile')],
    ];
@endphp

<nav aria-label="Customer Navigation">
    <span class="role-nav-title">Navigation</span>
    @foreach($items as $item)
        <a href="{{ $item['route'] }}" class="role-nav-link {{ $item['active'] ? 'active' : '' }}">
            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>