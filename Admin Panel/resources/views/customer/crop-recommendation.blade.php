@extends('layouts.frontend')

@section('title', 'Crop Recommendation | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.01));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Crop Recommendation AI</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-success text-decoration-none">AI Tools</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Crop Recommendation</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Content -->
    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">
            <div class="row g-5">
                
                <!-- Main Content -->
                <div class="col-lg-8 order-lg-last">
                    <div class="card-agri p-lg-5 p-4 border-0 mb-4">
                        <div class="mb-4">
                            <span class="badge bg-success bg-opacity-10 text-success mb-2 px-3 py-2 fs-6 border border-success border-opacity-25 rounded-pill"><i class="fas fa-brain me-2"></i> Machine Learning</span>
                            <h2 class="fw-bold text-dark mb-3">AI-Powered Crop Recommendation</h2>
                            <p class="text-muted" style="line-height: 1.8; font-size: 16px;">
                                Get smart, data-driven suggestions on which crop to sow based on your field’s Nitrogen (N), Phosphorus (P), Potassium (K), soil humidity, soil pH, and expected rainfall. Our engine compares your inputs to optimized crop profiles and returns the best-fit crop plus practical alternatives.
                            </p>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-seedling text-primary me-2"></i> What you’ll get</h4>
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted small" style="line-height: 1.6;">
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Best-fit crop for your field</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> 2–3 suitable alternative crops</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Reasoning based on soil nutrients</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Simple, farmer-friendly guidance</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-chart-line text-warning me-2"></i> Why it matters</h4>
                                    <p class="text-muted mb-0 small" style="line-height: 1.6;">
                                        Choosing a crop that matches your soil chemistry and climate boosts yields and reduces wasted inputs. By aligning crop requirements with your NPK ratio, pH, and climate, you de-risk the season and maximize profitability.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-25">

                        <!-- Tool Form -->
                        <div class="crop-recommendation-form p-4 rounded-4" style="background: rgba(16, 185, 129, 0.03); border: 2px dashed var(--agri-primary-light);">
                            <div class="text-center mb-4">
                                <div class="d-inline-flex bg-white text-primary p-3 rounded-circle shadow-sm mb-3">
                                    <i class="fas fa-flask fs-3"></i>
                                </div>
                                <h3 class="fw-bold text-dark fs-4">Analyze Soil Conditions</h3>
                                <p class="text-muted mb-0 mx-auto" style="max-width: 600px;">
                                    Enter your field’s nutrient levels and physical conditions below to instantly generate a tailored crop recommendation.
                                </p>
                            </div>

                            <form id="npkForm" class="contact-form">
                                <h5 class="fw-bold text-dark mb-3 mt-4 fs-6 border-bottom pb-2">Soil Nutrients (NPK)</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="nitrogen" class="form-label fw-bold small text-muted">Nitrogen (N) kg/ha</label>
                                        <input type="number" class="form-agri" id="nitrogen" name="nitrogen" placeholder="120" min="0" max="300" step="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phosphorus" class="form-label fw-bold small text-muted">Phosphorus (P) kg/ha</label>
                                        <input type="number" class="form-agri" id="phosphorus" name="phosphorus" placeholder="60" min="0" max="200" step="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="potassium" class="form-label fw-bold small text-muted">Potassium (K) kg/ha</label>
                                        <input type="number" class="form-agri" id="potassium" name="potassium" placeholder="100" min="0" max="300" step="1" required>
                                    </div>
                                </div>

                                <h5 class="fw-bold text-dark mb-3 mt-4 fs-6 border-bottom pb-2">Field Environment</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="humidity" class="form-label fw-bold small text-muted">Soil Humidity (%)</label>
                                        <input type="number" class="form-agri" id="humidity" name="humidity" placeholder="65" min="0" max="100" step="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ph" class="form-label fw-bold small text-muted">Soil pH</label>
                                        <input type="number" class="form-agri" id="ph" name="ph" placeholder="6.5" min="0" max="14" step="0.1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="rainfall" class="form-label fw-bold small text-muted">Rainfall (mm)</label>
                                        <input type="number" class="form-agri" id="rainfall" name="rainfall" placeholder="700" min="0" max="3000" step="1" required>
                                    </div>
                                </div>

                                <div class="text-center mt-5">
                                    <button type="submit" class="btn-agri btn-agri-primary px-5 py-3 fs-5 shadow-sm">
                                        <i class="fas fa-magic me-2"></i> Generate Recommendation
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Recommendation Result -->
                        <div id="npkResult" class="mt-4" style="display: none;">
                            <div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">
                                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0 m-0">Recommendation Ready</h4>
                                </div>
                                <div id="npkResultContent"></div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- End Main Content -->

                <!-- Sidebar Settings -->
                <div class="col-lg-4">
                    <div class="card-agri p-4 border-0 mb-4 sticky-top" style="top: 20px;">
                        <h4 class="fw-bold text-dark fs-5 mb-4 border-bottom pb-3">AI Tools</h4>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <li>
                                <a href="{{ route('crop.recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none" style="background: var(--agri-primary-light); color: var(--agri-primary);">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-primary" style="width: 36px; height: 36px;"><i class="fas fa-seedling text-primary"></i></div>
                                    <span class="fw-bold">Crop Recommendation</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('crop.planning') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-calendar-alt text-secondary"></i></div>
                                    <span class="fw-medium">Crop Planning</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('disease.identification') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-microscope text-secondary"></i></div>
                                    <span class="fw-medium">Disease Identification</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('fertilizer.recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-flask text-secondary"></i></div>
                                    <span class="fw-medium">Fertilizer Recommendation</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-agri p-4 border-0 bg-success text-white position-relative overflow-hidden text-center sticky-top" style="top: 380px;">
                        <div class="position-absolute" style="top: -20px; right: -20px; font-size: 150px; opacity: 0.1; transform: rotate(-15deg);">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="position-relative z-index-1">
                            <div class="bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 64px; height: 64px; font-size: 28px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-white">Need Soil Testing?</h4>
                            <p class="mb-4 text-white text-opacity-75 small">
                                Don't know your NPK values? Order a professional soil testing kit or schedule a collection via our partners.
                            </p>
                            <h3 class="fw-bold mb-4 text-white">+92 330 088123</h3>
                            <a href="{{ route('contact') }}" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-success shadow-sm">Contact Us</a>
                        </div>
                    </div>
                </div>
                <!-- End Sidebar Settings -->

            </div>
        </div>
    </div>
    <!-- End Content -->

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

                // Show loading state
                var btn = form.querySelector('button[type="submit"]');
                var originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analyzing...';
                btn.disabled = true;

                setTimeout(function() {
                    var recommendation = generateCropRecommendation(n, p, k, humidity, ph, rainfall);
                    document.getElementById('npkResultContent').innerHTML = recommendation;
                    var box = document.getElementById('npkResult');
                    box.style.display = 'block';
                    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                }, 1000);
            });

            function scoreRange(val, min, max, tolerance) {
                if (val >= min && val <= max) return 2; // ideal
                if (val >= (min - tolerance) && val <= (max + tolerance)) return 1; // near
                return 0; // out
            }

            function generateCropRecommendation(n, p, k, humidity, ph, rainfall) {
                // Dummy heuristic profiles
                var profiles = {
                    'Rice': { N: [80, 180], P: [40, 80], K: [40, 100], H: [60, 90], pH: [5.0, 6.5], R: [800, 2000], icon: 'fas fa-seedling' },
                    'Wheat': { N: [100, 160], P: [50, 90], K: [40, 80], H: [40, 60], pH: [6.0, 7.5], R: [300, 700], icon: 'fas fa-leaf' },
                    'Maize': { N: [120, 180], P: [60, 100], K: [60, 120], H: [40, 70], pH: [5.5, 7.0], R: [300, 700], icon: 'fab fa-pagelines' },
                    'Soybean': { N: [0, 60], P: [60, 90], K: [60, 120], H: [50, 70], pH: [6.0, 7.0], R: [400, 800], icon: 'fas fa-seedling' },
                    'Cotton': { N: [80, 150], P: [40, 80], K: [80, 120], H: [50, 70], pH: [5.5, 7.5], R: [600, 1200], icon: 'fas fa-tree' },
                    'Potato': { N: [120, 160], P: [50, 100], K: [100, 150], H: [50, 80], pH: [5.0, 6.5], R: [500, 750], icon: 'fas fa-leaf' },
                    'Sugarcane': { N: [150, 250], P: [60, 100], K: [120, 200], H: [60, 80], pH: [6.0, 7.5], R: [1000, 2000], icon: 'fas fa-seedling' }
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
                    return { crop: crop, score: score, icon: prof.icon };
                });

                scores.sort(function (a, b) { return b.score - a.score; });
                var primary = scores[0];
                var alternatives = scores.slice(1, 4);

                var html = '';
                
                // Primary Recommendation
                html += '<div class="alert alert-success mb-4 bg-success bg-opacity-10 border-success border-opacity-25">';
                html += '<div class="d-flex align-items-center gap-3">';
                html += '<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;"><i class="' + primary.icon + ' fs-3"></i></div>';
                html += '<div>';
                html += '<h6 class="text-success text-uppercase fw-bold mb-1" style="font-size: 13px; letter-spacing: 1px;">Top Recommended Crop</h6>';
                html += '<h3 class="fw-bold text-dark mb-0">' + primary.crop + '</h3>';
                html += '</div></div>';
                html += '<p class="mt-3 mb-0 text-dark opacity-75">Based on your NPK balance, pH, humidity, and rainfall context, ' + primary.crop + ' appears to be the most optimal choice for maximum yield.</p>';
                html += '</div>';

                // Alternatives
                html += '<h6 class="fw-bold text-dark mb-3">Strong Alternative Crops:</h6>';
                html += '<div class="row g-3 mb-4">';
                alternatives.forEach(function (s) { 
                    html += '<div class="col-md-4">';
                    html += '<div class="bg-light border rounded-3 p-3 text-center h-100 d-flex flex-column justify-content-center">';
                    html += '<i class="' + s.icon + ' text-primary fs-4 mb-2"></i>';
                    html += '<h6 class="fw-bold text-dark m-0">' + s.crop + '</h6>';
                    html += '</div></div>';
                });
                html += '</div>';

                // Inputs summary
                html += '<div class="accordion" id="inputsSummaryAccordion">';
                html += '<div class="accordion-item border-0 bg-transparent">';
                html += '<h2 class="accordion-header" id="headingOne">';
                html += '<button class="accordion-button collapsed bg-light rounded-3 fw-bold text-dark shadow-none border" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">';
                html += 'View Your Submitted Parameters';
                html += '</button></h2>';
                html += '<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#inputsSummaryAccordion">';
                html += '<div class="accordion-body px-0 py-3">';
                html += '<div class="row g-3">';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Nitrogen (N)</small><strong>' + n + ' kg/ha</strong></div></div>';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Phosphorus (P)</small><strong>' + p + ' kg/ha</strong></div></div>';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Potassium (K)</small><strong>' + k + ' kg/ha</strong></div></div>';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Humidity</small><strong>' + humidity + '%</strong></div></div>';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Soil pH</small><strong>' + ph + '</strong></div></div>';
                html += '<div class="col-6 col-sm-4"><div class="p-2 border rounded bg-white text-center"><small class="text-muted d-block">Rainfall</small><strong>' + rainfall + ' mm</strong></div></div>';
                html += '</div></div></div></div></div>';

                html += '<div class="mt-4 text-center">';
                html += '<p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> Note: This is an AI simulation. Please consult local agronomists and verified soil test results before finalizing crop choices.</p>';
                html += '</div>';
                
                return html;
            }
        })();
    </script>
@endsection
