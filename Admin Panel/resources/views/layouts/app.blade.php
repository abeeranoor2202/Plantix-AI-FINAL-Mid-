<!doctype html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"

      <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->

    <meta name="csrf-token" content="{{ csrf_token() }}">

<!-- <title>{{ config('app.name', 'Laravel') }}</title> -->

    <title id="app_name"><?php echo @$_COOKIE['meta_title']; ?></title>

    <link rel="icon" id="favicon" type="image/x-icon"

          href="<?php echo str_replace('images/', 'images%2F', @$_COOKIE['favicon']); ?>">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->

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

    
    
    <!-- AgriTech Redesign: Core Design System -->
    <link href="{{ asset('css/agritech-redesign.css') }}" rel="stylesheet">

    <style>
        /* Modern structure overrides */
        #main-wrapper {
            background-color: var(--agri-bg);
            min-height: 100vh;
        }
        .page-wrapper {
            background-color: var(--agri-bg) !important;
        }
        .scroll-sidebar {
            background: var(--agri-white);
        }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1050;
        }
    </style>

</head>

<body>



<div id="app" class="fix-header fix-sidebar card-no-border">

    <div id="main-wrapper">



        <header class="topbar">



            <nav class="navbar top-navbar navbar-expand-md navbar-light">

                @include('layouts.header')

            </nav>



        </header>



        <aside class="left-sidebar">



            <!-- Sidebar scroll-->



            <div class="scroll-sidebar">



                @include('layouts.menu')



            </div>



            <!-- End Sidebar scroll-->

        </aside>

        <main class="page-wrapper pt-4">

            @yield('content')

        </main>

    </div>

</div>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

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







<script type="text/javascript">

    jQuery(window).scroll(function () {

        var scroll = jQuery(window).scrollTop();

        if (scroll <= 60) {

            jQuery("body").removeClass("sticky");

        } else {

            jQuery("body").addClass("sticky");

        }

    });



</script>

<!-- Firebase has been removed from the application -->

<script src="{{ asset('js/chosen.jquery.js') }}"></script>

<script src="{{ asset('js/bootstrap-tagsinput.js') }}"></script>

<script src="{{ asset('js/crypto-js.js') }}"></script>

<script src="{{ asset('js/jquery.cookie.js') }}"></script>

<script src="{{ asset('js/jquery.validate.js') }}"></script>



<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>



<script src="{{ asset('js/jquery.masking.js') }}"></script>



<script type="text/javascript">
    // Firebase has been removed from the application
    // Global settings, languages, and notifications require backend migration

    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }



    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Firebase has been removed - stub functions for compatibility
    async function sendEmail(url, subject, message, recipients) {
        return true;
    }

    async function sendNotification(fcmToken = '', title, body) {
        return true;
    }

    async function loadGoogleMapsScript() {
        // Firebase geolocation removed - using browser geolocation
        const script = document.createElement('script');
        let googleMapKey = 'AIzaSyD7-lVVm_uo5ydwCqRYZUP9Sy_qHF8Oi8w'; // Fallback key
        script.src = "https://maps.googleapis.com/maps/api/js?key=" + googleMapKey + "&libraries=drawing,geometry,places";
        script.onload = function () {
            navigator.geolocation.getCurrentPosition(GeolocationSuccessCallback, GeolocationErrorCallback);
            if(typeof window['InitializeGodsEyeMap'] === 'function') {
                InitializeGodsEyeMap();
            }
        };
        document.head.appendChild(script);
    }



    const GeolocationSuccessCallback = (position) => {

        if(position.coords != undefined){

            default_latitude = position.coords.latitude

            default_longitude = position.coords.longitude

            setCookie('default_latitude', default_latitude, 365);

            setCookie('default_longitude', default_longitude, 365);

        }

    };



    const GeolocationErrorCallback = (error) => {

        console.log('Error: You denied for your default Geolocation',error.message);

        setCookie('default_latitude', '23.022505', 365);

        setCookie('default_longitude','72.571365', 365);

    };



    loadGoogleMapsScript();



    async function sendNotification(fcmToken = '', title, body) {

        var checkFlag = false;

        // FCM notification endpoint (route not yet defined)
        var sendNotificationUrl = null;

        if (fcmToken !== '' && sendNotificationUrl) {

            await $.ajax({

                type: 'POST',

                url: sendNotificationUrl,

                data: {

                    _token: $('meta[name="csrf-token"]').attr('content'),

                    'fcm': fcmToken,

                    'title': title,

                    'message': body

                },

                success: function (data) {

                    checkFlag = true;

                },

                error: function (error) {

                    checkFlag = true;

                }

            });

        } else {

            checkFlag = true;

        }



        return checkFlag;

    }


    // Firebase notification service removed

</script>



@yield('scripts')



</body>

</html>

