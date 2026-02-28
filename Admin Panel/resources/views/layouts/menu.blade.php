@php
    $user = Auth::user();
    if(Auth::guard('admin')->check()){
        $user = Auth::guard('admin')->user();
    } elseif(Auth::guard('expert')->check()){
        $user = Auth::guard('expert')->user();
    }
@endphp

<nav class="sidebar-nav sidebar-agri">
    <div style="padding: 12px 8px;">
        <ul id="sidebarnav" style="list-style: none; padding: 0; margin: 0;">
            
            <li class="nav-small-cap" style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin-bottom: 8px; padding-left: 10px;">MAIN MENU</li>
            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/dashboard*') ? 'active' : '' }}" href="{!! route('admin.dashboard') !!}">
                    <i class="mdi mdi-home" style="font-size: 20px;"></i>
                    <span>{{trans('lang.dashboard')}}</span>
                </a>
            </li>

            <li class="nav-small-cap" style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin: 12px 0 8px 0; padding-left: 10px;">MANAGEMENT</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/users*') ? 'active' : '' }}" href="{!! route('admin.users') !!}">
                    <i class="mdi mdi-account-multiple" style="font-size: 20px;"></i>
                    <span>{{trans('lang.user_customer')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/vendors*') ? 'active' : '' }}" href="{!! route('admin.vendors') !!}">
                    <i class="mdi mdi-account-card-details" style="font-size: 20px;"></i>
                    <span>{{trans('lang.owner_vendor')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/products*') ? 'active' : '' }}" href="{!! route('admin.products.index') !!}">
                    <i class="mdi mdi-cart" style="font-size: 20px;"></i>
                    <span>{{trans('lang.item_plural')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/orders*') ? 'active' : '' }}" href="{!! route('admin.orders.index') !!}">
                    <i class="mdi mdi-library-books" style="font-size: 20px;"></i>
                    <span>{{trans('lang.order_plural')}}</span>
                </a>
            </li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/appointments*') ? 'active' : '' }}" href="{!! route('admin.appointments.index') !!}">
                    <i class="mdi mdi-calendar-clock" style="font-size: 20px;"></i>
                    <span>Appointments</span>
                </a>
            </li>

            <li class="nav-small-cap" style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); letter-spacing: 1px; margin: 12px 0 8px 0; padding-left: 10px;">SETTINGS</li>

            <li style="margin-bottom: 2px;">
                <a class="nav-link-agri {{ Request::is('admin/settings*') ? 'active' : '' }}" href="{!! route('admin.settings.app.globals') !!}">
                    <i class="mdi mdi-settings" style="font-size: 20px;"></i>
                    <span>{{trans('lang.app_setting')}}</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
