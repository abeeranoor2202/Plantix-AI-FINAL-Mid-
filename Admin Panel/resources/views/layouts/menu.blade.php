@php
    $user = Auth::user();
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
    }
@endphp

@php $cap = 'text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin: 14px 0 6px 0; padding-left: 10px;'; @endphp

<nav class="sidebar-nav sidebar-agri">
    <div style="padding: 12px 8px;">
        <ul id="sidebarnav" style="list-style: none; padding: 0; margin: 0;">

            {{-- ── MAIN MENU ─────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">MAIN MENU</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/dashboard*') || Request::is('admin') ? 'active' : '' }}" href="{!! route('admin.dashboard') !!}">
                    <i class="mdi mdi-home" style="font-size: 20px;"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- ── PEOPLE ────────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">PEOPLE</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/users*') ? 'active' : '' }}" href="{!! route('admin.users') !!}">
                    <i class="mdi mdi-account-multiple" style="font-size: 20px;"></i>
                    <span>Customers</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/vendors*') ? 'active' : '' }}" href="{!! route('admin.vendors') !!}">
                    <i class="mdi mdi-store" style="font-size: 20px;"></i>
                    <span>Vendors</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/experts*') ? 'active' : '' }}" href="{!! route('admin.experts.index') !!}">
                    <i class="mdi mdi-account-star" style="font-size: 20px;"></i>
                    <span>Experts</span>
                </a>
            </li>

            {{-- ── CATALOGUE ─────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">CATALOGUE</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/products*') ? 'active' : '' }}" href="{!! route('admin.products.index') !!}">
                    <i class="mdi mdi-cart" style="font-size: 20px;"></i>
                    <span>Products</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/categories*') ? 'active' : '' }}" href="{!! route('admin.categories') !!}">
                    <i class="mdi mdi-tag-multiple" style="font-size: 20px;"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/attributes*') ? 'active' : '' }}" href="{!! route('admin.attributes') !!}">
                    <i class="mdi mdi-format-list-bulleted" style="font-size: 20px;"></i>
                    <span>Attributes</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/coupons*') ? 'active' : '' }}" href="{!! route('admin.coupons') !!}">
                    <i class="mdi mdi-ticket-percent" style="font-size: 20px;"></i>
                    <span>Coupons</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/reviews*') ? 'active' : '' }}" href="{!! route('admin.reviews') !!}">
                    <i class="mdi mdi-star" style="font-size: 20px;"></i>
                    <span>Reviews</span>
                </a>
            </li>

            {{-- ── ORDERS & FULFILMENT ───────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">ORDERS & FULFILMENT</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/orders*') ? 'active' : '' }}" href="{!! route('admin.orders.index') !!}">
                    <i class="mdi mdi-library-books" style="font-size: 20px;"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/returns*') ? 'active' : '' }}" href="{!! route('admin.returns.index') !!}">
                    <i class="mdi mdi-keyboard-return" style="font-size: 20px;"></i>
                    <span>Returns</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/stock*') ? 'active' : '' }}" href="{!! route('admin.stock.index') !!}">
                    <i class="mdi mdi-package-variant" style="font-size: 20px;"></i>
                    <span>Stock</span>
                </a>
            </li>

            {{-- ── APPOINTMENTS ──────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">APPOINTMENTS</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/appointments*') ? 'active' : '' }}" href="{!! route('admin.appointments.index') !!}">
                    <i class="mdi mdi-calendar-clock" style="font-size: 20px;"></i>
                    <span>Appointments</span>
                </a>
            </li>

            {{-- ── COMMUNITY ─────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">COMMUNITY</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/forum*') ? 'active' : '' }}" href="{!! route('admin.forum.index') !!}">
                    <i class="mdi mdi-forum" style="font-size: 20px;"></i>
                    <span>Forum</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/forum/flags*') ? 'active' : '' }}" href="{!! route('admin.forum.flags.index') !!}">
                    <i class="mdi mdi-flag" style="font-size: 20px;"></i>
                    <span>Forum Flags</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/forum/categories*') ? 'active' : '' }}" href="{!! route('admin.forum.categories.index') !!}">
                    <i class="mdi mdi-shape" style="font-size: 20px;"></i>
                    <span>Forum Categories</span>
                </a>
            </li>

            {{-- ── AI MODULES ────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">AI MODULES</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules') ? 'active' : '' }}" href="{!! route('admin.ai.dashboard') !!}">
                    <i class="mdi mdi-brain" style="font-size: 20px;"></i>
                    <span>AI Overview</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules/crop-recommendations*') ? 'active' : '' }}" href="{!! route('admin.ai.crop-recommendations') !!}">
                    <i class="mdi mdi-sprout" style="font-size: 20px;"></i>
                    <span>Crop Recommendations</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules/disease-reports*') ? 'active' : '' }}" href="{!! route('admin.ai.disease-reports') !!}">
                    <i class="mdi mdi-bug" style="font-size: 20px;"></i>
                    <span>Disease Reports</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules/fertilizer*') ? 'active' : '' }}" href="{!! route('admin.ai.fertilizer') !!}">
                    <i class="mdi mdi-flask" style="font-size: 20px;"></i>
                    <span>Fertilizer Recs</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules/crop-plans*') ? 'active' : '' }}" href="{!! route('admin.ai.crop-plans') !!}">
                    <i class="mdi mdi-calendar-text" style="font-size: 20px;"></i>
                    <span>Crop Plans</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/ai-modules/seasonal-data*') ? 'active' : '' }}" href="{!! route('admin.ai.seasonal-data') !!}">
                    <i class="mdi mdi-weather-sunny" style="font-size: 20px;"></i>
                    <span>Seasonal Data</span>
                </a>
            </li>

            {{-- ── NOTIFICATIONS ─────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">NOTIFICATIONS</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/notification') ? 'active' : '' }}" href="{!! route('admin.notification') !!}">
                    <i class="mdi mdi-bell" style="font-size: 20px;"></i>
                    <span>Push Notifications</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/notifications/broadcast*') ? 'active' : '' }}" href="{!! route('admin.notifications.broadcast.history') !!}">
                    <i class="mdi mdi-broadcast" style="font-size: 20px;"></i>
                    <span>Broadcasts</span>
                </a>
            </li>

            {{-- ── ACCESS CONTROL ────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">ACCESS CONTROL</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/role*') ? 'active' : '' }}" href="{!! route('admin.role.index') !!}">
                    <i class="mdi mdi-shield-key" style="font-size: 20px;"></i>
                    <span>Roles</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/permissions*') ? 'active' : '' }}" href="{!! route('admin.permissions.index') !!}">
                    <i class="mdi mdi-lock-open" style="font-size: 20px;"></i>
                    <span>Permissions</span>
                </a>
            </li>

            {{-- ── SETTINGS ──────────────────────────────────────────── --}}
            <li class="nav-small-cap" style="{{ $cap }}">SETTINGS</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/settings/payment/stripe*') ? 'active' : '' }}" href="{!! route('admin.payment.stripe') !!}">
                    <i class="mdi mdi-credit-card" style="font-size: 20px;"></i>
                    <span>Stripe Settings</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/settings/payment/cod*') ? 'active' : '' }}" href="{!! route('admin.payment.cod') !!}">
                    <i class="mdi mdi-cash" style="font-size: 20px;"></i>
                    <span>COD Settings</span>
                </a>
            </li>

        </ul>
    </div>
</nav>
