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
              <li><a href="{{ route('plantix-ai') }}">Plantix-AI</a></li>
              <li><a href="{{ route('forum') }}">Forum</a></li>
              <li><a href="{{ route('shop') }}">Shop</a></li>
              <li><a href="{{ route('appointments') }}">Appointments</a></li>
            </ul>
          </div>
          <div class="attr-right">
            <div class="attr-nav">
              <ul>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                    ><i class="far fa-shopping-cart"></i
                    ><span class="badge">0</span></a
                  >
                  <ul class="dropdown-menu cart-list">
                    <li class="total">
                      <span class="pull-right"
                        ><strong>Total</strong>: PKR 0</span
                      ><a href="{{ route('cart') }}" class="btn btn-default btn-cart"
                        >Cart</a
                      ><a href="{{ route('checkout') }}" class="btn btn-default btn-cart"
                        >Checkout</a
                      >
                    </li>
                  </ul>
                </li>
                <li class="button"><a href="{{ route('signin') }}">Sign In</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="overlay-screen"></div>
      </nav>
    </header>
