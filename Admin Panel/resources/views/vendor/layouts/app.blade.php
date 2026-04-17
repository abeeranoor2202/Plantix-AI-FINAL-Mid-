<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title id="app_name">@yield('title', 'Vendor Panel') | Plantix AI</title>

    <link rel="icon" id="favicon" type="image/x-icon" href="<?php echo str_replace('images/', 'images%2F', @$_COOKIE['favicon']); ?>">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
    <?php } ?>

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
    <link href="{{ asset('css/style_rtl.css') }}" rel="stylesheet">
    <?php } ?>

    <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
    <link href="{{ asset('css/colors/green.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="{{ asset('css/icons/themify-icons/themify-icons.css') }}" rel="stylesheet">

    <link href="{{ asset('css/agritech-redesign.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-customer-unified.css') }}" rel="stylesheet">
    <link href="{{ asset('css/platform-design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/panel-unified.css') }}" rel="stylesheet">

    <style>
        #main-wrapper { background-color: var(--agri-bg); min-height: 100vh; }
        .page-wrapper { background-color: var(--agri-bg) !important; }
        .scroll-sidebar { background: var(--agri-white); }
        .topbar { position: sticky; top: 0; z-index: 1050; }

        /* Critical first-paint sidebar styles to avoid color flash */
        body.admin-unified-ui .left-sidebar,
        body.admin-unified-ui .scroll-sidebar {
            background: #ffffff !important;
        }

        body.admin-unified-ui .sidebar-nav .nav-link-agri {
            color: #334155 !important;
            background: transparent !important;
        }

        body.admin-unified-ui .sidebar-nav .nav-link-agri.active {
            background: #dcead8 !important;
            color: #234d20 !important;
        }

        @media (min-width: 768px) {
            body.admin-sidebar-lock.mini-sidebar .left-sidebar { width: 240px; }
            body.admin-sidebar-lock.mini-sidebar .navbar-header { width: 240px; }
            body.admin-sidebar-lock.mini-sidebar .page-wrapper { padding-left: 240px !important; margin-left: 0 !important; }
            body.admin-sidebar-lock.mini-sidebar .footer { left: 240px; }
            body.admin-sidebar-lock.mini-sidebar .scroll-sidebar { overflow-x: hidden !important; position: relative !important; }
            body.admin-sidebar-lock.mini-sidebar .sidebar-nav #sidebarnav > li > a { width: auto !important; }
            body.admin-sidebar-lock.mini-sidebar .sidebar-nav #sidebarnav > li:hover > a { width: auto !important; background: transparent !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="admin-sidebar-lock admin-unified-ui panel-unified-ui vendor-unified-ui">
<div id="app" class="fix-header fix-sidebar card-no-border">
    <div id="main-wrapper">
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                @include('layouts.header')
            </nav>
        </header>

        <aside class="left-sidebar">
            <div class="scroll-sidebar">
                @include('layouts.vendor-menu')
            </div>
        </aside>

        <main class="page-wrapper" style="min-height: 100vh;">
            @yield('content')
            <footer class="footer">@include('layouts.footer')</footer>
        </main>
    </div>
</div>

@if(session('success') || session('error'))
    <div class="position-fixed" style="top: 82px; right: 20px; z-index: 1090; min-width: 300px;">
        @if(session('success'))
            <div class="toast align-items-center text-bg-success border-0 mb-2 js-session-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast align-items-center text-bg-danger border-0 mb-2 js-session-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('js/waves.js') }}"></script>
<script src="{{ asset('js/sidebarmenu.js') }}"></script>
<script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('js/custom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.js') }}"></script>
<script src="{{ asset('js/jquery.resizeImg.js') }}"></script>
<script src="{{ asset('js/mobileBUGFix.mini.js') }}"></script>

<script>
    jQuery(window).scroll(function () {
        var scroll = jQuery(window).scrollTop();
        if (scroll <= 60) {
            jQuery('body').removeClass('sticky');
        } else {
            jQuery('body').addClass('sticky');
        }
    });
</script>

<script>
    (function ($) {
        function keepSidebarExpanded() {
            if (window.innerWidth >= 768) {
                $('body').removeClass('mini-sidebar');
                $('.navbar-brand span').show();
            }
        }

        $(window).on('resize.vendorSidebarLock', keepSidebarExpanded);
        $(document).on('click', '.sidebartoggler', function () {
            setTimeout(keepSidebarExpanded, 0);
        });
        $(keepSidebarExpanded);
    })(jQuery);
</script>

<script>
    (function () {
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Toast === 'undefined') {
            return;
        }

        document.querySelectorAll('.js-session-toast').forEach(function (el) {
            var toast = new bootstrap.Toast(el, { delay: 3500 });
            toast.show();
        });
    })();
</script>

<script>
    (function () {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const confirmText = form.getAttribute('data-confirm');
                if (confirmText && !window.confirm(confirmText)) {
                    event.preventDefault();
                    return;
                }

                const submitButton = form.querySelector('button[type="submit"], .platform-submit-btn');
                if (!submitButton || submitButton.classList.contains('is-loading')) {
                    return;
                }

                submitButton.classList.add('is-loading');
                submitButton.setAttribute('disabled', 'disabled');
                const loadingText = submitButton.getAttribute('data-loading-text');
                if (loadingText) {
                    const content = submitButton.querySelector('.btn-content');
                    if (content) {
                        content.dataset.originalText = content.textContent;
                        content.textContent = loadingText;
                    }
                }
            });
        });
    })();
</script>

<script src="{{ asset('js/chosen.jquery.js') }}"></script>
<script src="{{ asset('js/bootstrap-tagsinput.js') }}"></script>
<script src="{{ asset('js/crypto-js.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
<script src="{{ asset('js/jquery.masking.js') }}"></script>
<script src="{{ asset('js/platform-api.js') }}"></script>

@stack('scripts')
</body>
</html>
