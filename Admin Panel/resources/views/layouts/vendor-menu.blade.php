@php
    $navItems = [
        ['label' => 'Dashboard',      'icon' => 'mdi-view-dashboard-outline',      'active' => Request::is('vendor/dashboard*') || Request::is('vendor'), 'route' => route('vendor.dashboard')],
        ['label' => 'Products',       'icon' => 'mdi-package-variant-closed',       'active' => Request::is('vendor/products*'),      'route' => route('vendor.products.index')],
        ['label' => 'Inventory',      'icon' => 'mdi-warehouse',                    'active' => Request::is('vendor/inventory*'),      'route' => route('vendor.inventory.index')],
        ['label' => 'Orders',         'icon' => 'mdi-cart-outline',                 'active' => Request::is('vendor/orders*'),         'route' => route('vendor.orders.index')],
        ['label' => 'Returns',        'icon' => 'mdi-keyboard-return',              'active' => Request::is('vendor/returns*'),        'route' => route('vendor.returns.index')],
        ['label' => 'Return Reasons', 'icon' => 'mdi-format-list-bulleted-square',  'active' => Request::is('vendor/return-reasons*'), 'route' => route('vendor.return-reasons.index')],
        ['label' => 'Coupons',        'icon' => 'mdi-ticket-percent-outline',       'active' => Request::is('vendor/coupons*'),        'route' => route('vendor.coupons.index')],
        ['label' => 'Categories',     'icon' => 'mdi-shape-outline',                'active' => Request::is('vendor/categories*'),     'route' => route('vendor.categories.index')],
        ['label' => 'Attributes',     'icon' => 'mdi-format-list-bulleted-square',  'active' => Request::is('vendor/attributes*'),     'route' => route('vendor.attributes.index')],
        ['label' => 'Payouts',        'icon' => 'mdi-cash-multiple',                'active' => Request::is('vendor/payouts*'),        'route' => route('vendor.payouts.index')],
        ['label' => 'Profile',        'icon' => 'mdi-account-outline',              'active' => Request::is('vendor/profile*'),        'route' => route('vendor.profile')],
    ];
@endphp

<nav class="sidebar-nav sidebar-agri">
    <div class="admin-side-nav-wrap">
        <ul id="sidebarnav" class="admin-side-nav-list">
            @foreach($navItems as $item)
                <li class="admin-side-nav-item">
                    <x-sidebar-item
                        :icon="$item['icon']"
                        :label="$item['label']"
                        :route="$item['route']"
                        :active="$item['active']"
                    />
                </li>
            @endforeach
        </ul>
    </div>
</nav>
