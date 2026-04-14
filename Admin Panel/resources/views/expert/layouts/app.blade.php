<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Expert Portal') | Plantix-AI</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome / Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* AgriTech Color System */
            --agri-primary: #10B981;
            --agri-primary-dark: #059669;
            --agri-primary-light: #D1FAE5;
            --agri-secondary: #FBBF24;
            --agri-secondary-dark: #D97706;
            --agri-dark: #1F2937;
            --agri-bg: #F3F4F6;
            --agri-surface: #FFFFFF;
            
            /* Sidebar Variables */
            --sidebar-width: 260px;
            --sidebar-bg: var(--agri-surface);
            --sidebar-border: #E5E7EB;
            --sidebar-text: #4B5563;
            --sidebar-hover-bg: #F9FAFB;
            --sidebar-active-bg: var(--agri-primary-light);
            --sidebar-active-text: var(--agri-primary-dark);
            
            /* Shadows & Radius */
            --agri-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --agri-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
            --agri-radius: 0.75rem;
            
            /* Fonts */
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
        }

        body { 
            background: var(--agri-bg); 
            font-family: var(--font-main); 
            color: var(--agri-dark);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); font-weight: 700; }

        /* ── AgriTech Dashboard Components ─────────────────────────────── */
        
        .card-agri {
            background: var(--agri-surface);
            border-radius: var(--agri-radius);
            box-shadow: var(--agri-shadow);
            border: 1px solid rgba(0,0,0,0.02) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-agri.hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: var(--agri-shadow-lg);
        }

        /* Forms & Inputs */
        .form-agri {
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background-color: #F9FAFB;
            width: 100%;
            display: block;
        }
        
        .form-agri:focus {
            outline: none;
            border-color: var(--agri-primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        /* Buttons */
        .btn-agri {
            font-family: var(--font-main);
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            text-align: center;
            display: inline-block;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-agri-primary {
            background-color: var(--agri-primary);
            color: white;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
        }
        
        .btn-agri-primary:hover {
            background-color: var(--agri-primary-dark);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-agri-outline {
            background-color: transparent;
            color: var(--agri-dark);
            border: 1px solid #D1D5DB;
        }
        
        .btn-agri-outline:hover {
            background-color: #F3F4F6;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed; 
            top: 0; 
            left: 0; 
            height: 100vh;
            width: var(--sidebar-width); 
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            z-index: 1040;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-bottom: 1px solid var(--sidebar-border);
        }
        
        .sidebar-brand .icon-box {
            background: var(--agri-primary);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .sidebar-brand .brand-text {
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--agri-dark);
            line-height: 1.2;
        }

        .sidebar-brand small {
            display: block;
            font-family: var(--font-main);
            font-size: 0.75rem;
            color: var(--sidebar-text);
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .nav-item-agri {
            margin: 4px 16px;
        }
        
        .nav-link-agri {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--sidebar-text);
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        
        .nav-link-agri i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 10px;
            text-align: center;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .nav-link-agri:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--agri-dark);
        }
        
        .nav-link-agri.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 600;
        }
        
        .nav-link-agri.active i {
            opacity: 1;
            color: var(--agri-primary-dark);
        }

        /* Main Content Area */
        .main-wrapper { 
            margin-left: var(--sidebar-width); 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .topbar-agri {
            background: var(--agri-surface);
            border-bottom: 1px solid var(--sidebar-border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; 
            top: 0; 
            z-index: 100;
        }
        
        .topbar-title {
            margin: 0;
            font-size: 1.25rem;
            color: var(--agri-dark);
        }
        
        .content-body { 
            padding: 2rem; 
            flex-grow: 1;
        }
        
        /* Badges */
        .badge-agri {
            padding: 0.4em 0.8em;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .badge-warning-agri { background-color: #FEF3C7; color: #D97706; }
        .badge-success-agri { background-color: #D1FAE5; color: #059669; }
        .badge-danger-agri { background-color: #FEE2E2; color: #DC2626; }
        .badge-info-agri { background-color: #DBEAFE; color: #2563EB; }

        /* Notification bell */
        .notif-bell-agri { 
            position: relative; 
            color: var(--sidebar-text);
            text-decoration: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            background: #F9FAFB;
        }

        .notif-bell-agri:hover {
            background: #F3F4F6;
            color: var(--agri-dark);
        }

        .notif-bell-agri .badge-count {
            position: absolute; 
            top: -2px; 
            right: -2px;
            background: #EF4444; 
            color: white;
            border-radius: 50%; 
            width: 18px; 
            height: 18px;
            font-size: 0.65rem; 
            font-weight: bold;
            display: flex; 
            align-items: center; 
            justify-content: center;
            border: 2px solid white;
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }

        @yield('extra-css')
    </style>
    <link href="{{ asset('css/panel-unified.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="panel-unified-ui expert-unified-ui">

{{-- Sidebar --}}
<aside class="sidebar" id="expertSidebar">
    <div class="sidebar-brand">
        <div class="icon-box"><i class="fas fa-stethoscope"></i></div>
        <div>
            <div class="brand-text">Expert Portal</div>
            <small>
                @if(isset($currentExpert) && $currentExpert->profile?->account_type === 'agency')
                    <i class="fas fa-building me-1"></i> Agency Account
                @else
                    <i class="fas fa-user-md me-1"></i> Specialist
                @endif
            </small>
        </div>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.dashboard') ? 'active' : '' }}" href="{{ route('expert.dashboard') }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
        </div>
        
        <div class="px-4 py-2 mt-2 mb-1">
            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Consultations</span>
        </div>
        
        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.appointments.*') ? 'active' : '' }}" href="{{ route('expert.appointments.index') }}">
                <i class="far fa-calendar-check"></i> Appointments
            </a>
        </div>

        <div class="px-4 py-2 mt-2 mb-1">
            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Community</span>
        </div>
        
        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.forum.*') ? 'active' : '' }}" href="{{ route('expert.forum.index') }}">
                <i class="far fa-comments"></i> Forum
            </a>
        </div>

        <div class="px-4 py-2 mt-2 mb-1">
            <span class="text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Account</span>
        </div>

        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.notifications.*') ? 'active' : '' }}" href="{{ route('expert.notifications.index') }}">
                <i class="far fa-bell"></i> Notifications
                @php $unread = isset($currentExpert) ? $currentExpert->notificationLogs()->where('is_read',false)->count() : 0; @endphp
                @if($unread > 0)
                    <span class="badge rounded-pill bg-danger ms-auto" style="font-size: 0.65rem;">{{ $unread }}</span>
                @endif
            </a>
        </div>
        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.payouts.*') ? 'active' : '' }}" href="{{ route('expert.payouts.index') }}">
                <i class="fas fa-wallet"></i> Payouts
            </a>
        </div>
        <div class="nav-item-agri">
            <a class="nav-link-agri {{ request()->routeIs('expert.profile.*') ? 'active' : '' }}" href="{{ route('expert.profile.show') }}">
                <i class="far fa-user-circle"></i> My Profile
            </a>
        </div>
    </div>
    
    <div class="p-4 border-top">
        <form action="{{ route('expert.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-agri w-100 text-danger" style="background: #FEE2E2; border-radius: 0.5rem; font-weight: 600;">
                <i class="fas fa-sign-out-alt me-2"></i> Log Out
            </button>
        </form>
    </div>
</aside>

{{-- Main Content --}}
<div class="main-wrapper">
    {{-- Topbar --}}
    <header class="topbar-agri">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none" type="button" onclick="document.getElementById('expertSidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="d-flex align-items-center gap-4">
            <a href="{{ route('expert.notifications.index') }}" class="notif-bell-agri">
                <i class="far fa-bell fs-5"></i>
                @php $unread = isset($currentExpert) ? $currentExpert->notificationLogs()->where('is_read',false)->count() : 0; @endphp
                @if(($unread ?? 0) > 0)
                    <span class="badge-count">{{ $unread }}</span>
                @endif
            </a>
            
            <div class="d-flex align-items-center gap-3 bg-light px-2 py-1 pe-3 rounded-pill border">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: bold; font-size: 14px;">
                    {{ substr(auth('expert')->user()->name ?? 'E', 0, 1) }}
                </div>
                <div>
                    <span class="fw-bold text-dark fs-6 d-block" style="line-height: 1;">{{ auth('expert')->user()->name ?? 'Expert' }}</span>
                    <span class="badge-agri bg-success bg-opacity-10 text-success mt-1" style="font-size: 0.6rem; padding: 0.2em 0.6em;"><i class="fas fa-check-circle me-1"></i>Verified</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Body --}}
    <main class="content-body">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm" role="alert" style="background-color: #D1FAE5; color: #065F46; border-radius: var(--agri-radius);">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert" style="background-color: #FEE2E2; color: #991B1B; border-radius: var(--agri-radius);">
                <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
