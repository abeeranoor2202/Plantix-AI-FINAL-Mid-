<header class="agri-header bg-white" style="box-shadow: var(--agri-shadow-sm); position: sticky; top: 0; z-index: 1000;">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container-fluid px-4 px-lg-5 d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Plantix-AI Logo" style="height: 40px; border-radius: 8px;">
            </a>

            <!-- Mobile Toggle -->
            <button type="button" class="navbar-toggler border-0 d-lg-none" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <i class="fas fa-bars text-dark" style="font-size: 24px;"></i>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse justify-content-center" id="navbar-menu">
                <ul class="navbar-nav gap-4" style="font-weight: 600; font-size: 15px;">
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('contact') }}">Contact</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-primary" href="{{ route('ai.chat') }}">Plantix-AI</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('forum') }}">Forum</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('experts.index') }}">Experts</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('shop') }}">Shop</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-agri text-dark" href="{{ route('appointments') }}">Appointments</a></li>
                </ul>
            </div>

            <!-- Right Actions -->
            <div class="d-none d-lg-flex align-items-center gap-4">
                @php
                    $cartCount = 0;
                    if (auth('web')->check()) {
                        $cart = \App\Models\Cart::where('user_id', auth('web')->id())->withCount('items')->first();
                        $cartCount = $cart ? $cart->items_count : 0;
                    }
                @endphp
                <div class="dropdown">
                    <a href="#" class="text-dark position-relative dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration: none;">
                        <i class="far fa-shopping-cart text-dark" style="font-size: 20px;"></i>
                        @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 10px; top: -5px !important; right: -10px !important;">
                            {{ $cartCount }}
                        </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-3 shadow border-0" style="border-radius: var(--agri-radius-md); min-width: 250px;">
                        @if($cartCount > 0)
                        <li class="d-flex flex-column gap-2">
                            <a href="{{ route('cart') }}" class="btn-agri btn-agri-outline w-100 text-center text-decoration-none">View Cart</a>
                            <a href="{{ route('checkout') }}" class="btn-agri btn-agri-primary w-100 text-center text-decoration-none">Checkout</a>
                        </li>
                        @else
                        <li><p class="text-center mb-0 text-muted">Your cart is empty.</p></li>
                        @endif
                    </ul>
                </div>

                @auth('web')
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-dark dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration: none; font-weight: 600;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user"></i>
                        </div>
                        {{ Str::limit(auth('web')->user()->name, 14) }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: var(--agri-radius-md);">
                        <li><a class="dropdown-item py-2" href="{{ route('account.profile') }}"><i class="fas fa-user fa-fw text-muted me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('orders') }}"><i class="fas fa-box fa-fw text-muted me-2"></i> Orders</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('appointments') }}"><i class="fas fa-calendar fa-fw text-muted me-2"></i> Appointments</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @else
                <a href="{{ route('signin') }}" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 10px 24px; font-weight: 600;">Sign In</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
