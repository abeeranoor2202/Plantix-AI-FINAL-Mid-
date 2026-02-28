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
    } elseif(Auth::check()) {
        // Default customer or generic web guard
        if (Route::has('account.profile')) {
            $profile_route = route('account.profile');
        }
    }
@endphp

<div class="navbar-header" style="background: var(--agri-white) !important; border-bottom: 1px solid var(--agri-border);">
    <a class="navbar-brand" href="{{ URL::to('/') }}" style="display: flex; align-items: center; padding: 15px 25px;">
        <div style="background: var(--agri-primary-light); padding: 8px; border-radius: var(--agri-radius-sm); margin-right: 12px; display: flex; align-items: center;">
            <i class="mdi mdi-leaf" style="color: var(--agri-primary); font-size: 24px;"></i> 
        </div>
        <span style="font-size: 20px; font-weight: 700; color: var(--agri-primary-dark); font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">Plantix AI</span>
    </a>
</div>

<div class="navbar-collapse" style="background: var(--agri-white) !important; padding: 0 25px;">
    <ul class="navbar-nav mr-auto mt-md-0">
        <li class="nav-item">
            <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)">
                <i class="mdi mdi-menu" style="font-size: 22px;"></i>
            </a>
        </li>
        <li class="nav-item m-l-10">
            <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)">
                <i class="ti-menu" style="font-size: 20px;"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav my-lg-0 align-items-center">
        <!-- AI Status Indicator -->
        <li class="nav-item m-r-20 hidden-sm-down">
            <div style="display: flex; align-items: center; background: var(--agri-primary-light); padding: 6px 14px; border-radius: 20px;">
                <div class="ai-pulse" style="width: 8px; height: 8px; background: var(--agri-primary); border-radius: 50%; margin-right: 8px;"></div>
                <span style="font-size: 13px; font-weight: 600; color: var(--agri-primary-dark);">AI Engine Online</span>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="text-align: right;" class="hidden-sm-down">
                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">{{ $is_logged_in ? $user->name : 'Guest' }}</p>
                        <p style="margin: 0; font-size: 12px; color: var(--agri-text-muted);">{{ session()->get('user_role', 'Explorer') }}</p>
                    </div>
                    <img src="{{ asset('/images/users/user-new.png') }}" alt="user" class="profile-pic" style="width: 40px; height: 40px; border-radius: 12px; border: 2px solid var(--agri-primary-light); padding: 2px;">
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right scale-up" style="border-radius: var(--agri-radius-md); box-shadow: var(--agri-shadow-lg); border: 1px solid var(--agri-border); padding: 10px; min-width: 200px; margin-top: 15px;">
                <ul class="dropdown-user" style="padding: 0; list-style: none;">
                    <li>
                        <a href="{{ $profile_route }}" class="nav-link-agri">
                            <i class="ti-user"></i> {!! trans('lang.user_profile') !!}
                        </a>
                    </li>
                    <li role="separator" class="divider" style="height: 1px; background: var(--agri-border); margin: 8px 0;"></li>
                    @if($is_logged_in)
                        <li>
                            <a href="#" class="nav-link-agri" style="color: var(--agri-error);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-power-off"></i> {{ __('Logout') }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ url('signin') }}" class="nav-link-agri">
                                <i class="ti-user"></i> Sign In
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
    <form id="logout-form" action="{{ $logout_route }}" method="POST" class="d-none">@csrf</form>
</div>
