@php
    $user = Auth::user();
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
    }
@endphp

<nav class="sidebar-nav sidebar-agri">
    <div style="padding: 10px 10px 20px 10px;">
        <p style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin-bottom: 15px; padding-left: 15px;">Main Menu</p>
        
        <ul id="sidebarnav" style="list-style: none; padding: 0;">
            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/dashboard*') ? 'active' : '' }}" href="{!! route('admin.dashboard') !!}">
                    <i class="mdi mdi-home" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.dashboard')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/map*') ? 'active' : '' }}" href="{!! route('admin.map') !!}">
                    <i class="mdi mdi-home-map-marker" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.god_eye')}}</span>
                </a>
            </li>

            <p style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin: 25px 0 15px 0; padding-left: 15px;">Management</p>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/users*') ? 'active' : '' }}" href="{!! route('admin.users') !!}">
                    <i class="mdi mdi-account-multiple" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.user_customer')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/vendors*') ? 'active' : '' }}" href="{!! route('admin.vendors') !!}">
                    <i class="mdi mdi-account-card-details" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.owner_vendor')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/products*') ? 'active' : '' }}" href="{!! route('admin.products.index') !!}">
                    <i class="mdi mdi-cart" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.item_plural')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/orders*') ? 'active' : '' }}" href="{!! route('admin.orders.index') !!}">
                    <i class="mdi mdi-library-books" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.order_plural')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/appointments*') ? 'active' : '' }}" href="{!! route('admin.appointments.index') !!}">
                    <i class="mdi mdi-calendar-clock" style="font-size: 20px;"></i>
                    <span class="hide-menu">Appointments</span>
                </a>
            </li>

            <p style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin: 25px 0 15px 0; padding-left: 15px;">Settings</p>

            <li style="margin-bottom: 5px;">
                <a class="nav-link-agri {{ Request::is('admin/settings*') ? 'active' : '' }}" href="{!! route('admin.settings.app.globals') !!}">
                    <i class="mdi mdi-settings" style="font-size: 20px;"></i>
                    <span class="hide-menu">{{trans('lang.app_setting')}}</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
