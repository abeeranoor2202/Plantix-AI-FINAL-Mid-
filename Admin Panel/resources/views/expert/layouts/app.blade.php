<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Expert Panel') | Plantix AI</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
    <link href="{{asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
    <?php } ?>

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
    <link href="{{asset('css/style_rtl.css')}}" rel="stylesheet">
    <?php } ?>

    <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css')}}" rel="stylesheet">
    <link href="{{ asset('css/colors/green.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="{{ asset('css/icons/themify-icons/themify-icons.css') }}" rel="stylesheet">

    <link href="{{ asset('css/plantixai-redesign.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-customer-unified.css') }}" rel="stylesheet">
    <link href="{{ asset('css/platform-design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/panel-unified.css') }}" rel="stylesheet">

    <style>
        #main-wrapper { background-color: var(--agri-bg); min-height: 100vh; }
        .page-wrapper { background-color: var(--agri-bg) !important; }
        .scroll-sidebar { background: var(--agri-white); }
        .topbar { position: sticky; top: 0; z-index: 1050; }

        @media (min-width: 768px) {
            body.expert-sidebar-lock.mini-sidebar .left-sidebar { width: 240px; }
            body.expert-sidebar-lock.mini-sidebar .navbar-header { width: 240px; }
            body.expert-sidebar-lock.mini-sidebar .page-wrapper {
                padding-left: 240px !important;
                margin-left: 0 !important;
            }
            body.expert-sidebar-lock.mini-sidebar .footer { left: 240px; }
            body.expert-sidebar-lock.mini-sidebar .scroll-sidebar {
                overflow-x: hidden !important;
                position: relative !important;
            }
            body.expert-sidebar-lock.mini-sidebar .sidebar-nav #sidebarnav > li > a {
                width: auto !important;
            }
            body.expert-sidebar-lock.mini-sidebar .sidebar-nav #sidebarnav > li:hover > a {
                width: auto !important;
                background: transparent !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="expert-sidebar-lock admin-unified-ui panel-unified-ui expert-unified-ui">
<div id="app" class="fix-header fix-sidebar card-no-border">
    <div id="main-wrapper">
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                @include('layouts.header')
            </nav>
        </header>

        <aside class="left-sidebar">
            <div class="scroll-sidebar">
                @include('layouts.expert-menu')
            </div>
        </aside>

        <main class="page-wrapper" style="min-height: 100vh;">
            <div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; right: 0; top: 0;">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow js-session-alert" role="alert" style="min-width:280px;">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow js-session-alert" role="alert" style="min-width:280px;">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow js-session-alert" role="alert" style="min-width:280px;">
                            <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif
                </div>

                @yield('content')
            </div>
            <footer class="footer">
                @include('layouts.footer')
            </footer>
        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('js/waves.js') }}"></script>
<script src="{{ asset('js/sidebarmenu.js') }}"></script>
<script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
<script src="{{ asset('js/custom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.js')}}"></script>
<script src="{{ asset('js/jquery.resizeImg.js') }}"></script>
<script src="{{ asset('js/mobileBUGFix.mini.js') }}"></script>
<script src="{{ asset('js/chosen.jquery.js') }}"></script>
<script src="{{ asset('js/bootstrap-tagsinput.js') }}"></script>
<script src="{{ asset('js/crypto-js.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script src="{{ asset('js/platform-api.js') }}"></script>

<script>
    (function ($) {
        function keepSidebarExpanded() {
            if (window.innerWidth >= 768) {
                $('body').removeClass('mini-sidebar');
                $('.navbar-brand span').show();
            }
        }

        $(window).on('resize.expertSidebarLock', keepSidebarExpanded);
        $(document).on('click', '.sidebartoggler', function () {
            setTimeout(keepSidebarExpanded, 0);
        });
        $(keepSidebarExpanded);
    })(jQuery);
</script>

<script>
    (function () {
        // Auto-dismiss flash alerts after 3.5 seconds
        setTimeout(function () {
            $('.js-session-alert').alert('close');
        }, 3500);
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

                // Skip loading state for buttons that handle their own submission
                if (submitButton.hasAttribute('data-no-loading')) {
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

@stack('scripts')
</body>
</html>
