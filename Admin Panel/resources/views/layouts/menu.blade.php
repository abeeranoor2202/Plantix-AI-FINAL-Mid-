@php
    $user = Auth::user();
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
    }

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'mdi-view-dashboard-outline', 'active' => Request::is('admin/dashboard*') || Request::is('admin'), 'route' => route('admin.dashboard')],
        ['label' => 'Customers', 'icon' => 'mdi-account-multiple-outline', 'active' => Request::is('admin/users*'), 'route' => route('admin.users')],
        ['label' => 'Vendors', 'icon' => 'mdi-store-outline', 'active' => Request::is('admin/vendors*'), 'route' => route('admin.vendors')],
        ['label' => 'Experts', 'icon' => 'mdi-account-tie-outline', 'active' => Request::is('admin/experts*'), 'route' => route('admin.experts.index')],
        ['label' => 'Products', 'icon' => 'mdi-cart-outline', 'active' => Request::is('admin/products*'), 'route' => route('admin.products.index')],
        ['label' => 'Categories', 'icon' => 'mdi-shape-outline', 'active' => Request::is('admin/categories*'), 'route' => route('admin.categories')],
        ['label' => 'Attributes', 'icon' => 'mdi-format-list-bulleted-square', 'active' => Request::is('admin/attributes*'), 'route' => route('admin.attributes')],
        ['label' => 'Coupons', 'icon' => 'mdi-ticket-percent-outline', 'active' => Request::is('admin/coupons*'), 'route' => route('admin.coupons')],
        ['label' => 'Reviews', 'icon' => 'mdi-star-outline', 'active' => Request::is('admin/reviews*'), 'route' => route('admin.reviews')],
        ['label' => 'Orders', 'icon' => 'mdi-receipt-text-outline', 'active' => Request::is('admin/orders*'), 'route' => route('admin.orders.index')],
        ['label' => 'Returns', 'icon' => 'mdi-keyboard-return', 'active' => Request::is('admin/returns*'), 'route' => route('admin.returns.index')],
        ['label' => 'Stock', 'icon' => 'mdi-package-variant-closed', 'active' => Request::is('admin/stock*'), 'route' => route('admin.stock.index')],
        ['label' => 'Appointments', 'icon' => 'mdi-calendar-clock', 'active' => Request::is('admin/appointments*'), 'route' => route('admin.appointments.index')],
        ['label' => 'Forum', 'icon' => 'mdi-forum-outline', 'active' => Request::is('admin/forum') || Request::is('admin/forum/*'), 'route' => route('admin.forum.index')],
        ['label' => 'Forum Flags', 'icon' => 'mdi-flag-outline', 'active' => Request::is('admin/forum/flags*'), 'route' => route('admin.forum.flags.index')],
        ['label' => 'Forum Categories', 'icon' => 'mdi-view-grid-outline', 'active' => Request::is('admin/forum/categories*'), 'route' => route('admin.forum.categories.index')],
        ['label' => 'AI Overview', 'icon' => 'mdi-brain', 'active' => Request::is('admin/ai-modules') || Request::is('admin/ai-modules/'), 'route' => route('admin.ai.dashboard')],
        ['label' => 'Crop Recommendations', 'icon' => 'mdi-sprout', 'active' => Request::is('admin/ai-modules/crop-recommendations*'), 'route' => route('admin.ai.crop-recommendations')],
        ['label' => 'Disease Reports', 'icon' => 'mdi-bug-outline', 'active' => Request::is('admin/ai-modules/disease-reports*'), 'route' => route('admin.ai.disease-reports')],
        ['label' => 'Fertilizer Recs', 'icon' => 'mdi-flask-outline', 'active' => Request::is('admin/ai-modules/fertilizer*'), 'route' => route('admin.ai.fertilizer')],
        ['label' => 'Crop Plans', 'icon' => 'mdi-calendar-text-outline', 'active' => Request::is('admin/ai-modules/crop-plans*'), 'route' => route('admin.ai.crop-plans')],
        ['label' => 'Seasonal Data', 'icon' => 'mdi-weather-sunny', 'active' => Request::is('admin/ai-modules/seasonal-data*'), 'route' => route('admin.ai.seasonal-data')],
        ['label' => 'Push Notifications', 'icon' => 'mdi-bell-outline', 'active' => Request::is('admin/notification*'), 'route' => route('admin.notification.send')],
        ['label' => 'Roles', 'icon' => 'mdi-shield-key-outline', 'active' => Request::is('admin/role*'), 'route' => route('admin.role.index')],
        ['label' => 'Permissions', 'icon' => 'mdi-lock-open-variant-outline', 'active' => Request::is('admin/permissions*'), 'route' => route('admin.permissions.index')],
        ['label' => 'Stripe Settings', 'icon' => 'mdi-credit-card-outline', 'active' => Request::is('admin/settings/payment/stripe*'), 'route' => route('admin.payment.stripe')],
        ['label' => 'COD Settings', 'icon' => 'mdi-cash', 'active' => Request::is('admin/settings/payment/cod*'), 'route' => route('admin.payment.cod')],
    ];
@endphp

<nav class="sidebar-nav sidebar-agri">
    <div class="admin-side-nav-wrap">
        <ul id="sidebarnav" class="admin-side-nav-list">
            @foreach($navItems as $item)
                <li class="admin-side-nav-item">
                    <a class="nav-link-agri {{ $item['active'] ? 'active' : '' }}" href="{{ $item['route'] }}">
                        <i class="mdi {{ $item['icon'] }} admin-side-nav-icon" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
