@extends('layouts.app')

@section('title', 'Plantix-AI')

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

    <!-- Start Breadcrumb 
    ============================================= -->
    <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>About Us</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">About</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start About 
    ============================================= -->
    <div class="about-style-one-area default-padding overflow-hidden">
        <div class="container">
            <div class="row align-center">
                <div class="col-xl-6 col-lg-5">
                    <div class="about-style-one-thumb">
                        <img src="{{ asset('assets/img/3.jpg') }}" alt="Image Not Found">
                        <div class="animation-shape">
                            <img src="{{ asset('assets/img/illustration/1.png') }}" alt="Image Not Found">
                        </div>
                    </div>
                </div>
                <div class="col-xl-5 offset-xl-1 col-lg-6 offset-lg-1">
                    <div class="about-style-one-info">

                        <h2 class="title">About <br> Plantix-AI</h2>
                        <p>
                            Plantix-AI is an intelligent agriculture platform designed for Pakistani farmers. We combine
                            AI-powered crop diagnostics, localized advisory, and precision fertilizer planning to help
                            you boost yields, cut input costs, and farm sustainably. From wheat and rice to cotton and
                            sugarcane, we deliver data-driven insights right when you need them.
                        </p>
                        <div class="fun-fact-style-flex mt-35">
                            <div class="counter">
                                <div class="timer" data-to="38" data-speed="2000">38</div>
                                <div class="operator">K</div>
                            </div>
                            <span class="medium">Farmers Using <br> Plantix-AI</span>
                        </div>
                        <ul class="top-feature">
                            <li>
                                <div class="icon">
                                    <img src="{{ asset('assets/img/icon/3.png') }}" alt="Image Not Found">
                                </div>
                                <div class="info">
                                    <h4>AI Disease Detection</h4>
                                    <p>
                                        Identify crop diseases and pests early using image analysis and receive
                                        actionable, localized treatments.
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <img src="{{ asset('assets/img/icon/2.png') }}" alt="Image Not Found">
                                </div>
                                <div class="info">
                                    <h4>Fertilizer Optimization</h4>
                                    <p>
                                        Get precise nutrient plans tailored to soil, crop stage, and weather—reduce
                                        waste and improve yield.
                                    </p>
                                </div>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End About -->

    <!-- Start Timeline 
    ============================================= -->
    <div class="timeline-area default-padding-bottom" style="background-image: url({{ asset('assets/img/shape/21.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="site-heading">
                        <h2 class="title">Our Journey to <br> Smarter Farming</h2>
                        <div class="row">
                            <div class="col-xl-10 offset-xl-2">
                                <p>
                                    From pilot projects with local farmers to nationwide rollouts, Plantix-AI keeps
                                    evolving to deliver practical, high-impact tools for Pakistan’s agriculture.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="timeline-items">
                        <!-- Single Item -->
                        <div class="timeline-item">
                            <h2>2023</h2>
                            <h4>Plantix-AI Beta Launch</h4>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="timeline-item">
                            <h2>2024</h2>
                            <h4>Mobile App & Disease Detection</h4>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="timeline-item">
                            <h2>2025 Q1</h2>
                            <h4>Precision Fertilizer Engine v2</h4>
                        </div>
                        <!-- End Single Item -->
                        <!-- Single Item -->
                        <div class="timeline-item">
                            <h2>2025 Q3</h2>
                            <h4>Partnerships with Local Agencies</h4>
                        </div>
                        <!-- End Single Item -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Timeline -->

    <!-- Start Choose Us 
    ============================================= -->
    <div class="choose-us-style-three-area default-padding bg-dark text-light">
        <div class="illustration-bottom">
            <img src="{{ asset('assets/img/illustration/17.png') }}" alt="Image Not Found">
        </div>
        <div class="shape" style="background-image: url({{ asset('assets/img/farmer1.jpg') }});"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-6 pl-60 pl-md-15 pl-xs-15">
                    <h2 class="title">Why Plantix-AI <br> for Farmers</h2>
                    <p>
                        Empower your farm with AI-driven insights. Detect diseases early, apply the right nutrients at
                        the right time, and plan your cropping with confidence using localized, data-backed
                        recommendations.
                    </p>
                    <div class="list-grid">

                        <div class="achivement-content">
                            <div class="item">
                                <div class="progressbar">
                                    <div class="circle" data-percent="87">
                                        <strong></strong>
                                    </div>
                                </div>
                                <h4>Precision Recommendations</h4>
                            </div>
                        </div>
                        <ul class="list-item">
                            <li>AI disease identification</li>
                            <li>Fertilizer optimization plans</li>
                            <li>Localized advisory & alerts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Choose Us -->

    <!-- Start Team 
    ============================================= -->
    <div class="team-style-one-area default-padding">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-4">
                    <h4 class="sub-title">Expert Team</h4>
                    <h2 class="title">Meet our agricultural technology specialists</h2>
                    <a class="btn btn-theme secondary mt-10 btn-md radius animation" href="{{ route('plantix-ai') }}">Explore
                        Plantix-AI</a>
                </div>
                <div class="col-lg-7 offset-lg-1">
                    <div class="team-style-one-carousel swiper">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">

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
                                        <span>Senior Agronomist</span>
                                        <h4><a href="#">Dr. Ahmad Khan</a></h4>
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
                                        <span>AI & Data Scientist</span>
                                        <h4><a href="#">Sarah Ali</a></h4>
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
                                        <span>Soil & Nutrient Specialist</span>
                                        <h4><a href="#">Muhammad Usman</a></h4>
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
@endsection

