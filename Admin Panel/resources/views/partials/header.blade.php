<!-- Start Preloader 
    ============================================= -->
    <div id="preloader">
        <div id="agrica-preloader" class="agrica-preloader">
            <div class="animation-preloader">
                <div class="spinner"></div>
            </div>
            <div class="loader">
                <div class="row">
                    <div class="col-3 loader-section section-left">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-left">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-right">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-right">
                        <div class="bg"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Preloader -->

    <!-- Start Header Top 
    ============================================= -->
    <div class="top-bar-area top-bar-style-one bg-dark text-light">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-8">
                    <ul class="item-flex">
                        <li>
                            <i class="fas fa-clock"></i> Opening Hours : Sunday- Friday, 08:00 am - 05:00pm
                        </li>
                        <li>
                            <a href="tel:+92330088123"><i class="fas fa-phone-alt"></i> +92330088123</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="social">
                        <ul>
                            <li>
                                <a href="#">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Header Top -->

    <!-- Header 
    ============================================= -->
    <header>
        <!-- Start Navigation -->
        <nav class="navbar mobile-sidenav inc-shape navbar-sticky navbar-default validnavs dark">

            <div class="container d-flex justify-content-between align-items-center">


                <div class="navbar-brand-left">
                    <!-- Start Header Navigation -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                            <i class="fa fa-bars"></i>
                        </button>
                        <a class="navbar-brand" href="{{ route('home') }}">
                            <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" class="logo" alt="Logo">
                        </a>
                    </div>
                    <!-- End Header Navigation -->
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">

                    <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Logo">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-times"></i>
                    </button>

                    <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                        <li>
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li>
                            <a href="{{ route('about') }}">About</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>
                        <li>
                            <a href="{{ route('ai.chat') }}">Plantix-AI</a>
                        </li>
                        <li>
                            <a href="{{ route('forum') }}">Forum</a>
                        </li>
                        <li>
                            <a href="{{ route('experts.index') }}">Experts</a>
                        </li>
                        <li>
                            <a href="{{ route('shop') }}">Shop</a>
                        </li>
                        <li>
                            <a href="{{ route('appointments') }}">Appointments</a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->

                <div class="attr-right">
                    <!-- Start Atribute Navigation -->
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
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="far fa-shopping-cart"></i>
                                    <span class="badge">{{ $cartCount }}</span>
                                </a>
                                <ul class="dropdown-menu cart-list">
                                    @if($cartCount > 0)
                                    <li class="total">
                                        <a href="{{ route('cart') }}" class="btn btn-default btn-cart text-white" style="color: white !important;">View Cart</a>
                                        <a href="{{ route('checkout') }}" class="btn btn-default btn-cart">Checkout</a>
                                    </li>
                                    @else
                                    <li>
                                        <p class="text-center p-3 text-muted">Your cart is empty.</p>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            @auth('web')
                            <li class="dropdown user-nav">
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="far fa-user"></i> {{ Str::limit(auth('web')->user()->name, 14) }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('account.profile') }}"><i class="fas fa-user fa-fw"></i> Profile</a></li>
                                    <li><a href="{{ route('orders') }}"><i class="fas fa-box fa-fw"></i> Orders</a></li>
                                    <li><a href="{{ route('appointments') }}"><i class="fas fa-calendar fa-fw"></i> Appointments</a></li>
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
                    <!-- End Atribute Navigation -->

                </div>

                <!-- Main Nav -->
            </div>
            <!-- Overlay screen for menu -->
            <div class="overlay-screen"></div>
            <!-- End Overlay screen for menu -->

        </nav>
        <!-- End Navigation -->
    </header>
