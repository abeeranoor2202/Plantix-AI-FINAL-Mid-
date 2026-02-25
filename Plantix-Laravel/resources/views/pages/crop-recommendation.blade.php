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
    <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Crop Recommendation</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Crop Recommendation</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Star Services Details Area
    ============================================= -->
    <div class="services-details-area default-padding">
        <div class="container">
            <div class="services-details-items">
                <div class="row">

                    <div class="col-xl-8 col-lg-7 pl-45 pl-md-15 pl-xs-15 services-single-content order-lg-last">
                        <div class="thumb">
                            <img src="{{ asset('assets/img/1500x800.png') }}" alt="Thumb">
                        </div>
                        <h2>AI-Powered Crop Recommendation</h2>
                        <p>
                            Get smart, data-driven suggestions on which crop to sow based on your field’s Nitrogen (N),
                            Phosphorus (P), Potassium (K), soil humidity, soil pH, and expected rainfall. Our engine
                            compares your inputs to crop profiles and returns the best-fit crop plus practical
                            alternatives.
                        </p>
                        <div class="features mt-40 mt-xs-30 mb-30 mb-xs-20">
                            <div class="row">
                                <div class="col-xl-5 col-lg-12 col-md-6">
                                    <div class="content">
                                        <h3>What you’ll get</h3>
                                        <ul class="feature-list-item">
                                            <li>Best-fit crop for your field</li>
                                            <li>2–3 alternative crop options</li>
                                            <li>Reasoning from NPK, pH, moisture, rainfall</li>
                                            <li>Simple, farmer‑friendly guidance</li>
                                            <li>Aligned with your local conditions</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xl-7 col-lg-12 col-md-6 mt-xs-30">
                                    <div class="content">
                                        <h3>Why it matters</h3>
                                        <p>
                                            Choosing a crop that matches your soil chemistry and climate can boost
                                            yields and reduce wasted inputs. By aligning crop requirements with your NPK
                                            balance, soil pH, moisture and rainfall, you de‑risk the season and improve
                                            profitability.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <blockquote>Right crop + right nutrients + right conditions = higher yield and lower risk.
                        </blockquote>
                        <h2>How our recommendation works</h2>
                        <p>
                            Enter your N, P, K, soil pH, humidity and rainfall. We compare these with known crop ranges
                            and produce a primary recommendation and alternatives. This demo uses simple heuristic
                            rules—pair it with local expertise and soil tests for the best outcomes.
                        </p>

                        <div class="crop-recommendation-form mt-40">
                            <h2 class="mb-25">NPK & Soil Condition Based Crop Recommendation</h2>
                            <p>Enter your field’s nutrient levels and conditions, and we’ll suggest a suitable crop to
                                sow. This is a simple demo using heuristic rules.</p>

                            <form id="npkForm" class="contact-form">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nitrogen" class="form-label">Nitrogen (N) kg/ha</label>
                                        <input type="number" class="form-control" id="nitrogen" name="nitrogen"
                                            placeholder="e.g., 120" min="0" max="300" step="1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="phosphorus" class="form-label">Phosphorus (P) kg/ha</label>
                                        <input type="number" class="form-control" id="phosphorus" name="phosphorus"
                                            placeholder="e.g., 60" min="0" max="200" step="1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="potassium" class="form-label">Potassium (K) kg/ha</label>
                                        <input type="number" class="form-control" id="potassium" name="potassium"
                                            placeholder="e.g., 100" min="0" max="300" step="1" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="humidity" class="form-label">Soil Humidity (%)</label>
                                        <input type="number" class="form-control" id="humidity" name="humidity"
                                            placeholder="e.g., 65" min="0" max="100" step="1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="ph" class="form-label">Soil pH</label>
                                        <input type="number" class="form-control" id="ph" name="ph"
                                            placeholder="e.g., 6.5" min="0" max="14" step="0.1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="rainfall" class="form-label">Rainfall (mm)</label>
                                        <input type="number" class="form-control" id="rainfall" name="rainfall"
                                            placeholder="e.g., 700" min="0" max="3000" step="1" required>
                                    </div>
                                </div>

                                <div class="text-center mt-2">
                                    <button type="submit" class="btn btn-theme btn-md"><i class="fas fa-seedling"></i>
                                        Get Recommendation</button>
                                </div>
                            </form>

                            <!-- Recommendation Result -->
                            <div id="npkResult" class="alert alert-success mt-40 hidden">
                                <h4 class="mb-3"><i class="fas fa-check-circle"></i> Crop Recommendation</h4>
                                <div id="npkResultContent"></div>
                            </div>
                        </div>

                        <script>
                            (function () {
                                var form = document.getElementById('npkForm');
                                if (!form) return;
                                form.addEventListener('submit', function (e) {
                                    e.preventDefault();
                                    var n = parseFloat(document.getElementById('nitrogen').value);
                                    var p = parseFloat(document.getElementById('phosphorus').value);
                                    var k = parseFloat(document.getElementById('potassium').value);
                                    var humidity = parseFloat(document.getElementById('humidity').value);
                                    var ph = parseFloat(document.getElementById('ph').value);
                                    var rainfall = parseFloat(document.getElementById('rainfall').value);

                                    if ([n, p, k, humidity, ph, rainfall].some(function (v) { return isNaN(v); })) {
                                        alert('Please provide all values.');
                                        return;
                                    }

                                    var recommendation = generateCropRecommendation(n, p, k, humidity, ph, rainfall);
                                    document.getElementById('npkResultContent').innerHTML = recommendation;
                                    var box = document.getElementById('npkResult');
                                    box.classList.remove('hidden');
                                    box.style.display = 'block';
                                    document.getElementById('npkResult').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                });

                                function scoreRange(val, min, max, tolerance) {
                                    if (val >= min && val <= max) return 2; // ideal
                                    if (val >= (min - tolerance) && val <= (max + tolerance)) return 1; // near
                                    return 0; // out
                                }

                                function generateCropRecommendation(n, p, k, humidity, ph, rainfall) {
                                    // Dummy heuristic profiles (illustrative only)
                                    var profiles = {
                                        'Rice': { N: [80, 180], P: [40, 80], K: [40, 100], H: [60, 90], pH: [5.0, 6.5], R: [800, 2000] },
                                        'Wheat': { N: [100, 160], P: [50, 90], K: [40, 80], H: [40, 60], pH: [6.0, 7.5], R: [300, 700] },
                                        'Maize': { N: [120, 180], P: [60, 100], K: [60, 120], H: [40, 70], pH: [5.5, 7.0], R: [300, 700] },
                                        'Soybean': { N: [0, 60], P: [60, 90], K: [60, 120], H: [50, 70], pH: [6.0, 7.0], R: [400, 800] },
                                        'Cotton': { N: [80, 150], P: [40, 80], K: [80, 120], H: [50, 70], pH: [5.5, 7.5], R: [600, 1200] },
                                        'Potato': { N: [120, 160], P: [50, 100], K: [100, 150], H: [50, 80], pH: [5.0, 6.5], R: [500, 750] },
                                        'Sugarcane': { N: [150, 250], P: [60, 100], K: [120, 200], H: [60, 80], pH: [6.0, 7.5], R: [1000, 2000] }
                                    };

                                    var tolerance = { N: 30, P: 20, K: 30, H: 10, pH: 0.5, R: 150 };
                                    var params = { N: n, P: p, K: k, H: humidity, pH: ph, R: rainfall };

                                    var scores = Object.keys(profiles).map(function (crop) {
                                        var prof = profiles[crop];
                                        var score = 0;
                                        score += scoreRange(params.N, prof.N[0], prof.N[1], tolerance.N);
                                        score += scoreRange(params.P, prof.P[0], prof.P[1], tolerance.P);
                                        score += scoreRange(params.K, prof.K[0], prof.K[1], tolerance.K);
                                        score += scoreRange(params.H, prof.H[0], prof.H[1], tolerance.H);
                                        score += scoreRange(params.pH, prof.pH[0], prof.pH[1], tolerance.pH);
                                        score += scoreRange(params.R, prof.R[0], prof.R[1], tolerance.R);
                                        return { crop: crop, score: score };
                                    });

                                    scores.sort(function (a, b) { return b.score - a.score; });
                                    var primary = scores[0];
                                    var alternatives = scores.slice(1, 4);

                                    // Build details
                                    var html = '';
                                    html += '<p><strong>Your inputs:</strong></p>';
                                    html += '<ul class="feature-list-item mb-3">';
                                    html += '<li><strong>N:</strong> ' + n + ' kg/ha</li>';
                                    html += '<li><strong>P:</strong> ' + p + ' kg/ha</li>';
                                    html += '<li><strong>K:</strong> ' + k + ' kg/ha</li>';
                                    html += '<li><strong>Humidity:</strong> ' + humidity + ' %</li>';
                                    html += '<li><strong>Soil pH:</strong> ' + ph + '</li>';
                                    html += '<li><strong>Rainfall:</strong> ' + rainfall + ' mm</li>';
                                    html += '</ul>';

                                    html += '<div class="alert alert-warning mb-3">';
                                    html += '<h5 class="mb-2"><i class="fas fa-star"></i> Recommended Crop</h5>';
                                    html += '<p class="mb-0"><strong>' + primary.crop + '</strong> appears most suitable for your field conditions based on NPK balance, soil pH, humidity, and rainfall.</p>';
                                    html += '</div>';

                                    html += '<p><strong>Other suitable options:</strong></p>';
                                    html += '<ul class="feature-list-item">';
                                    alternatives.forEach(function (s) { html += '<li>' + s.crop + '</li>'; });
                                    html += '</ul>';

                                    html += '<p class="mt-3"><small><em>Note: This is a demo recommendation using simple rules. Consult local agronomists and soil tests for precise guidance.</em></small></p>';
                                    return html;
                                }
                            })();
                        </script>
                    </div>

                    <div class="col-xl-4 col-lg-5 mt-md-100 mt-xs-50 services-sidebar">
                        <!-- Single Widget -->
                        <div class="single-widget services-list-widget">
                            <div class="content">
                                <ul>
                                    <li class="current-item"><a href="{{ route('crop-recommendation') }}">Crop Recommendation</a>
                                    </li>
                                    <li><a href="{{ route('crop-planning') }}">Crop Planning</a></li>
                                    <li><a href="{{ route('disease-identification') }}">Disease Identification</a></li>
                                    <li><a href="{{ route('fertilizer-recommendation') }}">Fertilizer Recommendation</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- End Single Widget -->
                        <div class="single-widget quick-contact-widget text-light quick-contact-bg-800">
                            <div class="content">
                                <h3>Need Help?</h3>
                                <p>
                                    Need help using this tool or understanding the results? Talk to a Plantix‑AI
                                    agronomy specialist — call our office and we will connect you with an expert.
                                </p>
                                <h2>+92 330 088123</h2>
                                <h4><a href="mailto:info@plantixai.com">info@plantixai.com</a></h4>
                                <a href="{{ route('contact') }}" class="btn btn-light mt-3">Contact Us</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End Services Details Area -->
@endsection

