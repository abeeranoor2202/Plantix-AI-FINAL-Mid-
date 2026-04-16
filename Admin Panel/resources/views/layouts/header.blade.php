@php
    $user = Auth::user();
    $is_logged_in = Auth::check();
    $logout_route = route('logout');
    $profile_route = '#';
    
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
