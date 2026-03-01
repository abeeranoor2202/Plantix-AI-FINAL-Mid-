<header>
      <nav
        class="navbar mobile-sidenav inc-shape navbar-sticky navbar-default validnavs dark"
      >
        <div
          class="container d-flex justify-content-between align-items-center"
        >
          <div class="navbar-brand-left">
            <div class="navbar-header">
              <button
                type="button"
                class="navbar-toggle"
                data-toggle="collapse"
                data-target="#navbar-menu"
                title="Open menu"
                aria-label="Open menu"
              >
                <i class="fa fa-bars"></i>
              </button>
              <a class="navbar-brand" href="{{ route('home') }}"
                ><img
                  src="{{ asset('assets/img/plantix-ai-logo.png') }}"
                  class="logo"
                  alt="Logo"
              /></a>
            </div>
          </div>
          <div class="collapse navbar-collapse" id="navbar-menu">
            <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Logo" />
            <button
              type="button"
              class="navbar-toggle"
              data-toggle="collapse"
              data-target="#navbar-menu"
              title="Close menu"
              aria-label="Close menu"
            >
              <i class="fa fa-times"></i>
            </button>
            <ul
              class="nav navbar-nav navbar-right"
              data-in="fadeInDown"
              data-out="fadeOutUp"
            >
              <li><a href="{{ route('home') }}">Home</a></li>
              <li><a href="{{ route('about') }}">About</a></li>
              <li><a href="{{ route('contact') }}">Contact</a></li>
              <li><a href="{{ route('ai.chat') }}">Plantix-AI</a></li>
              <li><a href="{{ route('forum') }}">Forum</a></li>
              <li><a href="{{ route('experts.index') }}">Experts</a></li>
              <li><a href="{{ route('shop') }}">Shop</a></li>
              <li><a href="{{ route('appointments') }}">Appointments</a></li>
            </ul>
          </div>
          <div class="attr-right">
            <div class="attr-nav">
              <ul>
@php
    $cartCount = 0;
    if (auth('web')->check()) {
        $cart = \App\Models\Cart::where('user_id', auth('web')->id())->withCount('items')->first();
        $cartCount = $cart ? $cart->items_count : 0;
    }
@endphp
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="far fa-shopping-cart"></i>
                    <span class="badge">{{ $cartCount }}</span>
                  </a>
                  <ul class="dropdown-menu cart-list">
                    @if($cartCount > 0)
                      <li class="total">
                        <a href="{{ route('cart') }}" class="btn btn-default btn-cart">View Cart</a>
                        <a href="{{ route('checkout') }}" class="btn btn-default btn-cart">Checkout</a>
                      </li>
                    @else
                      <li><p class="text-center p-3 text-muted">Your cart is empty.</p></li>
                    @endif
                  </ul>
                </li>
                @auth('web')
                <li class="dropdown user-nav">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="far fa-user"></i> {{ Str::limit(auth('web')->user()->name, 14) }}
                  </a>
                  <ul class="dropdown-menu">
                    <li><a href="{{ route('account.profile') }}"><i class="fas fa-user fa-fw"></i> Profile</a></li>
                    <li><a href="{{ route('orders') }}"><i class="fas fa-box fa-fw"></i> Orders</a></li>
                    <li>
                      <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</button>
                      </form>
                    </li>
                  </ul>
                </li>
                @else
                <li class="button"><a href="{{ route('signin') }}">Sign In</a></li>
                @endauth
              </ul>
            </div>
          </div>
        </div>
        <div class="overlay-screen"></div>
      </nav>
    </header>
