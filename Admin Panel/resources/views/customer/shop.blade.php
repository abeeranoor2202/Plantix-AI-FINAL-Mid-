@extends('layouts.frontend')

@section('title', 'AgriTech Shop | Plantix-AI')

@section('page_styles')
    <link rel="stylesheet" href="{{ asset('assets/css/shop-dynamic.css') }}">
    <style>
        /* Custom tweaks for the shop wrapper to blend with AgriTech */
        .shop-controls-bar {
            background: white;
            border-radius: var(--agri-radius-md);
            padding: 15px 25px;
            box-shadow: var(--agri-shadow-sm);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border: none;
        }
        .shop-controls-bar select {
            border: 1px solid var(--agri-border);
            border-radius: var(--agri-radius-sm);
            padding: 8px 30px 8px 15px;
            background-color: var(--agri-bg);
            color: var(--agri-text-main);
            font-size: 14px;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
            outline: none;
        }
        .shop-controls-bar select:focus {
            border-color: var(--agri-primary);
        }
        .shop-controls-bar button.sidebar-toggle-btn {
            background: var(--agri-primary-light);
            color: var(--agri-primary);
            border: inline;
            border-radius: var(--agri-radius-sm);
            padding: 8px 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .shop-controls-bar button.sidebar-toggle-btn:hover {
            background: var(--agri-primary);
            color: white;
        }
        .sidebar-filters {
            background: white !important;
            border-radius: var(--agri-radius-md) !important;
            box-shadow: var(--agri-shadow-sm) !important;
            border: none !important;
            padding: 25px !important;
        }
        .sidebar-header {
            border-bottom: 2px solid var(--agri-bg) !important;
            padding-bottom: 15px !important;
            margin-bottom: 20px !important;
            font-weight: 700 !important;
            color: var(--agri-dark) !important;
        }
        .sidebar-section h4 {
            font-size: 16px !important;
            font-weight: 700 !important;
            margin-bottom: 15px !important;
            color: var(--agri-dark) !important;
        }
        .clear-filters-btn {
            background: var(--agri-bg) !important;
            color: var(--agri-text-main) !important;
            border-radius: var(--agri-radius-sm) !important;
            padding: 10px !important;
            font-weight: 600 !important;
            transition: all 0.2s !important;
        }
        .clear-filters-btn:hover {
            background: var(--agri-secondary) !important;
            color: var(--agri-text-main) !important;
        }
        /* Advanced filter elements */
        .filter-scroll { max-height: 180px; overflow-y: auto; padding-right: 4px; }
        .filter-scroll::-webkit-scrollbar { width: 4px; }
        .filter-scroll::-webkit-scrollbar-thumb { background: var(--agri-border); border-radius: 4px; }
        .sidebar-section label {
            display: flex; align-items: center; gap: 10px;
            color: var(--agri-text-muted); margin-bottom: 8px;
            cursor: pointer; transition: color 0.2s; font-size: 14px;
        }
        .sidebar-section label:hover { color: var(--agri-primary); }
        .sidebar-section label input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: var(--agri-primary); cursor: pointer; flex-shrink: 0;
        }
        .rating-btn {
            background: none; border: 1px solid var(--agri-border);
            border-radius: var(--agri-radius-sm); padding: 5px 10px;
            font-size: 12px; color: var(--agri-text-muted);
            cursor: pointer; transition: all 0.2s; text-align: left; width: 100%;
        }
        .rating-btn:hover, .rating-btn.active {
            background: var(--agri-primary-light); border-color: var(--agri-primary); color: var(--agri-primary);
        }
        .rating-btn .fa-star { color: #f59e0b; font-size: 11px; }
        #filterBadge {
            background: #ef4444; color: white; border-radius: 50%;
            width: 18px; height: 18px; font-size: 10px; font-weight: 700;
            display: none; align-items: center; justify-content: center; margin-left: 2px;
        }
        .price-inputs { display: flex; gap: 8px; align-items: center; }
        .price-inputs input {
            width: 90px; border: 1px solid var(--agri-border); border-radius: var(--agri-radius-sm);
            padding: 6px 10px; font-size: 13px; outline: none;
        }
        .price-inputs input:focus { border-color: var(--agri-primary); }
        .price-apply-btn {
            background: var(--agri-primary); color: white; border: none;
            border-radius: var(--agri-radius-sm); padding: 6px 12px;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;
        }
        .price-apply-btn:hover { opacity: 0.85; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-card:hover .product-img-hover {
            transform: scale(1.05);
        }
        .pagination button {
            background: white !important;
            border: 1px solid var(--agri-border) !important;
            color: var(--agri-text-main) !important;
            border-radius: 8px !important;
            min-width: 40px !important;
            height: 40px !important;
            font-weight: 600 !important;
            transition: all 0.2s !important;
        }
        .pagination button.active, .pagination button:hover:not(:disabled) {
            background: var(--agri-primary) !important;
            border-color: var(--agri-primary) !important;
            color: white !important;
        }
        #productCount {
            font-weight: 600;
            color: var(--agri-text-muted);
            margin-right: auto;
        }
    </style>
@endsection

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
// Backend-supplied shop data — consumed by shop-dynamic.js
window.SHOP_DATA = {
    products  : @json($shopData),
    brands    : @json($brandList),
    vendors   : @json($vendorList),
    priceRange: { min: {{ $priceMin }}, max: {{ $priceMax }} },
};
// Cart route map for cart.js
window.CART_ROUTES = {
    'cart.add'    : '{{ route('cart.add') }}',
    'cart.remove' : '{{ url('/cart') }}/{id}',
    'cart.update' : '{{ url('/cart') }}/{id}',
    'cart.count'  : '{{ route('cart.count') }}',
    'cart.mini'   : '{{ route('cart.mini') }}',
    'auth'        : {{ auth('web')->check() ? 'true' : 'false' }},
    'loginUrl'    : '{{ route('signin') }}',
};
</script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/shop-dynamic.js') }}"></script>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-5 bg-light" style="background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02)); border-bottom: 1px solid var(--agri-border);">
        <div class="container-agri">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="fw-bold mb-2 text-dark" style="font-size: 32px;">Agricultural Inputs Marketplace</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-muted" aria-current="page">Shop</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                    <p class="text-muted mb-0"><i class="fas fa-check-circle text-success me-2"></i> Quality verified fertilizers &amp; micronutrients</p>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Shop -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5">
            <!-- DYNAMIC SHOP ENHANCEMENTS BEGIN -->
            <div class="shop-controls-bar" id="shopControlsBar">
                <button id="sidebarToggle" class="sidebar-toggle-btn"><i class="fas fa-sliders-h"></i> Filters <span id="filterBadge"></span></button>
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

            <div class="shop-main-content-wrapper d-flex gap-4 align-items-start">
                <!-- Sidebar: Collapsible Filters -->
                <aside id="sidebarFilters" class="sidebar-filters d-none d-lg-block" style="width: 280px; flex-shrink: 0;">
                    <div class="sidebar-header d-flex justify-content-between align-items-center">
                        <span class="fs-5">Filters</span>
                        <i class="fas fa-times d-lg-none" id="sidebarClose" style="cursor:pointer;"></i>
                    </div>

                    {{-- Price Range --}}
                    <div class="sidebar-section">
                        <h4><i class="fas fa-tag me-2 text-success" style="font-size:14px;"></i>Price Range</h4>
                        <div class="price-inputs mt-2">
                            <input type="number" id="priceMin" min="0" placeholder="Min PKR">
                            <span class="text-muted">—</span>
                            <input type="number" id="priceMax" min="0" placeholder="Max PKR">
                        </div>
                        <button id="applyPrice" class="price-apply-btn mt-2 w-100"><i class="fas fa-check me-1"></i> Apply</button>
                    </div>

                    {{-- On Sale --}}
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-fire me-2 text-success" style="font-size:14px;"></i>Deals</h4>
                        <label class="mt-2">
                            <input type="checkbox" id="onSaleFilter">
                            On Sale Only
                        </label>
                    </div>

                    {{-- Min Rating --}}
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-star me-2 text-success" style="font-size:14px;"></i>Min Rating</h4>
                        <div id="ratingFilter" class="d-flex flex-column gap-1 mt-2"></div>
                    </div>

                    {{-- Categories --}}
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-layer-group me-2 text-success" style="font-size:14px;"></i>Categories</h4>
                        <div id="categoryFilters" class="d-flex flex-column filter-scroll mt-2"></div>
                    </div>

                    {{-- Brands --}}
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-certificate me-2 text-success" style="font-size:14px;"></i>Brand</h4>
                        <div id="brandFilters" class="d-flex flex-column filter-scroll mt-2"></div>
                    </div>

                    {{-- Stores / Vendors --}}
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-store me-2 text-success" style="font-size:14px;"></i>Store</h4>
                        <div id="vendorFilters" class="d-flex flex-column filter-scroll mt-2"></div>
                    </div>

                    {{-- Dynamic Attribute Filters --}}
                    @if(isset($filterAttributes) && $filterAttributes->count())
                    <div class="sidebar-section mt-4 pt-3 border-top">
                        <h4><i class="fas fa-sliders-h me-2 text-success" style="font-size:14px;"></i>Attribute Filters</h4>
                        <form method="GET" action="{{ route('shop') }}" class="mt-2 d-flex flex-column gap-3">
                            @if(request()->filled('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request()->filled('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request()->filled('vendor'))
                                <input type="hidden" name="vendor" value="{{ request('vendor') }}">
                            @endif
                            @if(request()->filled('min_price'))
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            @endif
                            @if(request()->filled('max_price'))
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            @endif

                            @foreach($filterAttributes as $attribute)
                                @php($attrName = $attribute->name ?: $attribute->title)
                                <div>
                                    <label class="fw-bold" style="font-size: 12px; color: var(--agri-text-heading); margin-bottom: 6px; display:block;">{{ $attrName }}</label>
                                    @if($attribute->type === 'number')
                                        <div class="price-inputs" style="gap:6px;">
                                            <input type="number" step="any" name="attr_min[{{ $attribute->id }}]" value="{{ $rawAttrMin[$attribute->id] ?? '' }}" placeholder="Min">
                                            <input type="number" step="any" name="attr_max[{{ $attribute->id }}]" value="{{ $rawAttrMax[$attribute->id] ?? '' }}" placeholder="Max">
                                        </div>
                                    @elseif(in_array($attribute->type, ['select', 'multi-select'], true))
                                        <select name="attr[{{ $attribute->id }}]" class="form-agri" style="margin-bottom:0; font-size:13px;">
                                            <option value="">Any</option>
                                            @foreach($attribute->values as $option)
                                                <option value="{{ $option->value }}" @selected(($rawAttrFilters[$attribute->id] ?? '') === $option->value)>{{ $option->value }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="attr[{{ $attribute->id }}]" class="form-agri" style="margin-bottom:0; font-size:13px;" value="{{ $rawAttrFilters[$attribute->id] ?? '' }}" placeholder="Enter value">
                                    @endif
                                </div>
                            @endforeach

                            <button type="submit" class="price-apply-btn w-100"><i class="fas fa-filter me-1"></i> Apply Attribute Filters</button>
                        </form>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <button id="clearFilters" class="clear-filters-btn w-100 border-0"><i class="fas fa-sync-alt me-2"></i> Clear All Filters</button>
                    </div>
                </aside>

                <!-- Product Grid -->
                <main class="shop-grid-main flex-grow-1" style="min-width: 0;">
                    <div id="productGrid" class="product-grid mb-5">
                        <!-- Loaded by JS -->
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div id="pagination" class="pagination d-flex justify-content-center gap-2"></div>
                </main>
            </div>
            <!-- DYNAMIC SHOP ENHANCEMENTS END -->
        </div>
    </div>
    <!-- End Shop -->
@endsection
