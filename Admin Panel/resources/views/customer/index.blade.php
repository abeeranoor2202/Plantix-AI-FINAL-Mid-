@extends('layouts.frontend')

@section('title', 'Plantix-AI | Smart Farming')

@section('header')
    <!-- Preloader -->
    <div id="preloader">
        <div id="agrica-preloader" class="agrica-preloader">
            <div class="animation-preloader"><div class="spinner"></div></div>
        </div>
    </div>

    <!-- ── Weather Marquee Bar ─────────────────────────────────────────── -->
    <div id="weather-topbar" style="background: linear-gradient(90deg, #1a3c1a 0%, #2d6a2d 100%); color: #fff; font-size: 13px; padding: 7px 0; overflow: hidden; position: relative; z-index: 1100;">
        <div class="d-flex align-items-center" style="width: 100%; overflow: hidden;">

            {{-- Left-pinned label --}}
            <div class="d-none d-md-flex align-items-center gap-2 flex-shrink-0"
                 style="padding: 0 16px; border-right: 1px solid rgba(255,255,255,0.25); margin-right: 16px; white-space: nowrap;">
                <i class="fas fa-cloud-sun" style="font-size: 16px; color: #90EE90;"></i>
                <span style="font-weight: 700; font-size: 13px; letter-spacing: 0.5px;">LIVE WEATHER</span>
            </div>

            {{-- Scrolling marquee --}}
            <div style="overflow: hidden; flex: 1; min-width: 0;">
                <div class="weather-marquee-inner" style="display: inline-flex; gap: 0; animation: weatherScroll {{ count($weatherList) > 0 ? max(40, count($weatherList) * 8) : 30 }}s linear infinite; white-space: nowrap;">

                    @php
                        // Helper: pick farming tip based on conditions
                        function weatherTip(array $w): string {
                            if ($w['humidity'] > 75) return '⚠ High humidity — watch for fungal disease';
                            if ($w['temp'] > 38)     return '☀ Extreme heat — irrigate early/evening';
                            if ($w['temp'] < 8)      return '❄ Frost risk — protect sensitive crops';
                            return '✓ Good growing conditions';
                        }
                    @endphp

                    @forelse($weatherList as $w)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 0 28px; border-right: 1px solid rgba(255,255,255,0.15);">
                        <img src="{{ $w['icon_url'] }}" alt="{{ $w['condition'] }}"
                             style="width: 22px; height: 22px; filter: brightness(1.2); vertical-align: middle;">
                        <strong style="font-size: 14px;">{{ $w['city'] }}</strong>
                        <span style="color: #FFD54F; font-weight: 700;">{{ $w['temp'] }}°C</span>
                        <span style="color: rgba(255,255,255,0.7);">{{ ucfirst($w['condition']) }}</span>
                        <span style="color: #7EC8E3;"><i class="fas fa-tint" style="font-size: 11px;"></i> {{ $w['humidity'] }}%</span>
                        <span style="color: #C8E6C9;"><i class="fas fa-wind" style="font-size: 11px;"></i> {{ $w['wind_speed'] }} km/h</span>
                    </span>
                    @empty
                    <span style="padding: 0 28px;"><i class="fas fa-seedling me-2" style="color: #A5D6A7;"></i>Welcome to Plantix AI — Smart Farming Solutions</span>
                    <span style="padding: 0 28px;"><i class="fas fa-robot me-2"></i>AI-powered crop disease detection &amp; expert consultations</span>
                    <span style="padding: 0 28px;"><i class="fas fa-store me-2"></i>Shop quality agricultural inputs from verified vendors</span>
                    @endforelse

                    {{-- Duplicate set for seamless infinite loop --}}
                    @forelse($weatherList as $w)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 0 28px; border-right: 1px solid rgba(255,255,255,0.15);">
                        <img src="{{ $w['icon_url'] }}" alt="{{ $w['condition'] }}"
                             style="width: 22px; height: 22px; filter: brightness(1.2); vertical-align: middle;">
                        <strong style="font-size: 14px;">{{ $w['city'] }}</strong>
                        <span style="color: #FFD54F; font-weight: 700;">{{ $w['temp'] }}°C</span>
                        <span style="color: rgba(255,255,255,0.7);">{{ ucfirst($w['condition']) }}</span>
                        <span style="color: #7EC8E3;"><i class="fas fa-tint" style="font-size: 11px;"></i> {{ $w['humidity'] }}%</span>
                        <span style="color: #C8E6C9;"><i class="fas fa-wind" style="font-size: 11px;"></i> {{ $w['wind_speed'] }} km/h</span>
                    </span>
                    @empty
                    <span style="padding: 0 28px;"><i class="fas fa-seedling me-2" style="color: #A5D6A7;"></i>Welcome to Plantix AI — Smart Farming Solutions</span>
                    <span style="padding: 0 28px;"><i class="fas fa-robot me-2"></i>AI-powered crop disease detection &amp; expert consultations</span>
                    <span style="padding: 0 28px;"><i class="fas fa-store me-2"></i>Shop quality agricultural inputs from verified vendors</span>
                    @endforelse

                </div>
            </div>

            {{-- Right: updated time --}}
            @if(count($weatherList) > 0)
            <div class="d-none d-lg-block flex-shrink-0"
                 style="padding: 0 16px; border-left: 1px solid rgba(255,255,255,0.2); font-size: 11px; color: rgba(255,255,255,0.55); white-space: nowrap;">
                <i class="fas fa-sync-alt me-1"></i>{{ now()->format('H:i') }}
            </div>
            @endif

        </div>
    </div>
    <style>
    @keyframes weatherScroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .weather-marquee-inner:hover { animation-play-state: paused; }
    </style>
    <!-- ── End Weather Marquee ─────────────────────────────────────────── -->

    <!-- Header Navigation -->
    <header class="agri-header bg-white" style="box-shadow: var(--agri-shadow-sm); position: sticky; top: 0; z-index: 1000;">
        <nav class="navbar navbar-expand-lg py-3">
            <div class="container-fluid px-4 px-lg-5 d-flex justify-content-between align-items-center">
                <!-- Logo -->
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Plantix-AI Logo" style="height: 40px; border-radius: 8px;">
                </a>

                <!-- Mobile Toggle -->
                <button type="button" class="navbar-toggler border-0 d-lg-none" data-toggle="collapse" data-target="#navbar-menu">
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
                        <a href="#" class="text-dark position-relative dropdown-toggle" data-toggle="dropdown" style="text-decoration: none;">
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
                        <a href="#" class="d-flex align-items-center gap-2 text-dark dropdown-toggle" data-toggle="dropdown" style="text-decoration: none; font-weight: 600;">
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
@endsection

@section('page_scripts')
@endsection

@section('content')

    <!-- Hero Section -->
    <section class="position-relative overflow-hidden" style="background: var(--agri-bg); padding: 80px 0 120px 0;">
        <div class="container-agri position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                    <span class="d-inline-block px-3 py-1 mb-3 rounded-pill" style="background: var(--agri-primary-light); color: var(--agri-primary-dark); font-weight: 700; font-size: 13px; letter-spacing: 0.5px;">SMART FARMING REVOLUTION</span>
                    <h1 class="display-4 fw-bold mb-4" style="color: var(--agri-text-heading); font-family: 'Outfit', sans-serif; line-height: 1.2;">
                        Supercharge Your Harvest with <span style="color: var(--agri-primary);">Plantix-AI</span>
                    </h1>
                    <p class="lead mb-5" style="color: var(--agri-text-muted); font-size: 18px; line-height: 1.6;">
                        Transform agricultural practices with AI-powered insights. Real-time disease detection, precise fertilizer advice, and premium local data specifically for Pakistani farmers.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('ai.chat') }}" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 14px 32px; font-size: 16px;">
                            <i class="fas fa-robot me-2"></i> Try AI Assistant
                        </a>
                        <a href="{{ route('about') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 14px 32px; font-size: 16px;">
                            Learn More
                        </a>
                    </div>
                    
                    <div class="d-flex align-items-center gap-4 mt-5 pt-4 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span class="fw-bold" style="color: var(--agri-text-main);">+40% Yield</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span class="fw-bold" style="color: var(--agri-text-main);">Instant Results</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span class="fw-bold" style="color: var(--agri-text-main);">38k+ Farmers</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 position-relative">
                    <div class="position-relative ai-pulse" style="border-radius: var(--agri-radius-lg); overflow: hidden; height: 500px; box-shadow: var(--agri-shadow-lg);">
                        <img src="{{ asset('assets/img/field.jpg') }}" alt="Farmers in field" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                            <div class="ai-glass px-4 py-3 rounded-3 d-flex align-items-center gap-3">
                                <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-leaf text-success fs-4"></i>
                                </div>
                                <div class="text-white">
                                    <div class="fw-bold mb-1">Crop Health Status</div>
                                    <div class="fs-sm opacity-75">Analysis complete: 98% Optimal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services / Core Features -->
    <section class="py-5" style="background: white;">
        <div class="container-agri pt-5 pb-5">
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold text-success mb-2 d-block" style="letter-spacing: 1px;">AI Capabilities</span>
                <h2 class="display-5 fw-bold text-dark">Intelligent Farming Solutions</h2>
            </div>
            
            <div class="row g-4">
                <!-- Service 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri text-center h-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <div style="width: 70px; height: 70px; background: var(--agri-primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                            <img src="{{ asset('assets/img/icon/17.png') }}" style="width: 35px;" alt="Crop Prediction">
                        </div>
                        <h4 class="fw-bold mb-3" style="font-size: 1.25rem;">Crop Recommendation</h4>
                        <p class="text-muted mb-4 flex-grow-1" style="font-size: 14px;">Data-driven suggestions for the best crops to plant based on your specific soil and weather.</p>
                        <a href="{{ route('crop.recommendation') }}" class="btn-agri w-100" style="background: var(--agri-bg); color: var(--agri-primary-dark); font-weight: 600; text-decoration: none;">Try Now <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Service 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri text-center h-100 d-flex flex-column align-items-center justify-content-center p-4" style="border-color: rgba(16, 185, 129, 0.3); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.1);">
                        <div class="position-absolute top-0 end-0 mt-3 me-3 text-success"><i class="fas fa-sparkles"></i> Popular</div>
                        <div style="width: 70px; height: 70px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                            <img src="{{ asset('assets/img/icon/18.png') }}" style="width: 35px;" alt="Crop Planning">
                        </div>
                        <h4 class="fw-bold mb-3" style="font-size: 1.25rem;">Disease Identification</h4>
                        <p class="text-muted mb-4 flex-grow-1" style="font-size: 14px;">Upload a leaf photo and instantly detect diseases or pests with 96% AI accuracy.</p>
                        <a href="{{ route('disease.identification') }}" class="btn-agri btn-agri-primary w-100 text-decoration-none">Analyze Plant <i class="fas fa-camera ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Service 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri text-center h-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <div style="width: 70px; height: 70px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                            <img src="{{ asset('assets/img/icon/19.png') }}" style="width: 35px;" alt="Crop Disease">
                        </div>
                        <h4 class="fw-bold mb-3" style="font-size: 1.25rem;">Fertilizer Guide</h4>
                        <p class="text-muted mb-4 flex-grow-1" style="font-size: 14px;">Calculate exact NPK requirements to prevent soil degradation and maximize output.</p>
                        <a href="{{ route('fertilizer.recommendation') }}" class="btn-agri w-100" style="background: var(--agri-bg); color: var(--agri-primary-dark); font-weight: 600; text-decoration: none;">Calculate <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Service 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri text-center h-100 d-flex flex-column align-items-center justify-content-center p-4">
                        <div style="width: 70px; height: 70px; background: rgba(139, 92, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                            <img src="{{ asset('assets/img/icon/20.png') }}" style="width: 35px;" alt="Fertilizer Guide">
                        </div>
                        <h4 class="fw-bold mb-3" style="font-size: 1.25rem;">Smart Planning</h4>
                        <p class="text-muted mb-4 flex-grow-1" style="font-size: 14px;">Establish a comprehensive crop calendar and irrigation schedule for your fields.</p>
                        <a href="{{ route('crop.planning') }}" class="btn-agri w-100" style="background: var(--agri-bg); color: var(--agri-primary-dark); font-weight: 600; text-decoration: none;">Plan Season <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- App / Why Choose Us -->
    <section class="py-5 bg-light" style="background: var(--agri-bg) !important;">
        <div class="container-agri py-5">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0 pe-lg-5">
                    <h4 class="text-success text-uppercase fw-bold mb-3" style="letter-spacing: 1px; font-size: 13px;">Why Choose Us</h4>
                    <h2 class="display-5 fw-bold text-dark mb-4">Precision Agriculture, Simplified.</h2>
                    
                    <div class="d-flex flex-column gap-4 mt-5">
                        <div class="d-flex gap-3">
                            <div style="min-width: 48px; width: 48px; height: 48px; background: var(--agri-white); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: var(--agri-shadow-sm);">
                                <i class="fas fa-seedling text-success fs-5"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-2 h5 text-dark">Real-Time Monitoring</h4>
                                <p class="text-muted mb-0" style="font-size: 15px;">Monitor crops 24/7. Upload photos and receive instant, personalized disease identification.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div style="min-width: 48px; width: 48px; height: 48px; background: var(--agri-white); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: var(--agri-shadow-sm);">
                                <i class="fas fa-chart-line text-success fs-5"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-2 h5 text-dark">Data-Driven Analytics</h4>
                                <p class="text-muted mb-0" style="font-size: 15px;">Track soil nutrients, weather forecasts, and growth patterns to optimize resource allocation.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div style="min-width: 48px; width: 48px; height: 48px; background: var(--agri-white); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: var(--agri-shadow-sm);">
                                <i class="fas fa-globe-asia text-success fs-5"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-2 h5 text-dark">Sustainable & Local</h4>
                                <p class="text-muted mb-0" style="font-size: 15px;">Reduce chemical usage with interventions specifically tailored to Pakistani agricultural ecosystems.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 position-relative pl-lg-5">
                    <div style="background: url({{ asset('assets/img/shape/18.png') }}) no-repeat; background-size: contain; padding: 20px;">
                        <img src="{{ asset('assets/img/1.jpg') }}" class="img-fluid rounded-4 shadow-lg w-100" style="border: 4px solid white;" alt="Farmers Using Tablets">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Expert Team Section -->
    <section class="py-5" style="background: white;">
        <div class="container-agri py-5">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <span class="text-uppercase fw-bold text-success mb-2 d-block" style="letter-spacing: 1px;">Expert Panel</span>
                    <h2 class="display-5 fw-bold text-dark mb-0">Book Consultations with Specialists</h2>
                </div>
                <a href="{{ route('experts.index') }}" class="btn-agri btn-agri-outline d-none d-md-flex text-decoration-none">View All Experts</a>
            </div>
            
            <div class="row g-4">
                <!-- Expert 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri p-0 overflow-hidden h-100 border-0 shadow-sm" style="transition: transform 0.3s; background: var(--agri-bg);">
                        <div style="height: 250px; overflow: hidden;">
                            <img src="{{ asset('assets/img/farmer2.jpg') }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.5s;" alt="Dr. Ayesha Khan" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="p-4 text-center">
                            <h4 class="fw-bold mb-1" style="font-size: 18px;">Dr. Ayesha Khan</h4>
                            <p class="text-success mb-3" style="font-size: 14px; font-weight: 500;">Plant Pathologist</p>
                            <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1"></i> Lahore, Punjab</p>
                        </div>
                    </div>
                </div>
                <!-- Expert 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri p-0 overflow-hidden h-100 border-0 shadow-sm" style="transition: transform 0.3s; background: var(--agri-bg);">
                        <div style="height: 250px; overflow: hidden;">
                            <img src="{{ asset('assets/img/farmer4.jpg') }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.5s;" alt="Engr. Hamid Raza" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="p-4 text-center">
                            <h4 class="fw-bold mb-1" style="font-size: 18px;">Engr. Hamid Raza</h4>
                            <p class="text-success mb-3" style="font-size: 14px; font-weight: 500;">Irrigation Engineer</p>
                            <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1"></i> Multan, Punjab</p>
                        </div>
                    </div>
                </div>
                <!-- Expert 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card-agri p-0 overflow-hidden h-100 border-0 shadow-sm" style="transition: transform 0.3s; background: var(--agri-bg);">
                        <div style="height: 250px; overflow: hidden;">
                            <img src="{{ asset('assets/img/farmer3.jpg') }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.5s;" alt="Dr. Imran Qureshi" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="p-4 text-center">
                            <h4 class="fw-bold mb-1" style="font-size: 18px;">Dr. Imran Qureshi</h4>
                            <p class="text-success mb-3" style="font-size: 14px; font-weight: 500;">Entomologist</p>
                            <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1"></i> Faisalabad, Punjab</p>
                        </div>
                    </div>
                </div>
                 <!-- Expert 4 -->
                 <div class="col-md-6 col-lg-3">
                    <div class="card-agri p-0 overflow-hidden h-100 border-0 shadow-sm" style="background: var(--agri-primary-light); display: flex; align-items: center; justify-content: center;">
                        <div class="p-4 text-center">
                            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-calendar-plus text-success fs-4"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-dark">Need Expert Advice?</h4>
                            <p class="text-muted mb-4 small">Schedule a 1-on-1 consultation with top agronomists and yield experts.</p>
                            <a href="{{ route('appointments') }}" class="btn-agri btn-agri-primary w-100 text-decoration-none shadow-sm">Book Appointment <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5" style="background: var(--agri-bg);">
        <div class="container-agri py-5">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <img src="{{ asset('assets/img/7.jpg') }}" class="img-fluid rounded-4 shadow" alt="FAQ Farming">
                </div>
                <div class="col-lg-6 offset-lg-1">
                    <span class="text-uppercase fw-bold text-success mb-2 d-block" style="letter-spacing: 1px;">FAQ</span>
                    <h2 class="display-6 fw-bold text-dark mb-4">Learn How Plantix-AI Improves Your Farming</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="card-agri p-0 mb-3 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                            <div class="p-3 bg-white" id="headingOne" data-bs-toggle="collapse" data-bs-target="#collapseOne" style="cursor:pointer;">
                                <h5 class="mb-0 fw-bold d-flex justify-content-between align-items-center" style="font-size: 16px; color: var(--agri-text-heading);">
                                    What do you add to the soil before planting?
                                    <i class="fas fa-chevron-down text-muted" style="font-size: 12px;"></i>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse show" data-bs-parent="#faqAccordion">
                                <div class="p-4 border-top" style="background: var(--agri-bg); font-size: 15px; color: var(--agri-text-main);">
                                    Before planting, test your soil to understand its profile. Based on AI analysis, we recommend incorporating organic compost and specific fertilizers to balance pH levels.
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-agri p-0 mb-3 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                            <div class="p-3 bg-white collapsed" id="headingTwo" data-bs-toggle="collapse" data-bs-target="#collapseTwo" style="cursor:pointer;">
                                <h5 class="mb-0 fw-bold d-flex justify-content-between align-items-center" style="font-size: 16px; color: var(--agri-text-heading);">
                                    Do you use herbicides?
                                    <i class="fas fa-chevron-down text-muted" style="font-size: 12px;"></i>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="p-4 border-top" style="background: var(--agri-bg); font-size: 15px; color: var(--agri-text-main);">
                                    We advocate for Integrated Pest Management (IPM). Start with natural weed control methods and use selective herbicides only when necessary.
                                </div>
                            </div>
                        </div>

                        <div class="card-agri p-0 mb-3 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                            <div class="p-3 bg-white collapsed" id="headingThree" data-bs-toggle="collapse" data-bs-target="#collapseThree" style="cursor:pointer;">
                                <h5 class="mb-0 fw-bold d-flex justify-content-between align-items-center" style="font-size: 16px; color: var(--agri-text-heading);">
                                    How does the Crop Detection AI work?
                                    <i class="fas fa-chevron-down text-muted" style="font-size: 12px;"></i>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="p-4 border-top" style="background: var(--agri-bg); font-size: 15px; color: var(--agri-text-main);">
                                    Simply open the Disease Detection camera on your mobile device, snap a picture of a leaf, and our trained neural network will identify the disease and prescribe local remedies within 2 seconds.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="py-5" style="background: var(--agri-primary-dark); color: white;">
        <div class="container-agri py-5 text-center position-relative">
            <div style="max-width: 800px; margin: 0 auto; position: relative; z-index: 2;">
                <h2 class="display-4 fw-bold mb-4 text-white" style="font-family: 'Outfit', sans-serif;">Join the Agricultural Revolution</h2>
                <p class="lead text-white mb-4 opacity-75" style="font-size: 18px; line-height: 1.6;">
                    Empower your farm with intelligent technology designed for Pakistani agriculture. From wheat and rice to cotton and sugarcane, start optimizing your fields today.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('signup') }}" class="btn-agri text-decoration-none shadow" style="background: #FBBF24; color: #1F2937; padding: 14px 32px; font-size: 16px; font-weight: 700; border: none; border-radius: var(--agri-radius-md); transition: all 0.3s ease;">
                        Create Free Account
                    </a>
                    <a href="{{ route('ai.chat') }}" class="btn-agri btn-agri-outline text-decoration-none text-white" style="border: 2px solid white; padding: 12px 32px; font-size: 16px; font-weight: 700; border-radius: var(--agri-radius-md); transition: all 0.3s ease;">
                        Try AI First
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
