
<div class="card {{
             Request::is('settings/app/*') ||
             Request::is('settings/app/social*') ||
             Request::is('settings/payment/*')
 ? '' : 'collapsed-card' }}">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.app_setting_globals')}}</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-widget="collapse"><i class="fa {{
             Request::is('settings/app/*') ||
             Request::is('settings/payment*')
             ? 'fa-minus' : 'fa-plus' }}"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{!! route('admin.settings.app.globals') !!}" class="nav-link {{  Request::is('settings/app/globals*') ? 'selected' : '' }}">
                    <i class="fa fa-cog"></i> {{trans('lang.app_setting_globals')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! route('admin.settings.app.globals') !!}" class="nav-link {{  Request::is('settings/app/social*') ? 'selected' : '' }}">
                    <i class="fa fa-globe"></i> {{trans('lang.app_setting_social')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! route('admin.payment.stripe') !!}" class="nav-link {{  Request::is('settings/payment*') ? 'selected' : '' }}">
                    <i class="fa fa-credit-card"></i> {{trans('lang.app_setting_payment')}}
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! route('admin.settings.app.notifications') !!}" class="nav-link {{  Request::is('settings/app/notifications*') || Request::is('notificationTypes*') ? 'selected' : '' }}">
                    <i class="fa fa-bell"></i> {{trans('lang.app_setting_notifications')}}
                </a>
            </li>

        </ul>
    </div>
</div>

