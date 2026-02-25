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
                    <h1>Contact Us</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Contact</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Contact Us 
    ============================================= -->
    <div class="contact-area contact-page overflow-hidden bg-gray default-padding">
        <div class="sahpe-right-bottom">
            <img src="{{ asset('assets/img/shape/16.png') }}" alt="Image Not Found">
        </div>
        <div class="container">
            <div class="row align-center">

                <div class="col-tact-stye-one col-xl-7 col-lg-7">
                    <div class="contact-form-style-one mb-md-50">
                        <img src="{{ asset('assets/img/illustration/10.png') }}" alt="Image Not Found">
                        <h5 class="sub-title">Need Farming Help?</h5>
                        <h2 class="heading">Send us a message</h2>
                        <form action="assets/mail/contact.php" method="POST" class="contact-form contact-form">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input class="form-control" id="name" name="name" placeholder="Full Name *"
                                            type="text" required data-label="Full name">
                                        <!-- friendly label used by strict-validation.js -->
                                        
                                        <span class="alert-error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <input class="form-control" id="email" name="email" placeholder="Email *"
                                            type="email" required data-label="Email address">
                                        <span class="alert-error"></span>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <input class="form-control" id="phone" name="phone"
                                            placeholder="Phone (optional)" type="text" data-label="Phone number (include country code, optional)">
                                        <span class="alert-error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group comments">
                                        <textarea class="form-control" id="comments" name="comments"
                                            placeholder="Share your farming query (crop, location, issue) *"
                                            required data-label="Your message"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="submit" name="submit" id="submit">
                                        <i class="fa fa-paper-plane"></i> Get in Touch
                                    </button>
                                </div>
                            </div>
                            <!-- Alert Message -->
                            <div class="col-lg-12 alert-notification">
                                <div id="message" class="alert-msg"></div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-tact-stye-one col-xl-5 col-lg-5 pl-80 pl-md-15 pl-xs-15">
                    <div class="contact-style-one-info">
                        <h2>
                            Contact
                            <span>
                                Plantix-AI Team
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150"
                                    preserveAspectRatio="none">
                                    <path
                                        d="M14.4,111.6c0,0,202.9-33.7,471.2 0c0,0-194-8.9-397.3,24.7c0,0,141.9-5.9,309.2,0"
                                        style="animation-play-state: running;"></path>
                                </svg>
                            </span>
                        </h2>
                        <p>
                            Reach out for AI-powered crop recommendations, disease identification support, or fertilizer
                            planning. We serve farmers across Pakistan.
                        </p>
                        <ul>
                            <li>
                                <div class="content">
                                    <h5 class="title">Hotline</h5>
                                    <a href="tel:+923001234567">+92 300 1234567</a>
                                </div>
                            </li>
                            <li>
                                <div class="info">
                                    <h5 class="title">Our Location</h5>
                                    <p>
                                        GIMS, <br> Gujrat, Punjab, Pakistan
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="info">
                                    <h5 class="title">Official Email</h5>
                                    <a href="mailto:support@plantix-ai.pk">support@plantix-ai.pk</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- End Contact -->
@endsection

