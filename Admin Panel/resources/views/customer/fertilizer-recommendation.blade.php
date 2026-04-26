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

                            <form id="fertilizerForm" class="contact-form" method="POST" action="{{ route('fertilizer.recommendation.recommend') }}">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12">
                                        <h5 class="fw-bold text-dark fs-6 mb-1">Soil Nutrients (kg/acre)</h5>
                                        <p class="text-muted small mb-3">Enter your soil test values. The model uses N, P, K to recommend the right fertilizer.</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nitrogen" class="form-label fw-bold text-muted small">Nitrogen (N) <span class="text-muted fw-normal">[0–42]</span></label>
                                        <input type="number" step="1" id="nitrogen" name="nitrogen" class="form-agri" placeholder="e.g. 24" min="0" max="500" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phosphorus" class="form-label fw-bold text-muted small">Phosphorus (P) <span class="text-muted fw-normal">[0–42]</span></label>
                                        <input type="number" step="1" id="phosphorus" name="phosphorus" class="form-agri" placeholder="e.g. 21" min="0" max="500" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="potassium" class="form-label fw-bold text-muted small">Potassium (K) <span class="text-muted fw-normal">[0–19]</span></label>
                                        <input type="number" step="1" id="potassium" name="potassium" class="form-agri" placeholder="e.g. 10" min="0" max="500" required>
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
            var form = document.getElementById('fertilizerForm');
            var result = document.getElementById('fertResult');
            var csrfToken = '{{ csrf_token() }}';

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                var payload = {
                    nitrogen: parseFloat(document.getElementById('nitrogen').value),
                    phosphorus: parseFloat(document.getElementById('phosphorus').value),
                    potassium: parseFloat(document.getElementById('potassium').value)
                };

                if (isNaN(payload.nitrogen) || isNaN(payload.phosphorus) || isNaN(payload.potassium)) {
                    alert('Please provide valid values for N, P, and K.');
                    return;
                }

                var btn = form.querySelector('button[type="submit"]');
                var originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating Plan...';
                btn.disabled = true;

                try {
                    var response = await fetch("{{ route('fertilizer.recommendation.recommend') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    var body = await response.json();
                    if (!response.ok || !body.success) {
                        var validationMessages = body.errors
                            ? Object.values(body.errors).flat().join(' ')
                            : (body.message || 'Fertilizer recommendation request failed.');
                        throw new Error(validationMessages);
                    }

                    result.innerHTML = renderRecommendation(body.data, payload);
                    result.style.display = 'block';
                    result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (error) {
                    alert(error.message || 'Unable to generate recommendation right now.');
                } finally {
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                }
            });

            function renderRecommendation(data, payload) {
                var plan = data.fertilizer_plan || [];
                var html = '';

                html += '<div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">';
                html += '<div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">';
                html += '<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;"><i class="fas fa-flask"></i></div>';
                html += '<h4 class="fw-bold text-dark mb-0 m-0">AI Fertilizer Plan</h4>';
                html += '</div>';

                html += '<div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-3 border">';
                html += '<div><span class="text-muted small d-block mb-1">Soil Input</span><h5 class="fw-bold text-dark m-0">N=' + payload.nitrogen + ' P=' + payload.phosphorus + ' K=' + payload.potassium + '</h5></div>';
                html += '<div class="text-end"><span class="text-muted small d-block mb-1">Estimated Cost</span><span class="fw-medium text-dark">PKR ' + Number(data.estimated_cost_pkr || 0).toLocaleString() + '</span></div>';
                html += '</div>';

                if (plan.length > 0) {
                    html += '<h5 class="fw-bold text-dark mb-3"><i class="fas fa-prescription-bottle-alt text-muted me-2"></i> Application Plan</h5>';
                    html += '<div class="d-flex flex-column gap-3 mb-4">';
                    plan.forEach(function (item) {
                        html += '<div class="border rounded-3 p-3 bg-white border-success border-opacity-50">';
                        html += '<div class="d-flex justify-content-between align-items-start mb-2">';
                        html += '<h6 class="fw-bold text-dark mb-0">' + (item.name || 'Fertilizer') + '</h6>';
                        html += '<span class="badge bg-success bg-opacity-10 text-success">' + (item.type || 'Nutrient') + '</span>';
                        html += '</div>';
                        html += '<p class="text-muted mb-1 small"><strong>Dose:</strong> ' + (item.dose_kg_per_acre || 0) + ' kg/acre</p>';
                        html += '<p class="text-muted mb-1 small"><strong>Timing:</strong> ' + (item.timing || 'As advised') + '</p>';
                        html += '<p class="text-muted mb-0 small"><strong>Notes:</strong> ' + (item.notes || '-') + '</p>';
                        html += '</div>';
                    });
                    html += '</div>';
                }

                html += '<div class="border rounded-3 bg-light p-3 mb-3">';
                html += '<h6 class="fw-bold text-dark mb-2">Application Instructions</h6>';
                html += '<p class="text-muted mb-0 small" style="white-space: pre-line;">' + (data.application_instructions || 'Follow agronomist guidance for split application.') + '</p>';
                html += '</div>';

                html += '<p class="text-muted small mb-0">Soil snapshot: N=' + payload.nitrogen + ', P=' + payload.phosphorus + ', K=' + payload.potassium + '</p>';
                html += '</div>';

                return html;
            }

            document.getElementById('resetBtn').addEventListener('click', function () {
                form.reset();
                result.style.display = 'none';
                result.innerHTML = '';
            });
        })();
    </script>
@endsection
