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
    <div class="breadcrumb-area text-center shadow dark bg-fixed text-light"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Forum Discussion: How Plantix-AI Helps Farmers Boost Yields</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Forum Discussion</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Blog
    ============================================= -->
    <div class="blog-area single full-blog right-sidebar full-blog default-padding">
        <div class="container">
            <div class="blog-items">
                <div class="row">
                    <div class="blog-content col-xl-8 col-lg-7 col-md-12 pr-35 pr-md-15 pl-md-15 pr-xs-15 pl-xs-15">

                        <div class="blog-style-two item">
                            <div class="thumb">
                                <a href="{{ route('blog.single') }}"><img src="{{ asset('assets/img/1500x700.png') }}"
                                        alt="Thumb"></a>
                                <div class="date"><strong>18</strong> <span>April, 2022</span></div>
                            </div>
                            <div class="info">
                                <div class="meta">
                                    <ul>
                                        <li>
                                            <a href="#"><i class="fas fa-user-circle"></i> Admin</a>
                                        </li>
                                        <li>
                                            <a href="#"><i class="fas fa-comments"></i> 26 Comments</a>
                                        </li>
                                    </ul>
                                </div>
                                <p>
                                    Plantix-AI brings AI-powered crop diagnostics and precision recommendations to
                                    fields across Pakistan. By analyzing leaf images, soil conditions, and local weather
                                    data, our platform helps farmers catch diseases early, plan nutrients accurately,
                                    and make smarter decisions for wheat, rice, cotton, sugarcane, and more.
                                </p>
                                <p>
                                    Farmers using Plantix-AI report reduced input costs and improved yields. Whether you
                                    farm a few acres or manage larger operations, actionable insights like targeted
                                    sprays, stage-based fertilizer plans, and irrigation timing can make a measurable
                                    difference in profitability and sustainability.
                                </p>
                                <blockquote>
                                    “Within one season, Plantix-AI helped us identify leaf rust early and adjust
                                    nitrogen plans—our wheat yield went up while costs went down.” — Farmer, Punjab
                                </blockquote>
                                <p>
                                    Our advisory adapts to your location and crop stage. Upload a leaf photo, select
                                    your crop, and receive instant guidance backed by agronomy experts and localized
                                    data. You also get reminders for key field operations and weather-aware alerts to
                                    protect your crop at the right time.
                                </p>
                                <h3>What You Get with Plantix-AI</h3>
                                <ul>
                                    <li>AI-based disease and pest identification from images</li>
                                    <li>Precision fertilizer plans by crop stage and soil needs</li>
                                    <li>Localized weather insights and irrigation timing</li>
                                    <li>Actionable, field-ready recommendations in Urdu/English</li>
                                    <li>Expert consultation and agency partnerships when needed</li>
                                </ul>
                                <p>
                                    Ready to modernize your farm? Explore Plantix-AI’s features for crop recommendation,
                                    disease identification, and fertilizer optimization—and join thousands of farmers
                                    improving yields with data-driven agriculture.
                                </p>
                            </div>
                        </div>


                        <!-- Post Author -->
                        <div class="post-author">
                            <div class="thumb">
                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Thumb">
                            </div>
                            <div class="content">
                                <h4><a href="#">Md Sohag</a></h4>
                                <p>
                                    Md Sohag is a senior agronomist with over 10 years of experience in digital
                                    agriculture. He specializes in integrating AI tools with traditional farming
                                    practices to help farmers optimize inputs and maximize yields sustainably.
                                </p>
                            </div>
                        </div>
                        <!-- Post Author -->

                        <!-- Post Tags Share -->
                        <div class="post-tags share">
                            <div class="tags">
                                <h4>Tags: </h4>
                                <a href="#">Plantix-AI</a>
                                <a href="#">Crop Health</a>
                                <a href="#">Precision Agriculture</a>
                            </div>

                            <div class="social">
                                <h4>Share:</h4>
                                <ul>
                                    <li>
                                        <a class="facebook" href="#" target="_blank"><i
                                                class="fab fa-facebook-f"></i></a>
                                    </li>
                                    <li>
                                        <a class="twitter" href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                    </li>
                                    <li>
                                        <a class="pinterest" href="#" target="_blank"><i
                                                class="fab fa-pinterest-p"></i></a>
                                    </li>
                                    <li>
                                        <a class="linkedin" href="#" target="_blank"><i
                                                class="fab fa-linkedin-in"></i></a>
                                    </li>
                                </ul><!-- End Social Share -->
                            </div>
                        </div>
                        <!-- Post Tags Share -->

                        <!-- Start Post Pagination -->
                        <div class="post-pagi-area">
                            <div class="post-previous">
                                <a href="{{ route('blog.single') }}">
                                    <div class="icon"><i class="fas fa-angle-double-left"></i></div>
                                    <div class="nav-title"> Previous Discussion <h5>How to Identify Wheat Rust Early
                                        </h5>
                                    </div>
                                </a>
                            </div>
                            <div class="post-next">
                                <a href="{{ route('blog.single') }}">
                                    <div class="nav-title">Next Discussion <h5>Fertilizer Plans that Save Costs</h5>
                                    </div>
                                    <div class="icon"><i class="fas fa-angle-double-right"></i></div>
                                </a>
                            </div>
                        </div>
                        <!-- End Post Pagination -->

                        <!-- Start Blog Comment -->
                        <div class="blog-comments">
                            <div class="comments-area">
                                <div class="comments-title">
                                    <h3>3 Comments On “How Plantix-AI Helps Pakistani Farmers Boost Yields.”</h3>
                                    <div class="comments-list">
                                        <div class="comment-item">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Author">
                                            </div>
                                            <div class="content">
                                                <div class="title">
                                                    <h5>Bubhan Prova <span class="reply"><a href="#"><i
                                                                    class="fas fa-reply"></i> Reply</a></span></h5>
                                                    <span>28 Feb, 2022</span>
                                                </div>
                                                <p>
                                                    I've been using the app for my maize crop this season, and the leaf
                                                    analysis feature is spot on. It correctly identified a fungal
                                                    infection early, allowing me to treat it before it spread. Great
                                                    tool for us!
                                                </p>
                                            </div>
                                        </div>
                                        <div class="comment-item reply">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Author">
                                            </div>
                                            <div class="content">
                                                <div class="title">
                                                    <h5>Mickel Jones <span class="reply"><a href="#"><i
                                                                    class="fas fa-reply"></i> Reply</a></span></h5>
                                                    <span>15 Mar, 2022</span>
                                                </div>
                                                <p>
                                                    The fertilizer calculator is very helpful. It saved me a lot of
                                                    money by suggesting the exact amount of urea needed based on my soil
                                                    test. Highly recommended for cost saving.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="comments-form">
                                    <div class="title">
                                        <h3>Leave a comment</h3>
                                    </div>
                                    <form action="#" class="contact-comments">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <!-- Name -->
                                                    <input name="name" class="form-control" placeholder="Name *"
                                                        type="text" required data-label="Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <!-- Email -->
                                                    <input name="email" class="form-control" placeholder="Email *"
                                                        type="email" required data-label="Email address">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group comments">
                                                    <!-- Comment -->
                                                    <textarea class="form-control" placeholder="Comment" required data-label="Comment"></textarea>
                                                </div>
                                                <div class="form-group full-width submit">
                                                    <button class="btn animation dark border" type="submit">Post
                                                        Comment</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Comments Form -->
                    </div>

                    <!-- Start Sidebar -->
                    <div class="sidebar col-xl-4 col-lg-5 col-md-12 mt-md-100 mt-xs-50">
                        <aside>
                            <div class="sidebar-item search">
                                <div class="sidebar-info">
                                    <form>
                                        <input type="text" placeholder="Enter Keyword" name="text" class="form-control" data-label="Search keywords">
                                        <button type="submit"><i class="fas fa-search"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="sidebar-item recent-post">
                                <h4 class="title">Recent Discussions</h4>
                                <ul>
                                    <li>
                                        <div class="thumb">
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Thumb">
                                            </a>
                                        </div>
                                        <div class="info">
                                            <a href="{{ route('blog.single') }}">How do you detect cotton leaf curl
                                                early?</a>
                                            <div class="meta-title">
                                                <span class="post-date">12 Sep, 2025</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="thumb">
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Thumb">
                                            </a>
                                        </div>
                                        <div class="info">
                                            <a href="{{ route('blog.single') }}">Best urea split schedule for Kharif
                                                maize?</a>
                                            <div class="meta-title">
                                                <span class="post-date">05 Jul, 2025</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="thumb">
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="Thumb">
                                            </a>
                                        </div>
                                        <div class="info">
                                            <a href="{{ route('blog.single') }}">Which rice varieties handled
                                                flooding better?</a>
                                            <div class="meta-title">
                                                <span class="post-date">29 Aug, 2025</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                </ul>
                            </div>
                            <div class="sidebar-item category">
                                <h4 class="title">Topics</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li>
                                            <a href="{{ route('blog') }}">Crop Health <span>69</span></a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog') }}">Fertilizer Plans <span>25</span></a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog') }}">Smart Irrigation <span>18</span></a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog') }}">AI in Agriculture <span>37</span></a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog') }}">Weather Insights <span>12</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="sidebar-item gallery">
                                <h4 class="title">Gallery</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog.single') }}">
                                                <img src="{{ asset('assets/img/800x800.png') }}" alt="thumb">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="sidebar-item archives">
                                <h4 class="title">Archives</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li><a href="{{ route('blog') }}">Aug 2020</a></li>
                                        <li><a href="{{ route('blog') }}">Sept 2020</a></li>
                                        <li><a href="{{ route('blog') }}">Nov 2020</a></li>
                                        <li><a href="{{ route('blog') }}">Dec 2020</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="sidebar-item social-sidebar">
                                <h4 class="title">follow us</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li class="facebook">
                                            <a href="#" aria-label="Follow us on Facebook" title="Facebook">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="twitter">
                                            <a href="#" aria-label="Follow us on Twitter" title="Twitter">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="pinterest">
                                            <a href="#" aria-label="Follow us on Pinterest" title="Pinterest">
                                                <i class="fab fa-pinterest"></i>
                                            </a>
                                        </li>
                                        <li class="linkedin">
                                            <a href="#" aria-label="Connect with us on LinkedIn" title="LinkedIn">
                                                <i class="fab fa-linkedin-in"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="sidebar-item tags">
                                <h4 class="title">Tags</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li><a href="{{ route('blog') }}">Plantix-AI</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Disease Detection</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Fertilizer</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Crop Planning</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Weather</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Irrigation</a>
                                        </li>
                                        <li><a href="{{ route('blog') }}">Yield</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <!-- End Start Sidebar -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Blog -->
@endsection

