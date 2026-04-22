@extends('layouts.frontend')

@section('title', $product->name . ' | Plantix-AI Shop')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
window.CART_ROUTES = {
    'cart.add'   : '{{ route("cart.add") }}',
    'cart.remove': '{{ url("/cart") }}/{id}',
    'cart.update': '{{ url("/cart") }}/{id}',
    'cart.count' : '{{ route("cart.count") }}',
    'cart.mini'  : '{{ route("cart.mini") }}',
    auth     : {{ auth("web")->check() ? "true" : "false" }},
    loginUrl : '{{ route("signin") }}',
};
</script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
@endsection

@section('content')

    <!-- Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border);">
        <div class="container-agri">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-success text-decoration-none">Shop</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Product Section -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">

            @if(session('success'))
                <div class="alert alert-success mb-4" style="border-radius: var(--agri-radius-sm);">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="card-agri border-0 p-lg-5 p-4 mb-5">
                <div class="row g-5">

                    <!-- Product Gallery -->
                    <div class="col-lg-5">
                        @php
                            $primaryImg = $product->primaryImage;
                            $allImages  = $product->images;
                        @endphp
                        <div class="position-relative bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center p-4"
                             style="height: 400px; border: 1px solid var(--agri-border);">
                            @if($product->is_on_sale)
                            <span class="badge position-absolute" style="top: 20px; left: 20px; background: var(--agri-secondary); color: var(--agri-text-main); font-weight: bold; padding: 6px 12px; font-size: 14px;">
                                -{{ round((1 - $product->effective_price / $product->price) * 100) }}% OFF
                            </span>
                            @endif
                            @if($primaryImg)
                                <img id="mainProductImg" src="{{ Storage::url($primaryImg->path) }}"
                                     class="img-fluid" alt="{{ $product->name }}"
                                     style="max-height: 100%; object-fit: contain;">
                            @else
                                <img id="mainProductImg" src="{{ asset('assets/img/products/urea_sona.png') }}"
                                     class="img-fluid" alt="{{ $product->name }}"
                                     style="max-height: 100%; object-fit: contain;">
                            @endif
                        </div>

                        @if($allImages->count() > 1)
                        <div class="d-flex gap-3 overflow-auto pb-2" style="scrollbar-width: none;">
                            @foreach($allImages as $img)
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center p-2"
                                 style="width: 80px; height: 80px; border: {{ $img->is_primary ? '2px solid var(--agri-primary)' : '1px solid var(--agri-border)' }}; cursor: pointer; flex-shrink: 0;"
                                 onclick="document.getElementById('mainProductImg').src='{{ Storage::url($img->path) }}'">
                                <img src="{{ Storage::url($img->path) }}" class="img-fluid" style="object-fit: contain;">
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="col-lg-7">
                        @if($product->category)
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-success fw-medium px-3 py-2 border"
                                  style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ $product->category->name }}
                            </span>
                        </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $product->is_returnable ? 'bg-success' : 'bg-danger' }} px-3 py-2"
                                  style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ $product->is_returnable ? 'Returnable' : 'Not Returnable' }}
                            </span>
                            <span class="badge {{ $product->is_refundable ? 'bg-info' : 'bg-secondary' }} px-3 py-2"
                                  style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ $product->is_refundable ? 'Refundable' : 'Not Refundable' }}
                            </span>
                        </div>

                        <h2 class="fw-bold mb-3 text-dark display-6">{{ $product->name }}</h2>

                        @if($product->rating_avg > 0)
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="text-warning fs-5">
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="{{ $s <= round($product->rating_avg) ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <a href="#reviews" class="text-muted text-decoration-none small">
                                ({{ $product->approvedReviews->count() }} Review{{ $product->approvedReviews->count() !== 1 ? 's' : '' }})
                            </a>
                        </div>
                        @endif

                        <div class="d-flex align-items-end gap-3 mb-4">
                            <h3 class="fw-bold text-success mb-0" style="font-size: 32px;">
                                PKR {{ number_format($product->effective_price) }}
                            </h3>
                            @if($product->is_on_sale)
                            <span class="text-muted text-decoration-line-through fs-5 mb-1">
                                PKR {{ number_format($product->price) }}
                            </span>
                            @endif
                        </div>

                        @php
                            $stockRecord = $product->stock;
                            $statusLabel = ! $product->track_stock
                                ? 'In Stock'
                                : (($stockRecord?->is_available ?? true) === false
                                    ? 'Unavailable'
                                    : ((int) ($stockRecord?->quantity ?? $product->stock_quantity ?? 0) <= 0 ? 'Out of Stock' : 'In Stock'));
                            $statusBadge = $statusLabel === 'Unavailable'
                                ? 'bg-secondary'
                                : ($statusLabel === 'Out of Stock' ? 'bg-danger' : 'bg-success');
                            $inStock = $statusLabel === 'In Stock';
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-4">
                            @if($statusLabel === 'Unavailable')
                                <i class="fas fa-minus-circle text-secondary fs-5"></i>
                            @elseif($statusLabel === 'Out of Stock')
                                <i class="fas fa-times-circle text-danger fs-5"></i>
                            @else
                                <i class="fas fa-check-circle text-success fs-5"></i>
                            @endif
                            <span class="badge rounded-pill {{ $statusBadge }}">{{ strtoupper($statusLabel) }}</span>
                            @if($product->track_stock && $statusLabel === 'In Stock')
                                <span class="text-muted ms-2">&mdash; {{ (int) ($stockRecord?->quantity ?? $product->stock_quantity ?? 0) }} units available</span>
                            @endif
                        </div>

                        @if($product->description)
                        <p class="text-muted mb-4" style="line-height: 1.8; font-size: 16px;">
                            {{ Str::limit($product->description, 300) }}
                        </p>
                        @endif

                        <hr class="my-4">

                        <!-- Add to Cart -->
                        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                            <div class="d-flex align-items-center bg-light rounded-3 p-1 border" style="width: 130px;">
                                <button type="button" class="btn btn-sm border-0 text-muted fs-5 px-3 py-2 bg-transparent"
                                        onclick="document.getElementById('productQty').stepDown()" {{ $inStock ? '' : 'disabled' }}>−</button>
                                <input type="number" id="productQty" data-product-qty="{{ $product->id }}"
                                       class="form-control border-0 text-center fw-bold bg-transparent px-0"
                                       value="1" min="1" max="{{ $product->track_stock ? (int) ($stockRecord?->quantity ?? $product->stock_quantity ?? 0) : 99 }}"
                                       {{ $inStock ? '' : 'disabled' }}
                                       style="box-shadow: none;">
                                <button type="button" class="btn btn-sm border-0 text-muted fs-5 px-3 py-2 bg-transparent"
                                        onclick="document.getElementById('productQty').stepUp()" {{ $inStock ? '' : 'disabled' }}>+</button>
                            </div>
                            @if($inStock)
                            <button class="btn-agri btn-agri-primary flex-grow-1" style="padding: 14px 24px; font-size: 16px;"
                                    data-add-to-cart="{{ $product->id }}">
                                <i class="fas fa-cart-plus me-2 fs-5"></i> Add to Cart
                            </button>
                            @else
                            <button class="btn-agri flex-grow-1" style="padding: 14px 24px; font-size: 16px; background: #e5e7eb; color: #9ca3af; cursor: not-allowed;" disabled>
                                {{ $statusLabel }}
                            </button>
                            @endif
                        </div>

                        <div class="bg-light rounded-3 p-4 border mt-2">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-3 text-muted" style="font-size: 14px;">
                                <li><i class="fas fa-shield-alt text-success me-2"></i> <strong>100% Genuine</strong> Product Guarantee</li>
                                <li><i class="fas fa-shipping-fast text-success me-2"></i> <strong>Fast Delivery</strong> via partner couriers</li>
                                <li><i class="fas fa-undo-alt text-success me-2"></i> <strong>Easy Returns</strong> within 7 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs: Description / Specs / Reviews -->
            <div class="card-agri p-0 border-0 overflow-hidden" id="reviews">
                <div class="border-bottom bg-light px-4 pt-4">
                    <ul class="nav nav-tabs border-0" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active bg-white border-bottom-0 fw-bold px-4 py-3 text-dark"
                                    style="border-radius: 8px 8px 0 0;"
                                    id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button">Description</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link bg-transparent border-0 fw-bold px-4 py-3 text-muted"
                                    id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link bg-transparent border-0 fw-bold px-4 py-3 text-muted"
                                    id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button">
                                Reviews ({{ $product->approvedReviews->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content p-5 bg-white" id="productTabsContent">

                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="desc" role="tabpanel">
                        <h4 class="fw-bold text-dark mb-4">Product Overview</h4>
                        <p class="text-muted" style="line-height: 1.8;">
                            {{ $product->description ?? 'No description available.' }}
                        </p>
                    </div>

                    <!-- Details Tab -->
                    <div class="tab-pane fade" id="info" role="tabpanel">
                        <h4 class="fw-bold text-dark mb-4">Product Details</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered border-light align-middle">
                                <tbody>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">SKU</th>
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $product->sku ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Category</th>
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $product->category?->name ?? '—' }}</td>
                                    </tr>
                                    @if($product->brand)
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Brand</th>
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $product->brand->name }}</td>
                                    </tr>
                                    @endif
                                    @if($product->vendor)
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Vendor</th>
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $product->vendor->store_name ?? $product->vendor->name }}</td>
                                    </tr>
                                    @endif
                                    @foreach($product->attributes as $attr)
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">{{ $attr->attribute?->name ?: $attr->attribute?->title ?: 'Attribute' }}</th>
                                        <td class="px-4 py-3 fw-medium text-dark">{{ $attr->display_value ?: '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="review" role="tabpanel">
                        @php
                            $allowTextReview = (bool) data_get($product, 'category.text_review_enabled', true);
                            $allowImageReview = (bool) data_get($product, 'category.image_review_enabled', false);
                        @endphp
                        <div class="row g-5">
                            <div class="col-lg-7 border-end">
                                <h4 class="fw-bold text-dark mb-4">Customer Reviews</h4>
                                @forelse($product->approvedReviews as $review)
                                <div class="mb-4 pb-4 border-bottom">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="text-warning fs-6">
                                            @for($s = 1; $s <= 5; $s++)
                                                <i class="{{ $s <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                            @endfor
                                        </div>
                                        <span class="text-muted small fw-bold">{{ $review->user?->name ?? 'Anonymous' }}</span>
                                        <span class="text-muted small ms-auto">{{ $review->created_at->format('M j, Y') }}</span>
                                    </div>
                                    @if($review->title)
                                    <h6 class="fw-bold text-dark mb-2">{{ $review->title }}</h6>
                                    @endif
                                    @if($review->comment)
                                    <p class="text-muted mb-0">{{ $review->comment }}</p>
                                    @endif
                                    @if(!empty($review->review_images))
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        @foreach($review->review_images as $image)
                                            <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ asset('storage/' . $image) }}" alt="Review image" style="width:72px;height:72px;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb;">
                                            </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                                @endforelse
                            </div>

                            <div class="col-lg-5">
                                <h4 class="fw-bold text-dark mb-4">Write a Review</h4>
                                @auth('web')
                                @if($canReviewProduct ?? false)
                                <form method="POST" action="{{ route('reviews.store', $product->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Order *</label>
                                        <select name="order_id" class="form-agri" required>
                                            <option value="">Select an order</option>
                                            @foreach($eligibleOrders as $order)
                                                <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                                                    {{ $order->order_number }} - {{ ucfirst($order->status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Your Rating *</label>
                                        <div class="d-flex gap-1 fs-4" id="starPicker">
                                            @for($s = 1; $s <= 5; $s++)
                                            <i class="far fa-star text-warning" style="cursor:pointer;" data-star="{{ $s }}"></i>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="ratingInput" value="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Title</label>
                                        <input type="text" name="title" class="form-agri" placeholder="Summary of your review" value="{{ old('title') }}">
                                    </div>
                                    @if($allowTextReview)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Your Review</label>
                                        <textarea name="comment" class="form-agri" rows="4" placeholder="Share your experience...">{{ old('comment') }}</textarea>
                                    </div>
                                    @elseif(! $allowImageReview)
                                    <div class="alert alert-info small mb-3">
                                        This category does not allow written or image reviews. You can still submit the star rating.
                                    </div>
                                    @endif
                                    @if($allowImageReview)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Review Photos</label>
                                        <input type="file" name="review_images[]" class="form-control" accept="image/*" multiple>
                                    </div>
                                    @endif
                                    <button type="submit" class="btn-agri btn-agri-primary w-100">Submit Review</button>
                                </form>
                                <script>
                                    document.querySelectorAll('#starPicker .fa-star').forEach(star => {
                                        star.addEventListener('click', function() {
                                            const val = +this.dataset.star;
                                            document.getElementById('ratingInput').value = val;
                                            document.querySelectorAll('#starPicker .fa-star').forEach((s, i) => {
                                                s.className = (i < val ? 'fas' : 'far') + ' fa-star text-warning';
                                                s.style.cursor = 'pointer';
                                            });
                                        });
                                    });
                                </script>
                                @else
                                <div class="alert alert-warning small">
                                    You can only review products you have purchased
                                </div>
                                @endif
                                @else
                                <div class="text-center py-4">
                                    <p class="text-muted mb-3">Please sign in to leave a review.</p>
                                    <a href="{{ route('signin') }}" class="btn-agri btn-agri-primary">Sign In</a>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Related Products -->
            @if($related->isNotEmpty())
            <div class="mt-5">
                <h4 class="fw-bold text-dark mb-4">Related Products</h4>
                <div class="row g-4">
                    @foreach($related as $rel)
                    @php
                        $relStock = $rel->stock;
                        $relStatusLabel = ! $rel->track_stock
                            ? 'In Stock'
                            : (($relStock?->is_available ?? true) === false
                                ? 'Unavailable'
                                : ((int) ($relStock?->quantity ?? $rel->stock_quantity ?? 0) <= 0 ? 'Out of Stock' : 'In Stock'));
                        $relInStock = $relStatusLabel === 'In Stock';
                    @endphp
                    <div class="col-sm-6 col-lg-3">
                        <div class="card-agri border-0 h-100 d-flex flex-column">
                            <div class="p-4 text-center" style="background: var(--agri-bg); border-radius: var(--agri-radius-md) var(--agri-radius-md) 0 0;">
                                <a href="{{ route('shop.single', $rel->id) }}">
                                    @if($rel->primaryImage)
                                        <img src="{{ Storage::url($rel->primaryImage->path) }}" alt="{{ $rel->name }}" style="height: 120px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('assets/img/products/urea_sona.png') }}" alt="{{ $rel->name }}" style="height: 120px; object-fit: contain;">
                                    @endif
                                </a>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold mb-1"><a href="{{ route('shop.single', $rel->id) }}" class="text-dark text-decoration-none">{{ $rel->name }}</a></h6>
                                <div class="mb-2">
                                    <span class="badge rounded-pill {{ $relStatusLabel === 'Unavailable' ? 'bg-secondary' : ($relStatusLabel === 'Out of Stock' ? 'bg-danger' : 'bg-success') }}">{{ strtoupper($relStatusLabel) }}</span>
                                </div>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="fw-bold text-success">PKR {{ number_format($rel->effective_price) }}</span>
                                    @if($relInStock)
                                        <button class="btn-agri btn-agri-outline" style="padding: 4px 12px; font-size: 13px;"
                                                data-add-to-cart="{{ $rel->id }}">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    @else
                                        <button class="btn-agri" style="padding: 4px 12px; font-size: 13px; background: #e5e7eb; color: #9ca3af; cursor: not-allowed;" disabled>
                                            {{ $relStatusLabel }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#productTabs button').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('#productTabs button').forEach(b => {
                        b.classList.remove('bg-white', 'text-dark', 'active');
                        b.classList.add('bg-transparent', 'text-muted');
                    });
                    this.classList.remove('bg-transparent', 'text-muted');
                    this.classList.add('bg-white', 'text-dark', 'active');
                });
            });
        });
    </script>

@endsection
