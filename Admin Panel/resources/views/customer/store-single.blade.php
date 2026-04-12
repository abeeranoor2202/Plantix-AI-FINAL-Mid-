@extends('layouts.frontend')

@section('title', $store->title . ' - Shop | Plantix-AI')

@section('page_styles')
    <style>
        .store-header-banner {
            height: 350px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('{{ $store->cover_photo ? Storage::url($store->cover_photo) : asset('assets/img/field.jpg') }}');
            background-size: cover;
            background-position: center;
            position: relative;
            margin-top: -80px; /* Pull up under header if transparent */
            z-index: 1;
        }
        
        .store-profile-container {
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }
        
        .store-profile-card {
            background: white;
            border-radius: var(--agri-radius-lg);
            box-shadow: var(--agri-shadow-lg);
            padding: 30px;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .store-profile-logo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 4px solid white;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .store-profile-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .store-profile-logo i {
            font-size: 50px;
            color: var(--agri-primary-light);
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .store-profile-info {
            flex-grow: 1;
        }
        
        .store-stat-box {
            background: var(--agri-bg);
            border-radius: var(--agri-radius-sm);
            padding: 15px 20px;
            text-align: center;
            min-width: 120px;
        }
        
        .store-stat-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--agri-dark);
            margin-bottom: 2px;
        }
        
        .store-stat-label {
            font-size: 12px;
            color: var(--agri-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .store-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .store-info-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: var(--agri-text-main);
            font-size: 14px;
        }
        
        .store-info-list i {
            color: var(--agri-primary);
            margin-top: 3px;
            width: 16px;
            text-align: center;
        }
        
        .sec-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: var(--agri-dark);
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }
        
        .sec-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--agri-primary);
            border-radius: 3px;
        }
        
        .product-card {
            border: 1px solid var(--agri-border);
            border-radius: var(--agri-radius-md);
            overflow: hidden;
            transition: all 0.3s;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            box-shadow: var(--agri-shadow-md);
            transform: translateY(-5px);
        }
        
        .product-image-container {
            height: 200px;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .product-image-container img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        
        .product-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .review-card {
            background: white;
            border-radius: var(--agri-radius-md);
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid var(--agri-border);
            height: 100%;
        }
        
        .review-stars {
            color: #f59e0b;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .store-profile-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .store-info-list {
                grid-template-columns: 1fr;
            }
            .store-info-list li {
                justify-content: center;
                text-align: left;
            }
        }
    </style>
@endsection

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')

    <!-- Banner -->
    <div class="store-header-banner">
        <div class="container-agri h-100 position-relative">
            @if($store->is_approved)
                <div class="position-absolute" style="top: 100px; right: 20px; background: rgba(16, 185, 129, 0.9); color: white; padding: 8px 16px; border-radius: 30px; font-weight: 700; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <i class="fas fa-check-circle me-1"></i> Verified Partner
                </div>
            @endif
        </div>
    </div>

    <!-- Store Profile Header -->
    <div class="store-profile-container container-agri pb-5 mb-5 border-bottom">
        <div class="store-profile-card">
            
            <div class="store-profile-logo">
                @if($store->image)
                    <img src="{{ Storage::url($store->image) }}" alt="{{ $store->title }}">
                @else
                    <i class="fas fa-store"></i>
                @endif
            </div>
            
            <div class="store-profile-info w-100">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-4">
                    <div>
                        <h1 class="fw-bold mb-2 text-dark" style="font-size: 32px; font-family: 'Outfit', sans-serif;">{{ $store->title }}</h1>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-light text-dark border"><i class="fas fa-certificate text-success me-1"></i> Official Vendor</span>
                            <div class="text-warning">
                                <i class="fas fa-star"></i> <span class="text-dark fw-bold ms-1">{{ number_format($store->rating, 1) }}</span>
                                <span class="text-muted ms-1">({{ $store->review_count }} Reviews)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <div class="store-stat-box">
                            <div class="store-stat-value">{{ number_format($store->products()->active()->count()) }}</div>
                            <div class="store-stat-label">Products</div>
                        </div>
                        <div class="store-stat-box">
                            <div class="store-stat-value">Rs. {{ number_format($store->delivery_fee) }}</div>
                            <div class="store-stat-label">Delivery Fee</div>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted mb-4" style="font-size: 16px; line-height: 1.6;">
                    {{ $store->description ?? 'Providing premium agricultural supplies and inputs to farmers across Pakistan.' }}
                </p>
                
                <div class="store-info-list" style="border-top: 1px dashed var(--agri-border); padding-top: 20px;">
                    @if($store->address)
                        <li><i class="fas fa-map-marker-alt"></i> <span><strong>Address:</strong><br>{{ $store->address }}</span></li>
                    @endif
                    @if($store->phone)
                        <li><i class="fas fa-phone-alt"></i> <span><strong>Phone:</strong><br><a href="tel:{{ $store->phone }}" class="text-dark text-decoration-none">{{ $store->phone }}</a></span></li>
                    @endif
                    <li><i class="fas fa-clock"></i> <span><strong>Operating Hours:</strong><br>{{ \Carbon\Carbon::parse($store->open_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($store->close_time)->format('h:i A') }}</span></li>
                    <li><i class="fas fa-money-bill-wave"></i> <span><strong>Min. Order:</strong><br>Rs. {{ number_format($store->min_order_amount) }}</span></li>
                </div>
                
            </div>
            
        </div>
    </div>

    <!-- Store Products -->
    <div class="container-agri pb-5 mb-5">
        <h2 class="sec-title h3">Available Products</h2>
        
        @if($products->count() > 0)
            <div class="row g-4 mb-4">
                @foreach($products as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card h-100 position-relative">
                            @if($product->is_on_sale)
                                <div class="position-absolute top-0 start-0 m-3 badge bg-danger" style="z-index: 2;">Sale</div>
                            @endif
                            <a href="{{ route('shop.single', $product->id) }}" class="text-decoration-none">
                                <div class="product-image-container">
                                    <img src="{{ $product->primaryImage ? Storage::url($product->primaryImage->path) : asset('assets/img/products/urea_sona.png') }}" class="img-fluid" alt="{{ $product->name }}">
                                </div>
                            </a>
                            <div class="product-details position-relative">
                                <div class="text-muted small mb-2 d-flex justify-content-between">
                                    <span>{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                    <span class="text-warning"><i class="fas fa-star"></i> {{ number_format($product->rating_avg ?? 0, 1) }}</span>
                                </div>
                                <a href="{{ route('shop.single', $product->id) }}" class="text-decoration-none text-dark">
                                    <h5 class="fw-bold fs-6 mb-2">{{ Str::limit($product->name, 45) }}</h5>
                                </a>
                                <p class="small text-muted mb-3 flex-grow-1">{{ Str::limit(strip_tags($product->description), 50) }}</p>
                                
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <div>
                                        @if($product->is_on_sale && $product->discount_price)
                                            <div class="fw-bold text-success fs-5">Rs.{{ number_format($product->discount_price) }}</div>
                                            <div class="text-muted text-decoration-line-through small">Rs.{{ number_format($product->price) }}</div>
                                        @else
                                            <div class="fw-bold text-success fs-5">Rs.{{ number_format($product->price) }}</div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success rounded-circle shadow-sm" style="width: 35px; height: 35px;" onclick="window.location.href='{{ route('shop.single', $product->id) }}'" title="View Product">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5 bg-light rounded shadow-sm border">
                <i class="fas fa-box-open text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-dark">No Products Available</h4>
                <p class="text-muted">This vendor hasn't listed any active products yet.</p>
            </div>
        @endif
    </div>

    <!-- Store Reviews section (if any) -->
    @if($reviews->count() > 0)
        <div class="container-agri pb-5 mb-5">
            <h2 class="sec-title h4">Recent Reviews</h2>
            <div class="row g-4">
                @foreach($reviews as $review)
                    <div class="col-md-6">
                        <div class="review-card">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 45px; height: 45px; border-radius: 50%; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                                        {{ Str::upper(substr($review->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $review->user->name ?? 'Guest User' }}</h6>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="review-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-light' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <h6 class="fw-bold" style="font-size: 15px;">{{ $review->product->name ?? 'Product' }}</h6>
                            @if($review->comment)
                            <p class="text-muted mb-0" style="font-size: 14px; font-style: italic;">"{{ $review->comment }}"</p>
                            @endif
                            @if(!empty($review->review_images))
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @foreach($review->review_images as $image)
                                        <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ asset('storage/' . $image) }}" alt="Review image" style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
