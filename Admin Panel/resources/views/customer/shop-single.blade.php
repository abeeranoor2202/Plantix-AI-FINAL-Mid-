@extends('layouts.frontend')

@section('title', 'Product Details | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/reviews.js') }}"></script>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border);">
        <div class="container-agri">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-success text-decoration-none">Shop</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Product Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Product Single -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">
            <div class="card-agri border-0 p-lg-5 p-4 mb-5">
                <div class="row g-5">
                    
                    <!-- Product Gallery -->
                    <div class="col-lg-5">
                        <div class="position-relative bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center p-4" style="height: 400px; border: 1px solid var(--agri-border);">
                            <span class="badge position-absolute" style="top: 20px; left: 20px; background: var(--agri-secondary); color: var(--agri-text-main); font-weight: bold; padding: 6px 12px; font-size: 14px;">-16% OFF</span>
                            <button class="btn btn-light position-absolute rounded-circle bg-white shadow-sm" style="top: 20px; right: 20px; width: 40px; height: 40px; border: none; color: var(--agri-text-muted);" title="Add to Wishlist">
                                <i class="far fa-heart fs-5"></i>
                            </button>
                            <img src="{{ asset('assets/img/products/urea_sona.png') }}" class="img-fluid" alt="Product Image" style="max-height: 100%; object-fit: contain;">
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <div class="d-flex gap-3 overflow-auto pb-2" style="scrollbar-width: none;">
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center p-2" style="width: 80px; height: 80px; border: 2px solid var(--agri-primary); cursor: pointer;">
                                <img src="{{ asset('assets/img/products/urea_sona.png') }}" class="img-fluid" style="object-fit: contain;">
                            </div>
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center p-2 opacity-75" style="width: 80px; height: 80px; border: 1px solid var(--agri-border); cursor: pointer; transition: 0.2s;">
                                <img src="{{ asset('assets/img/products/urea_sona.png') }}" class="img-fluid" style="object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-success fw-medium px-3 py-2 border" style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Nitrogen Fertilizer</span>
                        </div>
                        <h2 class="fw-bold mb-3 text-dark display-6">FFC Sona Urea (46% N)</h2>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="text-warning fs-5">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <a href="#reviews" class="text-muted text-decoration-none small">(8 Verified Reviews)</a>
                        </div>
                        
                        <div class="d-flex align-items-end gap-3 mb-4">
                            <h3 class="fw-bold text-success mb-0" style="font-size: 32px;">PKR 3,500</h3>
                            <span class="text-muted text-decoration-line-through fs-5 mb-1">PKR 3,800</span>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span class="fw-bold text-dark">In Stock</span>
                            <span class="text-muted ms-2">&mdash; Usually ships within 24 hours</span>
                        </div>

                        <p class="text-muted mb-4" style="line-height: 1.8; font-size: 16px;">
                            FFC Sona Urea is a high-quality nitrogen fertilizer widely used across Pakistan for cereal crops like wheat, maize and rice. Apply as a basal dose or in split applications for better nitrogen-use efficiency.
                        </p>

                        <hr class="my-4">

                        <!-- Action Controls -->
                        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                            <div class="d-flex align-items-center bg-light rounded-3 p-1 border" style="width: 130px;">
                                <button class="btn btn-sm border-0 text-muted fs-5 px-3 py-2 bg-transparent" onclick="document.getElementById('productQty').stepDown()">-</button>
                                <input type="number" id="productQty" class="form-control border-0 text-center fw-bold bg-transparent px-0" value="1" min="1" max="99" style="box-shadow: none;">
                                <button class="btn btn-sm border-0 text-muted fs-5 px-3 py-2 bg-transparent" onclick="document.getElementById('productQty').stepUp()">+</button>
                            </div>
                            <button class="btn-agri btn-agri-primary flex-grow-1" style="padding: 14px 24px; font-size: 16px;" onclick="addToCart(1)">
                                <i class="fas fa-cart-plus me-2 fs-5"></i> Add to Cart
                            </button>
                        </div>

                        <div class="bg-light rounded-3 p-4 border mt-4">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-3 text-muted text-sm">
                                <li><i class="fas fa-shield-alt text-success me-2"></i> <strong>100% Genuine</strong> Product Guarantee</li>
                                <li><i class="fas fa-shipping-fast text-success me-2"></i> <strong>Fast Delivery</strong> via partner couriers</li>
                                <li><i class="fas fa-undo-alt text-success me-2"></i> <strong>Easy Returns</strong> within 7 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="card-agri p-0 border-0 overflow-hidden" id="reviews">
                <div class="border-bottom bg-light px-4 pt-4">
                    <ul class="nav nav-tabs border-0" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active bg-white border-bottom-0 fw-bold px-4 py-3 text-dark" style="border-radius: 8px 8px 0 0;" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link bg-transparent border-0 fw-bold px-4 py-3 text-muted" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Specifications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link bg-transparent border-0 fw-bold px-4 py-3 text-muted" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab">Reviews (8)</button>
                        </li>
                    </ul>
                </div>
                
                <div class="tab-content p-5 bg-white" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="desc" role="tabpanel">
                        <h4 class="fw-bold text-dark mb-4">Product Overview</h4>
                        <p class="text-muted" style="line-height: 1.8;">
                            Recommended for wheat, rice, sugarcane, maize, and fodder crops. For loamy soils: apply 1–2 bags per acre per split, depending on crop stage and soil tests. Incorporate into moist soil to reduce volatilization losses. Not recommended for saline soils without proper leaching.
                        </p>
                        <h5 class="fw-bold text-dark mt-4 mb-3">Key Benefits</h5>
                        <ul class="text-muted ps-3" style="line-height: 1.8;">
                            <li class="mb-2">Guaranteed analysis: 46% Nitrogen (N)</li>
                            <li class="mb-2">Form: Granular prilled urea for easy application</li>
                            <li class="mb-2">Suggested splits: Basal + tillering + booting (cereals)</li>
                            <li class="mb-2">Storage: Keep dry and sealed; avoid caking in high humidity</li>
                        </ul>
                    </div>

                    <!-- Specifications Tab -->
                    <div class="tab-pane fade" id="info" role="tabpanel">
                        <h4 class="fw-bold text-dark mb-4">Technical Specifications</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered border-light align-middle">
                                <tbody>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Bag Size</th>
                                        <td class="px-4 py-3 fw-medium text-dark">50 kg</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Nutrient Composition</th>
                                        <td class="px-4 py-3 fw-medium text-dark">46% Nitrogen (N) Minimum</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Manufacturer</th>
                                        <td class="px-4 py-3 fw-medium text-dark">Fauji Fertilizer Company (FFC)</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-medium text-muted w-25 px-4 py-3">Formulation</th>
                                        <td class="px-4 py-3 fw-medium text-dark">Prilled / Granular</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="review" role="tabpanel">
                        <div class="row g-5">
                            <div class="col-lg-7 border-end">
                                <h4 class="fw-bold text-dark mb-4">Customer Reviews</h4>
                                
                                <!-- Single Review -->
                                <div class="mb-4 pb-4 border-bottom">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="text-warning fs-6">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        </div>
                                        <span class="text-muted small fw-bold">Aleesha Brown</span>
                                        <span class="text-muted small ms-auto">April 8, 2021</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Highly recommended</h6>
                                    <p class="text-muted mb-0">Will purchase more in future. Excellent packing and fast delivery from Plantix-AI.</p>
                                </div>
                                
                                <!-- Single Review -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="text-warning fs-6">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                        </div>
                                        <span class="text-muted small fw-bold">Sarah Albert</span>
                                        <span class="text-muted small ms-auto">April 8, 2021</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Great quality</h6>
                                    <p class="text-muted mb-0">Great product quality! Applied to my wheat crop and saw good results.</p>
                                </div>
                            </div>
                            
                            <div class="col-lg-5">
                                <h4 class="fw-bold text-dark mb-4">Write a Review</h4>
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark text-sm">Your Rating *</label>
                                        <div class="text-muted fs-5" style="cursor: pointer;">
                                            <i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark text-sm">Your Review *</label>
                                        <textarea class="form-agri" rows="4" placeholder="Share your experience..." required></textarea>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6 mb-3">
                                            <input type="text" class="form-agri" placeholder="Name *" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="email" class="form-agri" placeholder="Email *" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-agri btn-agri-primary w-100">Submit Review</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End Product Single -->

    <script>
        // Simple script to handle bootstrap tabs gracefully custom styling
        document.addEventListener('DOMContentLoaded', function() {
            var triggerTabList = [].slice.call(document.querySelectorAll('#productTabs button'))
            triggerTabList.forEach(function (triggerEl) {
                triggerEl.addEventListener('click', function (event) {
                    // Update tab styles
                    document.querySelectorAll('#productTabs button').forEach(b => {
                        b.classList.remove('bg-white', 'text-dark', 'active');
                        b.classList.add('bg-transparent', 'text-muted');
                    });
                    this.classList.remove('bg-transparent', 'text-muted');
                    this.classList.add('bg-white', 'text-dark', 'active');
                })
            })
        });
    </script>
@endsection
