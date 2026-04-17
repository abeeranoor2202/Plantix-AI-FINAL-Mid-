@extends('layouts.frontend')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
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

    <!-- Start Breadcrumb 
    ============================================= -->
    <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Plantix-AI</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Plantix-AI</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div class="default-padding" style="padding-top: 40px; padding-bottom: 20px; background: #fff;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card-agri" style="padding: 24px; border: 1px solid #E5E7EB; border-radius: 14px; background: #F9FAFB;">
                        <h4 style="margin-bottom: 8px; font-weight: 800; color: #1F2937;">Need Human Expert Help?</h4>
                        <p style="margin-bottom: 16px; color: #6B7280;">If AI guidance is unclear or urgent, escalate this chat to the expert queue for manual review.</p>
                        <form method="POST" action="{{ route('ai.chat.escalate') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 700; color: #374151;">Escalation Reason</label>
                                <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Describe what you want an expert to review...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-theme" style="border-radius: 8px;">Escalate to Expert</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Services 
    ============================================= -->
    <div class="services-style-one-area bg-gray default-padding bottom-less">
        <div class="shape-right-top" style="background-image: url({{ asset('assets/img/shape/9.png') }});"></div>
        <div class="container">

            <div class="row">
                <!-- Single Item -->
                <div class="col-lg-3 col-md-6 service-one-single">
                    <div class="service-style-one-item">
                        <div class="thumb">
                            <img src="{{ asset('assets/img/illustration/2.png') }}" alt="Crop Recommendation">
                        </div>
                        <div class="info">
                            <div class="top">
                                <h4><a href="{{ route('crop.recommendation') }}">Crop <span>Recommendation</span></a></h4>
                            </div>
                            <p>
                                Get the best crop suggestions for your field based on soil, weather, and region data.
                            </p>
                        </div>
                        <a href="{{ route('crop.recommendation') }}" class="btn-angle"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- End Single Item -->
                <!-- Single Item -->
                <div class="col-lg-3 col-md-6 service-one-single">
                    <div class="service-style-one-item">
                        <div class="thumb">
                            <img src="{{ asset('assets/img/illustration/3.png') }}" alt="Crop Planning">
                        </div>
                        <div class="info">
                            <div class="top">
                                <h4><a href="{{ route('crop.planning') }}">Crop <span>Planning</span></a></h4>
                            </div>
                            <p>
                                Plan your crop cycles for maximum yield and profitability with expert guidance.
                            </p>
                        </div>
                        <a href="{{ route('crop.planning') }}" class="btn-angle"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- End Single Item -->
                <!-- Single Item -->
                <div class="col-lg-3 col-md-6 service-one-single">
                    <div class="service-style-one-item">
                        <div class="thumb">
                            <img src="{{ asset('assets/img/illustration/4.png') }}" alt="Plant Disease Identification">
                        </div>
                        <div class="info">
                            <div class="top">
                                <h4><a href="{{ route('disease.identification') }}">Plant Disease <span>Identification</span></a>
                                </h4>
                            </div>
                            <p>
                                Instantly identify plant diseases from photos and get actionable treatment advice.
                            </p>
                        </div>
                        <a href="{{ route('disease.identification') }}" class="btn-angle"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- End Single Item -->
                <!-- Single Item -->
                <div class="col-lg-3 col-md-6 service-one-single">
                    <div class="service-style-one-item">
                        <div class="thumb">
                            <img src="{{ asset('assets/img/illustration/5.png') }}" alt="Fertilizer Recommendation">
                        </div>
                        <div class="info">
                            <div class="top">
                                <h4><a href="{{ route('fertilizer.recommendation') }}">Fertilizer <span>Recommendation</span></a>
                                </h4>
                            </div>
                            <p>
                                Receive precise fertilizer suggestions tailored to your crop and soil needs.
                            </p>
                        </div>
                        <a href="{{ route('fertilizer.recommendation') }}" class="btn-angle"><i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- End Single Item -->
            </div>
        </div>
    </div>


    <!-- End Services -->

    <!-- Start Product 
    ============================================= -->
    <div class="product-cat-area default-padding">
        <div class="shape-right-bottom-mini">
            <img src="{{ asset('assets/img/shape/11.png') }}" alt="Image Not Found">
        </div>
        <div class="container">
            <div class="product-cat-items">
                <div class="row align-center">
                    <div class="col-lg-5 product-cat-info">
                        <h2 class="mask-text" style="background-image: url({{ asset('assets/img/shape/28.jpg') }});">Smarter Fields
                            With AI-Powered Decisions</h2>
                    </div>
                    <div class="col-lg-6 offset-lg-1">
                        <div class="product-cat-lists text-light">
                            <div class="product-list-box">
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('crop.recommendation') }}">
                                        <img src="{{ asset('assets/img/icon/9.png') }}" alt="Crop Recommendation">
                                        <h5>Crop Recommendation</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('crop.planning') }}">
                                        <img src="{{ asset('assets/img/icon/10.png') }}" alt="Crop Planning">
                                        <h5>Crop Planning</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('disease.identification') }}">
                                        <img src="{{ asset('assets/img/icon/11.png') }}" alt="Disease Identification">
                                        <h5>Disease Identification</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('fertilizer.recommendation') }}">
                                        <img src="{{ asset('assets/img/icon/12.png') }}" alt="Fertilizer Recommendation">
                                        <h5>Fertilizer Recommendation</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('crop.recommendation') }}">
                                        <img src="{{ asset('assets/img/icon/13.png') }}" alt="Crop Recommendation">
                                        <h5>Crop Recommendation</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="product-list-item">
                                    <a href="{{ route('crop.planning') }}">
                                        <img src="{{ asset('assets/img/icon/14.png') }}" alt="Crop Planning">
                                        <h5>Crop Planning</h5>
                                    </a>
                                </div>
                                <!-- End Single Item -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Cat -->

    <!-- Start Testimonial 
    ============================================= -->
    <div class="testimonial-style-two-area default-padding bg-gray"
        style="background-image: url({{ asset('assets/img/shape/27.png') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="site-heading text-center">
                        <h5 class="sub-title">Farmers’ Reviews</h5>
                        <h2 class="title">What farmers say</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="testimonial-style-two-carousel swiper text-center">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Single item -->
                            <div class="swiper-slide">
                                <div class="testimonial-style-two">
                                    <div class="item-info">
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <p>
                                            “Plantix-AI helped me switch to the right wheat variety and fertilizer dose
                                            for my soil in Sheikhupura. My input costs dropped and yield went up this
                                            season.”
                                        </p>
                                    </div>
                                    <div class="provider">
                                        <div class="thumb">
                                            <img src="{{ asset('assets/img/farmer4.jpg') }}" alt="Image Not Found">
                                            <div class="quote">
                                                <img src="{{ asset('assets/img/shape/quote.png') }}" alt="Image Not Found">
                                            </div>
                                        </div>
                                        <div class="info">
                                            <div class="content">
                                                <h4>Muhammad Ali</h4>
                                                <span>Wheat farmer, Punjab</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single item -->
                            <!-- Single item -->
                            <div class="swiper-slide">
                                <div class="testimonial-style-two">
                                    <div class="item-info">
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <p>
                                            “The app flagged early signs of cotton leaf curl from my phone photo and
                                            suggested treatment. We contained the spread in time in our Multan fields.”
                                        </p>
                                    </div>
                                    <div class="provider">
                                        <div class="thumb">
                                            <img src="{{ asset('assets/img/farmer2.jpg') }}" alt="Image Not Found">
                                            <div class="quote">
                                                <img src="{{ asset('assets/img/shape/quote.png') }}" alt="Image Not Found">
                                            </div>
                                        </div>
                                        <div class="info">
                                            <div class="content">
                                                <h4>Ayesha Khan</h4>
                                                <span>Cotton grower, Multan</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single item -->
                            <!-- Single item -->
                            <div class="swiper-slide">
                                <div class="testimonial-style-two">
                                    <div class="item-info">
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <p>
                                            “Monsoon forecasts and the crop plan kept our transplanting on schedule. The
                                            fertilizer plan saved nearly 25% without affecting rice quality.”
                                        </p>
                                    </div>
                                    <div class="provider">
                                        <div class="thumb">
                                            <img src="{{ asset('assets/img/farmer1.jpg') }}" alt="Image Not Found">
                                            <div class="quote">
                                                <img src="{{ asset('assets/img/shape/quote.png') }}" alt="Image Not Found">
                                            </div>
                                        </div>
                                        <div class="info">
                                            <div class="content">
                                                <h4>Bilal Ahmed</h4>
                                                <span>Rice farmer, Sindh</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single item -->
                        </div>

                        <div class="swiper-pagination"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Testimonial -->

    <!-- Start Choose Us Area
    ============================================= -->
    <div class="choose-us-style-one-area default-padding">
        <div class="container">
            <div class="row align-center">
                <div class="col-xl-5 col-lg-5">
                    <div class="choose-us-thumb">
                        <img src="{{ asset('assets/img/illustration/9.png') }}" alt="Image Not Found">
                    </div>
                </div>
                <div class="col-xl-6 offset-xl-1 col-lg-7">
                    <div class="achivement-items">
                        <ul class="list-details">
                            <li>
                                <h4>AI‑Powered Decisions</h4>
                                <p>
                                    Recommendations use soil data, local weather, and regional best practices to guide
                                    crop choice, nutrition, and protection plans that boost yield.
                                </p>
                            </li>
                            <li>
                                <h4>Made for Global</h4>
                                <p>
                                    Designed for diverse crops, climates, and markets worldwide with localized insights
                                    and multilingual content.
                                </p>
                            </li>
                        </ul>
                        <div class="achivement-content">
                            <div class="item">
                                <div class="progressbar">
                                    <div class="circle" data-percent="92">
                                        <strong></strong>
                                    </div>
                                </div>
                                <h4>Precision Recommendations</h4>
                            </div>
                            <div class="item">
                                <div class="progressbar">
                                    <div class="circle" data-percent="88">
                                        <strong></strong>
                                    </div>
                                </div>
                                <h4>Disease Detection Accuracy</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Choose Us -->

    <!-- Start Brand
    ============================================= -->
    <div class="brand-style-two-area text-center bg-gray default-padding">
        <div class="container">
            <div class="brand-style-two">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="brand5col swiper">
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
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/5.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <img src="{{ asset('assets/img/brand/3.png') }}" alt="Thumb">
                                </div>
                                <!-- End Single Item -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Brand -->
            </div>
        </div>
    </div>
    <!-- End Brand -->
@endsection

