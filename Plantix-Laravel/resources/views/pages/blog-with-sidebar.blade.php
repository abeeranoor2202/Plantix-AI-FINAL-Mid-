@extends('layouts.app')

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
    <div class="breadcrumb-area text-center shadow dark bg-fixed text-light"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Forum & Discussions</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Forum</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Blog
    ============================================= -->
    <div class="blog-area full-blog default-padding">
        <div class="container">
            <div class="blog-items">
                <div class="row">
                    <div class="blog-content col-xl-8 col-lg-7 col-md-12 pr-35 pr-md-15 pl-md-15 pr-xs-15 pl-xs-15">
                        <div class="blog-item-box">
                            <!-- Single Item -->
                            <div class="item">
                                <div class="thumb">
                                    <a href="{{ route('blog.single') }}"><img
                                            src="{{ asset('assets/img/blog/cotton_leaf_curl.png') }}"
                                            alt="Cotton leaf curl symptoms"></a>
                                    <div class="date"><strong>18</strong> <span>April, 2022</span></div>
                                </div>
                                <div class="info">
                                    <div class="meta">
                                        <ul>
                                            <li>
                                                <a href="#">Admin</a>
                                            </li>
                                            <li>
                                                <a href="#">26 Comments</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <h2 class="title">
                                        <a href="{{ route('blog.single') }}">How do you detect cotton leaf curl
                                            early?</a>
                                    </h2>
                                    <p>
                                        Share photos, symptoms, and what AI flagged. Treatments that worked for you and
                                        weather considerations in your region.
                                    </p>
                                    <a class="btn mt-10 btn-md btn-theme animation"
                                        href="{{ route('blog.single') }}">Join Discussion</a>
                                </div>
                            </div>
                            <!-- Single Item -->
                            <!-- Single Item -->
                            <div class="item">
                                <div class="thumb">
                                    <a href="{{ route('blog.single') }}"><img src="{{ asset('assets/img/blog/maize_urea.png') }}"
                                            alt="Farmer applying urea to maize"></a>
                                    <div class="date"><strong>26</strong> <span>July, 2022</span></div>
                                </div>
                                <div class="info">
                                    <div class="meta">
                                        <ul>
                                            <li>
                                                <a href="#">Admin</a>
                                            </li>
                                            <li>
                                                <a href="#">35 Comments</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <h2 class="title">
                                        <a href="{{ route('blog.single') }}">Best urea split schedule for Kharif
                                            maize?</a>
                                    </h2>
                                    <p>
                                        Discuss soil tests, rainfall forecasts, and how Plantix‑AI recommends
                                        stage-based nitrogen for higher efficiency.
                                    </p>
                                    <a class="btn mt-10 btn-md btn-theme animation"
                                        href="{{ route('blog.single') }}">Join Discussion</a>
                                </div>
                            </div>
                            <!-- Single Item -->
                            <!-- Single Item -->
                            <div class="item">
                                <div class="thumb">
                                    <a href="{{ route('blog.single') }}"><img src="{{ asset('assets/img/blog/rice_flood.png') }}"
                                            alt="Rice paddy field in flood"></a>
                                    <div class="date"><strong>12</strong> <span>March, 2022</span></div>
                                </div>
                                <div class="info">
                                    <div class="meta">
                                        <ul>
                                            <li>
                                                <a href="#">Admin</a>
                                            </li>
                                            <li>
                                                <a href="#">48 Comments</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <h2 class="title">
                                        <a href="{{ route('blog.single') }}">Which rice varieties handled flooding
                                            better?</a>
                                    </h2>
                                    <p>
                                        Share your field experience and compare with Plantix‑AI crop planning
                                        suggestions for flood-prone areas.
                                    </p>
                                    <a class="btn mt-10 btn-md btn-theme animation"
                                        href="{{ route('blog.single') }}">Join Discussion</a>
                                </div>
                            </div>
                            <!-- Single Item -->
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-md-12 pagi-area text-center">
                                <nav aria-label="navigation">
                                    <ul class="pagination">
                                        <li class="page-item"><a class="page-link" href="{{ route('blog') }}"><i
                                                    class="fas fa-angle-double-left"></i></a></li>
                                        <li class="page-item active"><a class="page-link"
                                                href="{{ route('blog') }}">1</a></li>
                                        <li class="page-item"><a class="page-link" href="{{ route('blog') }}">2</a>
                                        </li>
                                        <li class="page-item"><a class="page-link" href="{{ route('blog') }}">3</a>
                                        </li>
                                        <li class="page-item"><a class="page-link" href="{{ route('blog') }}"><i
                                                    class="fas fa-angle-double-right"></i></a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
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
                                                <img src="{{ asset('assets/img/blog/cotton_leaf_curl.png') }}" alt="Cotton leaf curl">
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
                                                <img src="{{ asset('assets/img/blog/maize_urea.png') }}" alt="Maize urea">
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
                                                <img src="{{ asset('assets/img/blog/rice_flood.png') }}" alt="Rice flood">
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
                                            <a href="{{ route('disease-identification') }}">
                                                <img src="{{ asset('assets/img/blog/cotton_leaf_curl.png') }}"
                                                    alt="Leaf disease diagnosis">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('fertilizer-recommendation') }}">
                                                <img src="{{ asset('assets/img/blog/maize_urea.png') }}"
                                                    alt="Fertilizer recommendation plan">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('crop-recommendation') }}">
                                                <img src="{{ asset('assets/img/blog/rice_flood.png') }}" alt="Best crop to grow">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('crop-planning') }}">
                                                <img src="{{ asset('assets/img/blog/maize_urea.png') }}"
                                                    alt="Crop calendar and planning">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('plantix-ai') }}">
                                                <img src="{{ asset('assets/img/blog/ai_farming.jpg') }}" alt="AI for farming overview">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('blog') }}">
                                                <img src="{{ asset('assets/img/blog/community_discussion.png') }}"
                                                    alt="Community discussions">
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
                                            <a href="#">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="twitter">
                                            <a href="#">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="pinterest">
                                            <a href="#">
                                                <i class="fab fa-pinterest"></i>
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
                            <div class="sidebar-item tags">
                                <h4 class="title">Tags</h4>
                                <div class="sidebar-info">
                                    <ul>
                                        <li><a href="{{ route('blog') }}">Plantix-AI</a></li>
                                        <li><a href="{{ route('blog') }}">Disease Detection</a></li>
                                        <li><a href="{{ route('blog') }}">Fertilizer</a></li>
                                        <li><a href="{{ route('blog') }}">Crop Planning</a></li>
                                        <li><a href="{{ route('blog') }}">Weather</a></li>
                                        <li><a href="{{ route('blog') }}">Irrigation</a></li>
                                        <li><a href="{{ route('blog') }}">Yield</a></li>
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

