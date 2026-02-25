@extends('layouts.app')

@section('title', 'Plantix-AI')

@section('header')
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

    <!-- Header 
    ============================================= -->
    <header>
        <!-- Start Navigation -->
        <nav
            class="navbar mobile-sidenav navbar-style-one navbar-sticky navbar-default validnavs white navbar-fixed no-background">

            <div class="container-full d-flex justify-content-between align-items-center">

                <!-- Start Header Navigation -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-bars"></i>
                    </button>
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" class="logo desktop" alt="Logo">
                        <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" class="logo logo-mobile" alt="Logo">
                    </a>
                </div>
                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">

                    <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Logo">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-times"></i>
                    </button>

                    <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                        <li>
                            <a href="{{ route('home') }}" class="active">Home</a>
                        </li>
                        <li>
                            <a href="{{ route('about') }}">About</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>
                        <li>
                            <a href="{{ route('plantix-ai') }}">Plantix-AI</a>
                        </li>
                        <li>
                            <a href="{{ route('forum') }}">Forum</a>
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
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="far fa-shopping-cart"></i>
                                    <span class="badge">3</span>
                                </a>
                                <ul class="dropdown-menu cart-list">
                                    <li>
                                        <p class="text-center p-3 text-muted">Your cart is empty.</p>
                                    </li>
                                </ul>
                            </li>
                            <li class="button"><a href="{{ route('signup') }}">Register</a></li>

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
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/jquery.appear.js') }}"></script>
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/progress-bar.min.js') }}"></script>
    <script src="{{ asset('assets/js/circle-progress.js') }}"></script>
    <script src="{{ asset('assets/js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/count-to.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.scrolla.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollOnReveal.js') }}"></script>
    <script src="{{ asset('assets/js/YTPlayer.min.js') }}"></script>
    <script src="{{ asset('assets/js/gsap.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollTrigger.min.js') }}"></script>
    <script src="{{ asset('assets/js/SplitText.min.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')
<!-- End Header -->

    <!-- Start Banner Area 
    ============================================= -->
    <div class="banner-area banner-style-two text-center navigation-circle zoom-effect overflow-hidden text-light">
        <!-- Slider main container -->
        <div class="banner-fade">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">

                <!-- Single Item -->
                <div class="swiper-slide banner-style-two">
                    <div class="banner-thumb bg-cover shadow dark" style="background: url({{ asset('assets/img/field.jpg') }});"></div>
                    <div class="container">
                        <div class="row align-center">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="content">
                                    <h2>Smart Farming with <strong>Plantix-AI</strong></h2>
                                    <p>
                                        Transform your agricultural practices with AI-powered insights. Get real-time
                                        crop recommendations, disease detection, and precision farming solutions
                                        tailored for Pakistani farmers.
                                    </p>
                                    <div class="button">
                                        <a class="animated-btn" href="{{ route('about') }}"><i class="fas fa-angle-right"></i>
                                            Discover More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Single Item -->

                <!-- Single Item -->
                <div class="swiper-slide banner-style-two">
                    <div class="banner-thumb bg-cover shadow dark" style="background: url({{ asset('assets/img/field.jpg') }});"></div>
                    <div class="container">
                        <div class="row align-center">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="content">
                                    <h2>Maximize Your <strong>Harvest</strong></h2>
                                    <p>
                                        Leverage data-driven agriculture to increase crop yields by up to 40%. Our AI
                                        analyzes soil health, weather patterns, and crop conditions to help you make
                                        smarter farming decisions.
                                    </p>
                                    <div class="button">
                                        <a class="animated-btn" href="{{ route('about') }}"><i class="fas fa-angle-right"></i>
                                            Discover More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Single Item -->

            </div>

            <!-- Navigation -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>

        </div>
    </div>
    <!-- End Banner -->

    <!-- Start Feature 
    ============================================= -->
    <div class="feature-style-one-area default-padding" style="background-image: url({{ asset('assets/img/shape/18.png') }});">
        <div class="container">
            <div class="row align-center">
                <div class="col-xl-3 col-lg-6">
                    <div class="feature-style-one-item">
                        <img src="{{ asset('assets/img/1.jpg') }}" alt="Image Not Found">
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 pl-50 pr-50 pl-md-15 pr-md-15 pl-xs-15 pr-xs-15">
                    <div class="feature-style-one-info">
                        <h2 class="title">AI-Powered Agriculture <br> Solutions for Pakistan</h2>
                        <p>
                            Plantix-AI combines cutting-edge artificial intelligence with local agricultural expertise
                            to revolutionize farming in Pakistan. Our platform provides instant crop diagnostics,
                            personalized fertilizer recommendations, and data-driven insights that help farmers increase
                            productivity while reducing costs and environmental impact.
                        </p>
                        <ul class="item-list">
                            <li>AI-Driven Crop Disease Detection</li>
                            <li>Smart Fertilizer Optimization</li>
                        </ul>
                        <a class="btn btn-theme mt-30 btn-md radius animation" href="{{ route('about') }}">Discover More</a>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-12">
                    <div class="featured-product">
                        <!-- Single Item -->
                        <div class="product-list-item">
                            <a href="{{ route('crop-recommendation') }}">
                                <img src="{{ asset('assets/img/icon/17.png') }}" alt="Icon">
                                <h5>Crop Recommendation</h5>
                            </a>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="product-list-item">
                            <a href="{{ route('crop-planning') }}">
                                <img src="{{ asset('assets/img/icon/18.png') }}" alt="Icon">
                                <h5>Crop Planning</h5>
                            </a>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="product-list-item">
                            <a href="{{ route('disease-identification') }}">
                                <img src="{{ asset('assets/img/icon/19.png') }}" alt="Icon">
                                <h5>Disease Identification</h5>
                            </a>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="product-list-item">
                            <a href="{{ route('fertilizer-recommendation') }}">
                                <img src="{{ asset('assets/img/icon/20.png') }}" alt="Icon">
                                <h5>Fertilizer Recommendation</h5>
                            </a>
                        </div>
                        <!-- End Single Item -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Feature -->

    <!-- Start Choose Us 
    ============================================= -->
    <div class="choose-us-style-two-area overflow-hidden default-padding-top">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-lg-6 pr-100 pr-md-15 pr-xs-15 pb-120 pb-md-60 pb-xs-60">
                    <ul class="list-simple text-light">
                        <li>
                            <h4>Real-Time Monitoring</h4>
                            <p>
                                Monitor your crops 24/7 with AI-powered image analysis. Upload photos of your plants and
                                receive instant disease identification, pest detection, and treatment recommendations
                                specific to Pakistani agricultural conditions.
                            </p>
                        </li>
                        <li>
                            <h4>Precision Agriculture</h4>
                            <p>
                                Make data-driven decisions with our advanced analytics platform. Track soil nutrients,
                                weather forecasts, and crop growth patterns to optimize planting schedules and resource
                                allocation.
                            </p>
                        </li>
                        <li>
                            <h4>Sustainable Farming</h4>
                            <p>
                                Reduce chemical usage and costs with targeted interventions. Our AI recommends
                                eco-friendly solutions that improve soil health and increase long-term farm productivity
                                sustainably.
                            </p>
                        </li>
                    </ul>
                </div>
                <div class="col-xl-5 offset-xl-1 col-lg-6">
                    <div class="choose-us-style-two-content">
                        <h4 class="sub-title">Why Choose Plantix-AI</h4>
                        <h2 class="title">Transform Agriculture with Intelligent Technology</h2>
                        <div class="choose-us-style-two-info">
                            <div class="content">
                                <div class="fun-fact">
                                    <div class="counter">
                                        <div class="timer" data-to="38" data-speed="2000">38</div>
                                        <div class="operator">K</div>
                                    </div>
                                    <span class="medium">Farmers Using AI Tools</span>
                                </div>
                                <div class="fun-fact">
                                    <div class="counter">
                                        <div class="timer" data-to="28" data-speed="2000">28</div>
                                        <div class="operator">%</div>
                                    </div>
                                    <span class="medium">Average Yield Improvement</span>
                                </div>
                            </div>
                            <div class="thumb">
                                <img src="{{ asset('assets/img/4.jpg') }}" alt="Image Not Found">
                                <a href="https://www.youtube.com/watch?v=3JigXb9KXqI"
                                    class="popup-youtube video-play-button">
                                    <i class="fas fa-play"></i>
                                    <div class="effect"></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Choose Us -->

    <!-- Start Service 
    ============================================= -->
    <div class="service-style-two-area half-bg-dark-bottom default-padding-top pb-md-120 bg-gray">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="text-center mb-60 mb-md-40 mb-xs-40">
                        <h2 class="mask-text large" style="background-image: url({{ asset('assets/img/shape/28.jpg') }});">AI-Powered
                            Services for Modern Farming</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="service-style-two-carousel swiper mb--30">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">

                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="service-style-two">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/crop.png') }}" alt="Image not Found">
                                    </div>
                                    <div class="overlay">
                                        <div class="icon">
                                            <img src="{{ asset('assets/img/icon/21.png') }}" alt="Image Not Found">
                                        </div>
                                        <div class="info">
                                            <h4><a href="{{ route('crop-recommendation') }}">Crop Recommendation</a></h4>
                                            <span>Plantix-AI Module</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="service-style-two">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/soilhealth.png') }}" alt="Image not Found">
                                    </div>
                                    <div class="overlay">
                                        <div class="icon">
                                            <img src="{{ asset('assets/img/icon/22.png') }}" alt="Image Not Found">
                                        </div>
                                        <div class="info">
                                            <h4><a href="{{ route('crop-planning') }}">Crop Planning</a></h4>
                                            <span>Plantix-AI Module</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="service-style-two">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/plantdisease.png') }}" alt="Image not Found">
                                    </div>
                                    <div class="overlay">
                                        <div class="icon">
                                            <img src="{{ asset('assets/img/icon/23.png') }}" alt="Image Not Found">
                                        </div>
                                        <div class="info">
                                            <h4><a href="{{ route('disease-identification') }}">Disease Identification</a></h4>
                                            <span>Plantix-AI Module</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="service-style-two">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/fertilizer.png') }}" alt="Image not Found">
                                    </div>
                                    <div class="overlay">
                                        <div class="icon">
                                            <img src="{{ asset('assets/img/icon/24.png') }}" alt="Image Not Found">
                                        </div>
                                        <div class="info">
                                            <h4><a href="{{ route('fertilizer-recommendation') }}">Fertilizer Recommendation</a>
                                            </h4>
                                            <span>Plantix-AI Module</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->

                        </div>

                        <!-- Navigation -->
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Service -->



    <!-- Start Team 
    ============================================= -->
    <div class="team-style-one-area default-padding-bottom pt-md-120 mt-100">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-4">
                    <h4 class="sub-title">Expert Team</h4>
                    <h2 class="title">Meet Our Agricultural Technology Specialists</h2>
                    <a class="btn btn-theme secondary mt-10 btn-md radius animation" href="{{ route('about') }}">View All
                        Experts</a>
                </div>
                <div class="col-lg-7 offset-lg-1">
                    <div class="team-style-one-carousel swiper">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">

                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="farmer-style-one-item">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/farmer2.jpg') }}" alt="Image Not Found">
                                        <div class="social">
                                            <i class="fas fa-share-alt"></i>
                                            <ul>
                                                <li class="facebook">
                                                    <a href="#">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </a>
                                                </li>
                                                <li class="twitter">
                                                    <a href="#">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                </li>
                                                <li class="linkedin">
                                                    <a href="#">
                                                        <i class="fab fa-linkedin-in"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="info">
                                        <span>Plant Pathologist (Expert)</span>
                                        <h4><a href="#">Dr. Ayesha Khan</a></h4>
                                        <p class="small text-muted mb-0">Lahore, Punjab · Wheat, Rice, Fungal diseases
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="farmer-style-one-item">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/farmer4.jpg') }}" alt="Image Not Found">
                                        <div class="social">
                                            <i class="fas fa-share-alt"></i>
                                            <ul>
                                                <li class="facebook">
                                                    <a href="#">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </a>
                                                </li>
                                                <li class="twitter">
                                                    <a href="#">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                </li>
                                                <li class="linkedin">
                                                    <a href="#">
                                                        <i class="fab fa-linkedin-in"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="info">
                                        <span>Irrigation Engineer (Expert)</span>
                                        <h4><a href="#">Engr. Hamid Raza</a></h4>
                                        <p class="small text-muted mb-0">Multan, Punjab · Drip systems, Water
                                            scheduling, Cotton</p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="farmer-style-one-item">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/farmer2.jpg') }}" alt="Image Not Found">
                                        <div class="social">
                                            <i class="fas fa-share-alt"></i>
                                            <ul>
                                                <li class="facebook">
                                                    <a href="#">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </a>
                                                </li>
                                                <li class="twitter">
                                                    <a href="#">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                </li>
                                                <li class="linkedin">
                                                    <a href="#">
                                                        <i class="fab fa-linkedin-in"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="info">
                                        <span>Soil & Fertility Specialist (Expert)</span>
                                        <h4><a href="#">Dr. Sana Baloch</a></h4>
                                        <p class="small text-muted mb-0">Hyderabad, Sindh · Soil salinity, Fertilizer
                                            plans, Maize</p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="farmer-style-one-item">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/farmer3.jpg') }}" alt="Image Not Found">
                                        <div class="social">
                                            <i class="fas fa-share-alt"></i>
                                            <ul>
                                                <li class="facebook"><a href="#"><i class="fab fa-facebook-f"></i></a>
                                                </li>
                                                <li class="twitter"><a href="#"><i class="fab fa-twitter"></i></a></li>
                                                <li class="linkedin"><a href="#"><i class="fab fa-linkedin-in"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="info">
                                        <span>Entomologist (Expert)</span>
                                        <h4><a href="#">Dr. Imran Qureshi</a></h4>
                                        <p class="small text-muted mb-0">Faisalabad, Punjab · Pink bollworm, IPM, Traps
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                            <!-- Single Item -->
                            <div class="swiper-slide">
                                <div class="farmer-style-one-item">
                                    <div class="thumb">
                                        <img src="{{ asset('assets/img/farmer2.jpg') }}" alt="Image Not Found">
                                        <div class="social">
                                            <i class="fas fa-share-alt"></i>
                                            <ul>
                                                <li class="facebook"><a href="#"><i class="fab fa-facebook-f"></i></a>
                                                </li>
                                                <li class="twitter"><a href="#"><i class="fab fa-twitter"></i></a></li>
                                                <li class="linkedin"><a href="#"><i class="fab fa-linkedin-in"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="info">
                                        <span>Precision Ag Specialist (Expert)</span>
                                        <h4><a href="#">Engr. Ayesha Siddiqui</a></h4>
                                        <p class="small text-muted mb-0">Peshawar, KPK · Remote sensing, Yield maps,
                                            Variable-rate</p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Item -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Team -->

    <!-- Start Faq 
    ============================================= -->
    <div class="feq-style-one-area default-padding bg-gray">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-5">
                    <div class="thumb-style-two">
                        <img src="{{ asset('assets/img/7.jpg') }}" alt="Image Not Found">
                        <h2><strong>F</strong>AQ</h2>
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-1">
                    <div class="faq-style-one-info">
                        <h2 class="title">Learn How Plantix-AI Improves Your Farming</h2>
                        <div class="accordion accordion-regular mt-35 mt-xs-15" id="faqAccordion">
                            <div class="faq-style-one">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        What do you add to the soil before you plant?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show"
                                    aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>
                                            Before planting, it's essential to test your soil to understand its nutrient
                                            profile. Based on our AI analysis, we recommend incorporating organic
                                            compost and specific fertilizers to balance pH levels and ensure your crops
                                            have the necessary nitrogen, phosphorus, and potassium for optimal growth.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-style-one">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Do you use herbicides?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                    data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>
                                            We advocate for Integrated Pest Management (IPM). While herbicides can be
                                            effective, we recommend starting with natural weed control methods and using
                                            selective herbicides only when necessary, as guided by our precision farming
                                            module, to minimize environmental impact and resistance.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-style-one">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseThree" aria-expanded="false"
                                        aria-controls="collapseThree">
                                        Where does the water come on your crops?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse"
                                    aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p>
                                            Water management is critical. We recommend efficient irrigation systems like
                                            drip or sprinkler irrigation, scheduled according to our AI-driven weather
                                            and soil moisture forecasts. This ensures crops receive adequate water
                                            without wastage, crucial for water-scarce regions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Faq -->



    <!-- Start Call To Action 
    ============================================= -->
    <div class="call-to-action-area overflow-hidden default-padding-top bg-gray"
        style="background-image: url({{ asset('assets/img/shape/24.png') }});">
        <div class="shape">
            <img src="{{ asset('assets/img/illustration/13.png') }}" alt="Image Not Found">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-lg-12">
                    <div class="callto-action text-light">
                        <h2 class="title">Join the Agricultural Revolution with Plantix-AI</h2>
                        <p>
                            Empower your farm with intelligent technology designed for Pakistani agriculture. From wheat
                            and rice to cotton and sugarcane, our AI-powered platform helps you detect diseases early,
                            optimize fertilizer usage, and make smarter decisions that increase profitability. Join
                            thousands of farmers already transforming their yields with data-driven insights.
                        </p>
                        <a href="{{ route('plantix-ai') }}">Explore AI Features</a>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-12">
                    <div class="brand">
                        <div class="brand-style-one-carousel swiper">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/1.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/2.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/3.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/4.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Call To Action -->
@endsection

