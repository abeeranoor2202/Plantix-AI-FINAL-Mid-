@extends('layouts.frontend')

@section('title', 'Fertilizer Recommendation | Plantix-AI')

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
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Fertilizer Recommendation AI</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-success text-decoration-none">AI Tools</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Fertilizer Recs</li>
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
                            <span class="badge bg-success bg-opacity-10 text-success mb-2 px-3 py-2 fs-6 border border-success border-opacity-25 rounded-pill"><i class="fas fa-vial me-2"></i> Soil Chemistry</span>
                            <h2 class="fw-bold text-dark mb-3">Smart Crop Nutrition Engine</h2>
                            <p class="text-muted" style="line-height: 1.8; font-size: 16px;">
                                Get practical guidance on what to sow and how to balance nutrients for healthy crops. Enter your field conditions and soil test values (N, P, K) and receive a tailored recommendation for crop suitability and fertilizer dosage to correct deficits.
                            </p>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-bullseye text-primary me-2"></i> Actionable Insights</h4>
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted small" style="line-height: 1.6;">
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Deficit-based N-P-K guidance</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Suitable crop suggestion</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Data-driven top-up strategies</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border text-center d-flex flex-column justify-content-center">
                                    <i class="fas fa-leaf text-success fs-2 mb-3"></i>
                                    <p class="text-dark fw-bold mb-0" style="font-size: 15px;">
                                        "Smart nutrition decisions start with understanding your soil & crop needs."
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tool Form -->
                        <div class="mt-5 p-4 rounded-4" style="background: rgba(16, 185, 129, 0.03); border: 2px dashed var(--agri-primary-light);">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark fs-4">Analyze Target Crop & Fertilizer Needs</h3>
                                <p class="text-muted mb-0 mx-auto" style="max-width: 600px;">
                                    Fill in the environmental details below to find out how to balance your nutrition.
                                </p>
                            </div>

                            <form id="fertilizerForm" class="contact-form">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="cropType" class="form-label fw-bold text-dark small">Target Crop Intention</label>
                                        <select id="cropType" class="form-agri">
                                            <option value="rice">Rice</option>
                                            <option value="wheat">Wheat</option>
                                            <option value="maize">Maize</option>
                                            <option value="soybean">Soybean</option>
                                            <option value="cotton">Cotton</option>
                                            <option value="potato">Potato</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="soilType" class="form-label fw-bold text-dark small">Soil Texture</label>
                                        <select id="soilType" class="form-agri">
                                            <option value="loamy">Loamy</option>
                                            <option value="sandy">Sandy</option>
                                            <option value="clay">Clay</option>
                                            <option value="silty">Silty</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="temperature" class="form-label fw-bold text-dark small">Temperature (°C)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-thermometer-half"></i></span>
                                            <input type="number" step="0.1" id="temperature" class="form-agri border-start-0" placeholder="e.g. 25" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="humidity" class="form-label fw-bold text-dark small">Air Humidity (%)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-cloud-rain"></i></span>
                                            <input type="number" step="0.1" id="humidity" class="form-agri border-start-0" placeholder="e.g. 60" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="moisture" class="form-label fw-bold text-dark small">Soil Moisture (%)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-water"></i></span>
                                            <input type="number" step="0.1" id="moisture" class="form-agri border-start-0" placeholder="e.g. 30" required>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2 border-top pt-4">
                                        <h5 class="fw-bold text-dark fs-6 mb-3">Soil Nutrients Deficit Analysis (ppm)</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nitrogen" class="form-label fw-bold text-muted small">Nitrogen (N)</label>
                                        <input type="number" step="1" id="nitrogen" class="form-agri" placeholder="e.g. 20" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phosphorus" class="form-label fw-bold text-muted small">Phosphorus (P)</label>
                                        <input type="number" step="1" id="phosphorus" class="form-agri" placeholder="e.g. 15" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="potassium" class="form-label fw-bold text-muted small">Potassium (K)</label>
                                        <input type="number" step="1" id="potassium" class="form-agri" placeholder="e.g. 40" required>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-center gap-3 mt-5">
                                    <button type="submit" class="btn-agri btn-agri-primary px-5 py-3 fs-5 shadow-sm">
                                        <i class="fas fa-magic me-2"></i> Generate Plan
                                    </button>
                                    <button type="button" id="resetBtn" class="btn-agri btn-agri-outline p-3 shadow-sm text-dark">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Result Area -->
                        <div id="fertResult" class="mt-4" style="display:none;"></div>

                    </div>
                </div>
                <!-- End Main Content -->

                <!-- Sidebar Settings -->
                <div class="col-lg-4">
                    <div class="card-agri p-4 border-0 mb-4 sticky-top" style="top: 20px;">
                        <h4 class="fw-bold text-dark fs-5 mb-4 border-bottom pb-3">AI Tools</h4>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <li>
                                <a href="{{ route('crop.recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-seedling text-secondary"></i></div>
                                    <span class="fw-medium">Crop Recommendation</span>
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
                                <a href="{{ route('fertilizer.recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none" style="background: var(--agri-primary-light); color: var(--agri-primary);">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-primary" style="width: 36px; height: 36px;"><i class="fas fa-flask text-primary"></i></div>
                                    <span class="fw-bold">Fertilizer Recommendation</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-agri p-4 border-0 bg-success text-white position-relative overflow-hidden text-center sticky-top" style="top: 380px;">
                        <div class="position-absolute" style="top: -20px; right: -20px; font-size: 150px; opacity: 0.1; transform: rotate(-15deg);">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="position-relative z-index-1">
                            <div class="bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 64px; height: 64px; font-size: 28px;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-white">Need Fertilizers?</h4>
                            <p class="mb-4 text-white text-opacity-75 small">
                                Short on Urea or DAP? Visit our marketplace to buy high-quality agricultural inputs.
                            </p>
                            <a href="{{ route('shop') }}" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-success shadow-sm">Go to Shop</a>
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
            function suggestFertilizer(n, p, k, crop) {
                // Simple deficit-based suggestion (demo)
                var rec = [];
                var target = { "rice": { N: 40, P: 20, K: 40 }, "wheat": { N: 50, P: 25, K: 30 }, "maize": { N: 60, P: 30, K: 40 }, "soybean": { N: 20, P: 20, K: 30 }, "cotton": { N: 50, P: 25, K: 40 }, "potato": { N: 80, P: 50, K: 150 } }[crop] || { N: 40, P: 20, K: 40 };
                var dn = target.N - n; var dp = target.P - p; var dk = target.K - k;
                
                if (dn > 10) rec.push({ type: 'danger', icon: 'fas fa-arrow-up', msg: 'Heavy Nitrogen deficit!', detail: 'Apply nitrogen-rich fertilizer (e.g., Urea) approx ' + Math.max(0, Math.round(dn)) + ' kg/ha equivalent to meet target.'});
                else if (dn > 0) rec.push({ type: 'warning', icon: 'fas fa-arrow-up', msg: 'Slight Nitrogen deficit.', detail: 'Light nitrogen dressing (~' + Math.round(dn) + ' kg/ha equivalent) required.'});
                else rec.push({ type: 'success', icon: 'fas fa-check-circle', msg: 'Nitrogen levels adequate.', detail: 'Avoid extra N application.'});

                if (dp > 5) rec.push({ type: 'danger', icon: 'fas fa-arrow-up', msg: 'Phosphorus deficit!', detail: 'Apply phosphorus fertilizer (e.g., SSP or DAP) approx ' + Math.max(0, Math.round(dp)) + ' kg/ha equivalent.'});
                else if (dp > 0) rec.push({ type: 'warning', icon: 'fas fa-arrow-up', msg: 'Slight Phosphorus deficit.', detail: 'Small phosphorus top-up (~' + Math.round(dp) + ' kg/ha equivalent) is recommended.'});
                else rec.push({ type: 'success', icon: 'fas fa-check-circle', msg: 'Phosphorus levels adequate.', detail: 'Phosphorus is sufficient.'});

                if (dk > 10) rec.push({ type: 'danger', icon: 'fas fa-arrow-up', msg: 'Potassium deficit!', detail: 'Apply potassium fertilizer (e.g., MOP or SOP) approx ' + Math.max(0, Math.round(dk)) + ' kg/ha equivalent.'});
                else if (dk > 0) rec.push({ type: 'warning', icon: 'fas fa-arrow-up', msg: 'Slight Potassium deficit.', detail: 'Light potassium top-up (~' + Math.round(dk) + ' kg/ha equivalent) would boost yields.'});
                else rec.push({ type: 'success', icon: 'fas fa-check-circle', msg: 'Potassium levels adequate.', detail: 'Potassium is sufficient.'});

                return rec;
            }

            function chooseCrop(params) {
                var crops = {
                    rice: { t: [20, 35], m: [40, 100], score: 0 },
                    wheat: { t: [5, 25], m: [20, 60], score: 0 },
                    maize: { t: [18, 30], m: [25, 70], score: 0 },
                    soybean: { t: [15, 30], m: [20, 60], score: 0 },
                    cotton: { t: [20, 35], m: [15, 50], score: 0 },
                    potato: { t: [10, 25], m: [25, 80], score: 0 }
                };
                Object.keys(crops).forEach(function (c) {
                    var cfg = crops[c];
                    var t = params.temperature; var m = params.moisture; var h = params.humidity;
                    if (t >= cfg.t[0] && t <= cfg.t[1]) cfg.score += 2;
                    else cfg.score -= 1;
                    if (m >= cfg.m[0] && m <= cfg.m[1]) cfg.score += 2;
                    else cfg.score -= 1;
                    if (params.soilType === 'loamy') cfg.score += 0.5;
                    if (params.cropType && params.cropType === c) cfg.score += 3; // user preference boost
                });
                var list = Object.keys(crops).map(function (k) { return { crop: k, score: crops[k].score }; });
                list.sort(function (a, b) { return b.score - a.score; });
                return list;
            }

            var form = document.getElementById('fertilizerForm');
            var result = document.getElementById('fertResult');
            
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var params = {
                    cropType: document.getElementById('cropType').value,
                    soilType: document.getElementById('soilType').value,
                    temperature: parseFloat(document.getElementById('temperature').value) || 25,
                    humidity: parseFloat(document.getElementById('humidity').value) || 50,
                    moisture: parseFloat(document.getElementById('moisture').value) || 30,
                    nitrogen: parseFloat(document.getElementById('nitrogen').value) || 20,
                    phosphorus: parseFloat(document.getElementById('phosphorus').value) || 15,
                    potassium: parseFloat(document.getElementById('potassium').value) || 40
                };

                // Show loading state
                var btn = form.querySelector('button[type="submit"]');
                var originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating Plan...';
                btn.disabled = true;

                setTimeout(function() {
                    var ranked = chooseCrop(params);
                    var primary = ranked[0].crop;
                    var alternatives = ranked.slice(1, 4).map(function (r) { return r.crop; });

                    var fert = suggestFertilizer(params.nitrogen, params.phosphorus, params.potassium, primary);

                    var html = '';
                    
                    html += '<div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">';
                    html += '<div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">';
                    html += '<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;"><i class="fas fa-clipboard-list"></i></div>';
                    html += '<h4 class="fw-bold text-dark mb-0 m-0">Recommendations</h4>';
                    html += '</div>';
                    
                    html += '<div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-3 border">';
                    html += '<div><span class="text-muted small d-block mb-1">Target AI Match:</span><h5 class="fw-bold text-dark m-0">' + primary.charAt(0).toUpperCase() + primary.slice(1) + '</h5></div>';
                    html += '<div class="text-end"><span class="text-muted small d-block mb-1">Alternatives:</span><span class="fw-medium text-dark">' + alternatives.map(function (a) { return a.charAt(0).toUpperCase() + a.slice(1) }).join(', ') + '</span></div>';
                    html += '</div>';

                    html += '<h5 class="fw-bold text-dark mb-3"><i class="fas fa-prescription-bottle-alt text-muted me-2"></i> Fertility Action Plan</h5>';
                    html += '<div class="d-flex flex-column gap-3 mb-4">';
                    
                    fert.forEach(function(f) {
                        html += '<div class="border rounded-3 p-3 bg-white border-' + (f.type === 'danger' ? 'danger border-opacity-50' : (f.type === 'success' ? 'success border-opacity-50' : 'warning border-opacity-50')) + '">';
                        html += '<div class="d-flex gap-3">';
                        html += '<div class="text-' + f.type + ' mt-1"><i class="' + f.icon + ' fs-5"></i></div>';
                        html += '<div><h6 class="fw-bold text-dark mb-1">' + f.msg + '</h6><p class="text-muted mb-0 small" style="line-height: 1.5;">' + f.detail + '</p></div>';
                        html += '</div></div>';
                    });

                    html += '</div>';
                    html += '<p class="text-center text-muted small m-0"><i class="fas fa-exclamation-triangle me-1"></i> For planning purposes only.</p>';
                    html += '</div>';

                    result.innerHTML = html;
                    result.style.display = 'block';
                    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                }, 1200);
            });

            document.getElementById('resetBtn').addEventListener('click', function () {
                form.reset();
                result.style.display = 'none';
                result.innerHTML = '';
            });
        })();
    </script>
@endsection
