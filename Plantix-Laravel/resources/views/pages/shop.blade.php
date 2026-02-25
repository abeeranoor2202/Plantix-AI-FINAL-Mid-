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
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/shop-dynamic.js') }}"></script>
    <script src="{{ asset('assets/js/reviews.js') }}"></script>
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
                    <h1>Products</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Shop</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Shop 
    ============================================= -->
    <div class="validtheme-shop-area default-padding">
        <div class="container">
            <div class="shop-listing-contentes">

                <div class="row item-flex center">
                    <div class="container">
                        <!-- ================= DYNAMIC SHOP ENHANCEMENTS BEGIN ================= -->
                        <!-- Controls Bar: Count, Sort, Per Page -->
                        <div class="shop-controls-bar" id="shopControlsBar">
                            <button id="sidebarToggle" class="sidebar-toggle-btn"><i class="fas fa-sliders-h"></i>
                                Filters</button>
                            <span id="productCount">0 products found</span>
                            <select id="sortSelect" data-label="Sort by">
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="name-az">Name: A to Z</option>
                                <option value="popularity">Popularity</option>
                            </select>
                            <select id="perPageSelect" data-label="Items per page">
                                <option value="4">4 per page</option>
                                <option value="8" selected>8 per page</option>
                                <option value="16">16 per page</option>
                            </select>
                        </div>

                        <div class="shop-main-content-wrapper">
                            <!-- Sidebar: Collapsible Filters -->
                            <aside id="sidebarFilters" class="sidebar-filters">
                                <div class="sidebar-header">
                                    <span>Filters</span>

                                </div>
                                <div class="sidebar-section">
                                    <h4>Related Categories</h4>
                                    <div id="categoryFilters"></div>
                                </div>
                                <button id="clearFilters" class="clear-filters-btn">Clear All</button>
                            </aside>

                            <!-- Product Grid -->
                            <main class="shop-grid-main">

                                <div id="productGrid" class="product-grid"></div>
                                <div id="pagination" class="pagination"></div>
                            </main>
                        </div>
                        <!-- ================= DYNAMIC SHOP ENHANCEMENTS END ================= -->
                    </div>
                </div>


                <!-- End Product Grid Vies -->


            </div>
            <!-- End Tab Content -->

        </div>
    </div>

    <!-- End Shop -->
@endsection

