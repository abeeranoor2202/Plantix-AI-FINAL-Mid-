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
    <script src="{{ asset('assets/js/reviews.js') }}"></script>
@endsection

@section('content')
<!-- End Header -->

    <!-- Start Breadcrumb 
    ============================================= -->
    <div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
      style="background-image: url({{ asset('assets/img/banner7.jpg') }})"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Fertilizer Details</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="active">Shop Single</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Shop 
    ============================================= -->
    <div class="validtheme-shop-single-area default-padding">
      <div class="container">
        <div class="product-details">
          <div class="row">
            <div class="col-lg-6">
              <div class="product-thumb">
                <div
                  id="timeline-carousel"
                  class="carousel slide"
                  data-bs-ride="carousel"
                >
                  <div class="carousel-inner item-box">
                    <div class="carousel-item active product-item">
                      <a
                        href="{{ asset('assets/img/products/urea_sona.png') }}"
                        class="item popup-gallery"
                      >
                        <img
                          src="{{ asset('assets/img/products/urea_sona.png') }}"
                          alt="Thumb"
                        />
                      </a>
                      <span class="onsale theme">-16%</span>
                    </div>
                    <div class="carousel-item product-item">
                      <a
                        href="{{ asset('assets/img/products/urea_sona.png') }}"
                        class="item popup-gallery"
                      >
                        <img
                          src="{{ asset('assets/img/products/urea_sona.png') }}"
                          alt="Thumb"
                        />
                      </a>
                      <span class="onsale theme">-25%</span>
                    </div>
                    <div class="carousel-item product-item">
                      <a
                        href="{{ asset('assets/img/products/urea_sona.png') }}"
                        class="item popup-gallery"
                      >
                        <img
                          src="{{ asset('assets/img/products/urea_sona.png') }}"
                          alt="Thumb"
                        />
                      </a>
                      <span class="onsale theme">-33%</span>
                    </div>
                    <div class="carousel-item product-item">
                      <a
                        href="{{ asset('assets/img/products/urea_sona.png') }}"
                        class="item popup-gallery"
                      >
                        <img
                          src="{{ asset('assets/img/products/urea_sona.png') }}"
                          alt="Thumb"
                        />
                      </a>
                      <span class="onsale theme">-50%</span>
                    </div>
                  </div>

                  <!-- Carousel Indicators -->
                  <div class="carousel-indicators">
                    <div class="product-gallery-carousel swiper">
                      <!-- Additional required wrapper -->
                      <div class="swiper-wrapper">
                        <div class="swiper-slide">
                          <div
                            class="item active"
                            data-bs-target="#timeline-carousel"
                            data-bs-slide-to="0"
                            aria-current="true"
                          >
                            <img
                              src="{{ asset('assets/img/products/urea_sona.png') }}"
                              alt=""
                            />
                          </div>
                        </div>
                        <div class="swiper-slide">
                          <div
                            class="item"
                            data-bs-target="#timeline-carousel"
                            data-bs-slide-to="1"
                          >
                            <img
                              src="{{ asset('assets/img/products/urea_sona.png') }}"
                              alt=""
                            />
                          </div>
                        </div>
                        <div class="swiper-slide">
                          <div
                            class="item"
                            data-bs-target="#timeline-carousel"
                            data-bs-slide-to="2"
                          >
                            <img
                              src="{{ asset('assets/img/products/urea_sona.png') }}"
                              alt=""
                            />
                          </div>
                        </div>
                        <div class="swiper-slide">
                          <div
                            class="item"
                            data-bs-target="#timeline-carousel"
                            data-bs-slide-to="3"
                          >
                            <img
                              src="{{ asset('assets/img/products/urea_sona.png') }}"
                              alt=""
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- End Carousel Content -->
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div
                class="single-product-contents"
                data-product-id="ffc-sona-urea"
              >
                <div class="summary-top-box">
                  <div class="product-tags">
                    <a href="#">Nitrogen</a>
                    <a href="#">Urea</a>
                  </div>
                  <div class="review-count">
                    <div class="rating">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span>(8 Review)</span>
                  </div>
                </div>
                <h2 class="product-title">FFC Sona Urea (46% N)</h2>
                <div class="price">
                  <span><del>PKR 3,800</del></span>
                  <span>PKR 3,500</span>
                </div>
                <div class="product-stock validthemes-in-stock">
                  <span>In Stock</span>
                </div>
                <p>
                  FFC Sona Urea is a high-quality nitrogen fertilizer widely
                  used across Pakistan for cereal crops like wheat, maize and
                  rice. Apply as a basal dose or in split applications for
                  better nitrogen-use efficiency. Avoid application on standing
                  water and prefer evening application followed by irrigation.
                </p>
                <div class="product-purchase-list">
                  <input
                    type="number"
                    id="quantity"
                    step="1"
                    name="quantity"
                    min="0"
                    placeholder="0"
                  />
                  <a
                    href="#"
                    class="btn secondary btn-theme btn-sm animation btn-cart-add"
                  >
                    <i class="fas fa-shopping-cart"></i>
                    Add to cart
                  </a>
                  <div class="shop-action">
                    <ul>
                      <li class="wishlist">
                        <a href="#"><span>Add to wishlist</span></a>
                      </li>
                      <li class="compare">
                        <a href="#"><span>Compare</span></a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="product-estimate-delivary">
                  <i class="fas fa-box-open"></i>
                  <strong> 2-day Delivery</strong>
                  <span>Speedy and reliable parcel delivery!</span>
                </div>
                <div class="product-meta">
                  <span class="sku"> <strong>SKU:</strong> BE45VGRT </span>
                  <span class="posted-in">
                    <strong>Category:</strong>
                    <a href="#">Nitrogen</a> ,
                    <a href="#">Urea</a>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Bottom Info  -->
        <div class="single-product-bottom-info">
          <div class="row">
            <div class="col-lg-12">
              <!-- Tab Nav -->
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button
                  class="nav-link active"
                  id="description-tab-control"
                  data-bs-toggle="tab"
                  data-bs-target="#description-tab"
                  type="button"
                  role="tab"
                  aria-controls="description-tab"
                  aria-selected="true"
                >
                  Description
                </button>

                <button
                  class="nav-link"
                  id="information-tab-control"
                  data-bs-toggle="tab"
                  data-bs-target="#information-tab"
                  type="button"
                  role="tab"
                  aria-controls="information-tab"
                  aria-selected="false"
                >
                  Additional Information
                </button>

                <button
                  class="nav-link"
                  id="review-tab-control"
                  data-bs-toggle="tab"
                  data-bs-target="#review-tab"
                  type="button"
                  role="tab"
                  aria-controls="review-tab"
                  aria-selected="false"
                >
                  Review
                </button>
              </div>
              <!-- End Tab Nav -->
              <!-- Start Tab Content -->
              <div class="tab-content tab-content-info" id="myTabContent">
                <!-- Tab Single -->
                <div
                  class="tab-pane fade show active"
                  id="description-tab"
                  role="tabpanel"
                  aria-labelledby="description-tab-control"
                >
                  <p>
                    Recommended for wheat, rice, sugarcane, maize, and fodder
                    crops. For loamy soils: apply 1–2 bags per acre per split,
                    depending on crop stage and soil tests. Incorporate into
                    moist soil to reduce volatilization losses. Not recommended
                    for saline soils without proper leaching.
                  </p>
                  <ul>
                    <li>Guaranteed analysis: 46% Nitrogen (N)</li>
                    <li>Form: Granular prilled urea</li>
                    <li>
                      Suggested splits: Basal + tillering + booting (cereals)
                    </li>
                    <li>Storage: Keep dry and sealed; avoid caking</li>
                    <li>
                      Compatibility: Do not mix with calcium ammonium nitrate
                      (CAN) before application
                    </li>
                  </ul>
                </div>
                <!-- End Single -->

                <!-- Tab Single -->
                <div
                  class="tab-pane fade"
                  id="information-tab"
                  role="tabpanel"
                  aria-labelledby="information-tab-control"
                >
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <td>Bag Size</td>
                          <td>50 kg</td>
                        </tr>
                        <tr>
                          <td>Nutrient</td>
                          <td>46% Nitrogen (N)</td>
                        </tr>
                        <tr>
                          <td>Manufacturer</td>
                          <td>Fauji Fertilizer Company (FFC)</td>
                        </tr>
                        <tr>
                          <td>Country</td>
                          <td>Pakistan</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- End Tab Single -->

                <!-- Tab Single -->
                <div
                  class="tab-pane fade"
                  id="review-tab"
                  role="tabpanel"
                  aria-labelledby="review-tab-control"
                >
                  <h4>1 review for “FFC Sona Urea (46% N)”</h4>
                  <div class="review-items">
                    <div class="item">
                      <div class="thumb">
                        <img
                          src="{{ asset('assets/img/blog/cotton_leaf_curl.png') }}"
                          alt="Thumb"
                        />
                      </div>
                      <div class="info">
                        <div class="rating">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                        </div>
                        <div class="review-date">April 8, 2021</div>
                        <div class="review-authro">
                          <h5>Aleesha Brown</h5>
                        </div>
                        <p>Highly recommended. Will purchase more in future.</p>
                      </div>
                    </div>
                    <div class="item">
                      <div class="thumb">
                        <img src="{{ asset('assets/img/blog/maize_urea.png') }}" alt="Thumb" />
                      </div>
                      <div class="info">
                        <div class="rating">
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                          <i class="fas fa-star-half-alt"></i>
                        </div>
                        <div class="review-date">April 8, 2021</div>
                        <div class="review-authro">
                          <h5>Sarah Albert</h5>
                        </div>
                        <p>Great product quality!</p>
                      </div>
                    </div>
                  </div>
                  <div class="review-form">
                    <h4>Add a review</h4>
                    <div class="rating-select">
                      <div class="stars">
                        <span>
                          <a class="star-1" href="#" aria-label="Rate 1 star"
                            ><i class="fas fa-star"></i
                          ></a>
                          <a class="star-2" href="#" aria-label="Rate 2 stars"
                            ><i class="fas fa-star"></i
                          ></a>
                          <a class="star-3" href="#" aria-label="Rate 3 stars"
                            ><i class="fas fa-star"></i
                          ></a>
                          <a class="star-4" href="#" aria-label="Rate 4 stars"
                            ><i class="fas fa-star"></i
                          ></a>
                          <a class="star-5" href="#" aria-label="Rate 5 stars"
                            ><i class="fas fa-star"></i
                          ></a>
                        </span>
                      </div>
                    </div>
                    <form action="#" class="contact-form">
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="form-group comments">
                            <textarea
                              class="form-control"
                              id="comments"
                              name="comments"
                              required
                              placeholder="Tell Us About Project *"
                            ></textarea>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group">
                            <input
                              class="form-control"
                              id="name"
                              name="name"
                              placeholder="Name"
                              type="text"
                              required
                            />
                            <span class="alert-error"></span>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <input
                              class="form-control"
                              id="email"
                              name="email"
                              placeholder="Email*"
                              type="email"
                              required
                            />
                            <span class="alert-error"></span>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-12">
                          <button type="submit" name="submit" id="submit">
                            Post Review
                          </button>
                        </div>
                      </div>
                      <!-- Alert Message -->
                      <div class="col-md-12 alert-notification">
                        <div id="message" class="alert-msg"></div>
                      </div>
                    </form>
                  </div>
                </div>
                <!-- End Tab Single -->
              </div>
              <!-- End Tab Content -->
            </div>
          </div>
        </div>
        <!-- End Product Bottom Info  -->

        <!-- Related Product  -->
        <div class="related-products carousel-shadow">
          <div class="row">
            <div class="col-md-12">
              <h3>Related Products</h3>
              <div
                class="vt-products text-center related-product-carousel swiper"
              >
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                  <!-- Single product -->
                  <div class="swiper-slide">
                    <div class="product">
                      <div class="product-contents">
                        <div class="product-image">
                          <a href="{{ route('shop.single') }}">
                            <img
                              src="{{ asset('assets/img/products/urea_sona.png') }}"
                              alt="Product"
                            />
                          </a>
                          <div class="shop-action">
                            <ul>
                              <li class="cart">
                                <a href="#"><span>Add to cart</span></a>
                              </li>
                              <li class="wishlist">
                                <a href="#"><span>Add to wishlist</span></a>
                              </li>
                              <li class="quick-view">
                                <a href="#"><span>Quick view</span></a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="product-caption">
                          <div class="product-tags">
                            <a href="#">Nitrogen</a>
                            <a href="#">Urea</a>
                          </div>
                          <h4 class="product-title">
                            <a href="{{ route('shop.single') }}">FFC Sona Urea (46% N)</a>
                          </h4>
                          <div class="price">
                            <span>PKR 3,500</span>
                          </div>
                          <a href="#" class="cart-btn"
                            ><i class="fas fa-shopping-bag"></i> Add to cart</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Single product -->
                  <!-- Single product -->
                  <div class="swiper-slide">
                    <div class="product">
                      <div class="product-contents">
                        <div class="product-image">
                          <span class="onsale">Sale!</span>
                          <a href="{{ route('shop.single') }}">
                            <img
                              src="{{ asset('assets/img/products/dap_engro.png') }}"
                              alt="Product"
                            />
                          </a>
                          <div class="shop-action">
                            <ul>
                              <li class="cart">
                                <a href="#"><span>Add to cart</span></a>
                              </li>
                              <li class="wishlist">
                                <a href="#"><span>Add to wishlist</span></a>
                              </li>
                              <li class="quick-view">
                                <a href="#"><span>Quick view</span></a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="product-caption">
                          <div class="product-tags">
                            <a href="#">Phosphorus</a>
                            <a href="#">DAP</a>
                          </div>
                          <h4 class="product-title">
                            <a href="{{ route('shop.single') }}">Engro DAP (18-46-0)</a>
                          </h4>
                          <div class="price">
                            <span><del>PKR 15,500</del></span>
                            <span>PKR 14,500</span>
                          </div>
                          <a href="#" class="cart-btn"
                            ><i class="fas fa-shopping-bag"></i> Add to cart</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Single product -->
                  <!-- Single product -->
                  <div class="swiper-slide">
                    <div class="product">
                      <div class="product-contents">
                        <div class="product-image">
                          <a href="{{ route('shop.single') }}">
                            <img
                              src="{{ asset('assets/img/products/can_sarsabz.png') }}"
                              alt="Product"
                            />
                          </a>
                          <div class="shop-action">
                            <ul>
                              <li class="cart">
                                <a href="#"><span>Add to cart</span></a>
                              </li>
                              <li class="wishlist">
                                <a href="#"><span>Add to wishlist</span></a>
                              </li>
                              <li class="quick-view">
                                <a href="#"><span>Quick view</span></a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="product-caption">
                          <div class="product-tags">
                            <a href="#">Nitrogen</a>
                            <a href="#">CAN</a>
                          </div>
                          <h4 class="product-title">
                            <a href="{{ route('shop.single') }}">Sarsabz CAN</a>
                          </h4>
                          <div class="price">
                            <span>PKR 3,800</span>
                          </div>
                          <a href="#" class="cart-btn"
                            ><i class="fas fa-shopping-bag"></i> Add to cart</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Single product -->
                  <!-- Single product -->
                  <div class="swiper-slide">
                    <div class="product">
                      <div class="product-contents">
                        <div class="product-image">
                          <a href="{{ route('shop.single') }}">
                            <img
                              src="{{ asset('assets/img/products/agricultural_gypsum.jpg') }}"
                              alt="Product"
                            />
                          </a>
                          <div class="shop-action">
                            <ul>
                              <li class="cart">
                                <a href="#"><span>Add to cart</span></a>
                              </li>
                              <li class="wishlist">
                                <a href="#"><span>Add to wishlist</span></a>
                              </li>
                              <li class="quick-view">
                                <a href="#"><span>Quick view</span></a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="product-caption">
                          <div class="product-tags">
                            <a href="#">Soil Conditioner</a>
                            <a href="#">Calcium</a>
                          </div>
                          <h4 class="product-title">
                            <a href="{{ route('shop.single') }}">Agricultural Gypsum</a>
                          </h4>
                          <div class="price">
                            <span>PKR 1,200</span>
                          </div>
                          <a href="#" class="cart-btn"
                            ><i class="fas fa-shopping-bag"></i> Add to cart</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Single product -->
                  <!-- Single product -->
                  <div class="swiper-slide">
                    <div class="product">
                      <div class="product-contents">
                        <div class="product-image">
                          <a href="{{ route('shop.single') }}">
                            <img
                              src="{{ asset('assets/img/products/mop_potash.jpg') }}"
                              alt="Product"
                            />
                          </a>
                          <div class="shop-action">
                            <ul>
                              <li class="cart">
                                <a href="#"><span>Add to cart</span></a>
                              </li>
                              <li class="wishlist">
                                <a href="#"><span>Add to wishlist</span></a>
                              </li>
                              <li class="quick-view">
                                <a href="#"><span>Quick view</span></a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="product-caption">
                          <div class="product-tags">
                            <a href="#">Potash</a>
                            <a href="#">MOP</a>
                          </div>
                          <h4 class="product-title">
                            <a href="{{ route('shop.single') }}">MOP (60% K2O)</a>
                          </h4>
                          <div class="price">
                            <span>PKR 12,000</span>
                          </div>
                          <a href="#" class="cart-btn"
                            ><i class="fas fa-shopping-bag"></i> Add to cart</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Single product -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Related Product  -->
      </div>
    </div>
    <!-- End Shop -->
@endsection

