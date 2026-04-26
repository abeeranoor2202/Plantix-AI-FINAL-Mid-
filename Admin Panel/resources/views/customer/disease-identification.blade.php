@extends('layouts.frontend')

@section('title', 'Disease Identification | Plantix-AI')

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
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Disease Identification AI</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-success text-decoration-none">AI Tools</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Disease ID</li>
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
                            <span class="badge bg-success bg-opacity-10 text-success mb-2 px-3 py-2 fs-6 border border-success border-opacity-25 rounded-pill"><i class="fas fa-microscope me-2"></i> Computer Vision</span>
                            <h2 class="fw-bold text-dark mb-3">AI-Powered Plant Disease Identification</h2>
                            <p class="text-muted" style="line-height: 1.8; font-size: 16px;">
                                Quickly identify likely diseases from a photo of a leaf or plant part. This tool simulates an AI diagnosis to help farmers and agronomists triage field problems faster. Upload a clear image and get a probable diagnosis with symptoms and suggested actions.
                            </p>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-box-open text-primary me-2"></i> What we provide</h4>
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted">
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Instant demo diagnosis from photo</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Likely diseases &amp; brief symptoms</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Practical mitigation suggestions</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-star text-warning me-2"></i> Why use it</h4>
                                    <p class="text-muted mb-0 small" style="line-height: 1.6;">
                                        Early detection reduces spread and protects yield. Even a demo diagnosis helps prioritize scouting and control measures so you can act faster and save inputs. Combine this tool with local advice for best results.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-25">

                        <!-- Tool Form -->
                        <div class="disease-id-form p-4 rounded-4" style="background: rgba(16, 185, 129, 0.03); border: 2px dashed var(--agri-primary-light);">
                            <div class="text-center mb-4">
                                <div class="d-inline-flex bg-white text-primary p-3 rounded-circle shadow-sm mb-3">
                                    <i class="fas fa-cloud-upload-alt fs-3"></i>
                                </div>
                                <h3 class="fw-bold text-dark fs-4">Upload a Crop Image</h3>
                                <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">
                                    Upload a clear photo of a crop leaf or plant part. We’ll simulate an AI diagnosis and show a likely disease for demo UX purposes.
                                </p>
                            </div>

                            <form id="diseaseForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row align-items-center g-4">
                                    <div class="col-md-7">
                                        <div class="bg-white p-3 rounded-3 border">
                                            <input
                                                type="file"
                                                class="form-control border-0"
                                                id="cropImage"
                                                name="image"
                                                accept="image/jpeg,image/png,image/webp"
                                                required
                                            />
                                        </div>
                                        <div class="mt-2 text-muted small"><i class="fas fa-info-circle me-1"></i> Tip: Use a sharp, well-lit image focusing on the affected leaf area. JPEG / PNG / WebP, max 10 MB.</div>
                                    </div>
                                    <div class="col-md-5 text-center">
                                        <div id="imagePreview" class="bg-white border rounded-3 d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="height: 140px;">
                                            <span class="text-muted small"><i class="far fa-image fs-4 mb-2 d-block"></i> Image Preview</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" id="analyzeBtn" class="btn-agri btn-agri-primary px-5 py-3 fs-5 shadow-sm">
                                        <i class="fas fa-search me-2"></i> Analyze &amp; Identify Disease
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Diagnosis Result -->
                        <div id="diseaseResult" class="mt-4" style="display: none;">
                            <div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">
                                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                                    <div id="resultIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; background: #16a34a; color: #fff;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0 m-0" id="resultTitle">Analysis Complete</h4>
                                </div>
                                <div id="diseaseResultContent"></div>
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
                                <a href="{{ route('disease.identification') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none" style="background: var(--agri-primary-light); color: var(--agri-primary);">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-primary" style="width: 36px; height: 36px;"><i class="fas fa-microscope text-primary"></i></div>
                                    <span class="fw-bold">Disease Identification</span>
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
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="position-relative z-index-1">
                            <div class="bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 64px; height: 64px; font-size: 28px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-white">Need Expert Help?</h4>
                            <p class="mb-4 text-white text-opacity-75 small">
                                Need help diagnosing a disease or interpreting a result? Talk to a Plantix-AI agronomy specialist.
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
        var input      = document.getElementById("cropImage");
        var preview    = document.getElementById("imagePreview");
        var form       = document.getElementById("diseaseForm");
        var resultWrap = document.getElementById("diseaseResult");
        var resultContent = document.getElementById("diseaseResultContent");
        var resultIcon    = document.getElementById("resultIcon");
        var resultTitle   = document.getElementById("resultTitle");
        var analyzeBtn    = document.getElementById("analyzeBtn");

        if (!input || !form) return;

        // ── Image preview ─────────────────────────────────────────────────────
        input.addEventListener("change", function () {
            var file = this.files && this.files[0];
            if (!file) {
                preview.innerHTML = '<span class="text-muted small"><i class="far fa-image fs-4 mb-2 d-block"></i> Image Preview</span>';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="h-100 w-100 object-fit-cover shadow-sm" />';
            };
            reader.readAsDataURL(file);
        });

        // ── Helpers ───────────────────────────────────────────────────────────
        function setBtn(loading) {
            if (!analyzeBtn) return;
            analyzeBtn.disabled = loading;
            analyzeBtn.innerHTML = loading
                ? '<i class="fas fa-spinner fa-spin me-2"></i> Analyzing...'
                : '<i class="fas fa-search me-2"></i> Analyze &amp; Identify Disease';
        }

        function showResult(html, isInvalid) {
            resultContent.innerHTML = html;
            resultWrap.style.display = "block";

            if (isInvalid) {
                resultIcon.style.background = "#dc2626";
                resultIcon.innerHTML = '<i class="fas fa-times"></i>';
                resultTitle.textContent = "Image Not Recognised";
            } else {
                resultIcon.style.background = "#16a34a";
                resultIcon.innerHTML = '<i class="fas fa-check"></i>';
                resultTitle.textContent = "Diagnosis Complete";
            }

            resultWrap.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }

        // ── Render: invalid image ─────────────────────────────────────────────
        function renderInvalid(message, confidence) {
            var pct = confidence !== null ? Math.round(confidence * 100) : 0;
            var html = '';
            html += '<div class="alert alert-danger mb-4" style="border-radius:12px;">';
            html += '<div class="d-flex align-items-start gap-3">';
            html += '<i class="fas fa-exclamation-circle fs-4 text-danger mt-1"></i>';
            html += '<div>';
            html += '<div class="fw-bold mb-1">Invalid Image</div>';
            html += '<div>' + message + '</div>';
            if (pct > 0) {
                html += '<div class="mt-2 text-muted small">Model confidence: ' + pct + '% (minimum required: 70%)</div>';
            }
            html += '</div></div></div>';
            html += '<div class="text-center mt-3">';
            html += '<p class="text-muted mb-3">Tips for a good image:</p>';
            html += '<ul class="list-unstyled text-muted small text-start d-inline-block">';
            html += '<li><i class="fas fa-check-circle text-success me-2"></i> Focus on a single leaf or plant part</li>';
            html += '<li><i class="fas fa-check-circle text-success me-2"></i> Use good lighting — avoid shadows</li>';
            html += '<li><i class="fas fa-check-circle text-success me-2"></i> Keep the camera steady and close</li>';
            html += '<li><i class="fas fa-times-circle text-danger me-2"></i> Do not upload people, objects, or backgrounds</li>';
            html += '</ul></div>';
            showResult(html, true);
        }

        // ── Render: successful diagnosis ──────────────────────────────────────
        function renderSuccess(data) {
            var disease    = data.detected_disease || "Unknown";
            var pct        = data.confidence_pct !== null ? data.confidence_pct : 0;
            var suggestion = data.suggestion || null;
            var preds      = data.all_predictions || [];

            var html = '';

            // Disease badge
            html += '<div class="alert alert-warning mb-4 bg-warning bg-opacity-10 border-warning border-opacity-25" style="border-radius:12px;">';
            html += '<div class="d-flex align-items-center gap-3">';
            html += '<div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-virus fs-4"></i></div>';
            html += '<div>';
            html += '<h6 class="text-warning text-uppercase fw-bold mb-1" style="font-size:12px;letter-spacing:1px;">Disease Detected</h6>';
            html += '<h4 class="fw-bold text-dark mb-0">' + disease + '</h4>';
            html += '<div class="mt-1">';
            html += '<div class="progress" style="height:6px;width:160px;">';
            html += '<div class="progress-bar bg-warning" style="width:' + pct + '%"></div>';
            html += '</div>';
            html += '<small class="text-muted">Confidence: ' + pct + '%</small>';
            html += '</div></div></div></div>';

            // Treatment suggestion
            if (suggestion) {
                if (suggestion.description) {
                    html += '<div class="mb-3 px-3">';
                    html += '<h6 class="fw-bold text-dark mb-2"><i class="fas fa-search-plus text-muted me-2"></i> Description:</h6>';
                    html += '<p class="text-muted">' + suggestion.description + '</p>';
                    html += '</div>';
                }
                if (suggestion.organic_treatment) {
                    html += '<div class="mb-3 px-3">';
                    html += '<h6 class="fw-bold text-success mb-2"><i class="fas fa-leaf text-success me-2"></i> Organic Treatment:</h6>';
                    html += '<p class="text-dark">' + suggestion.organic_treatment + '</p>';
                    html += '</div>';
                }
                if (suggestion.chemical_treatment) {
                    html += '<div class="mb-3 px-3">';
                    html += '<h6 class="fw-bold text-primary mb-2"><i class="fas fa-flask text-primary me-2"></i> Chemical Treatment:</h6>';
                    html += '<p class="text-dark">' + suggestion.chemical_treatment + '</p>';
                    html += '</div>';
                }
                if (suggestion.preventive_measures) {
                    html += '<div class="mb-4 px-3 pb-3 border-bottom">';
                    html += '<h6 class="fw-bold text-info mb-2"><i class="fas fa-shield-alt text-info me-2"></i> Prevention:</h6>';
                    html += '<p class="text-dark">' + suggestion.preventive_measures + '</p>';
                    html += '</div>';
                }
            }

            // Other top predictions
            if (preds.length > 1) {
                html += '<div class="px-3 mt-3">';
                html += '<h6 class="fw-bold text-dark mb-3">Other Possibilities:</h6>';
                html += '<div class="d-flex flex-wrap gap-2">';
                for (var i = 1; i < Math.min(preds.length, 4); i++) {
                    var p = preds[i];
                    if (p.confidence > 0.001) {
                        html += '<span class="badge bg-light text-muted border px-3 py-2">';
                        html += (p.display_name || p.disease) + ' (' + Math.round(p.confidence * 100) + '%)';
                        html += '</span>';
                    }
                }
                html += '</div></div>';
            }

            html += '<div class="mt-4 pt-3 text-center">';
            html += '<div class="alert alert-secondary py-2 px-3 d-inline-block m-0" style="font-size:13px;">';
            html += '<i class="fas fa-exclamation-triangle text-muted me-1"></i> For accurate diagnosis, combine AI results with field scouting or expert advice.';
            html += '</div></div>';

            showResult(html, false);
        }

        // ── Render: manual review / error ─────────────────────────────────────
        function renderManualReview() {
            var html = '<div class="alert alert-info text-center" style="border-radius:12px;">';
            html += '<i class="fas fa-user-md fs-3 mb-2 d-block text-info"></i>';
            html += '<div class="fw-bold mb-1">Sent for Expert Review</div>';
            html += '<div class="text-muted small">Our team will review your image and provide a diagnosis shortly.</div>';
            html += '</div>';
            showResult(html, false);
        }

        // ── Poll for result ───────────────────────────────────────────────────
        function pollStatus(reportId, attempts) {
            attempts = attempts || 0;
            if (attempts > 30) { // max ~60 seconds
                setBtn(false);
                renderManualReview();
                return;
            }

            fetch("{{ route('disease.poll', ['id' => '__ID__']) }}".replace('__ID__', reportId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) { setBtn(false); renderManualReview(); return; }

                var d = res.data;

                if (d.status === 'pending') {
                    setTimeout(function() { pollStatus(reportId, attempts + 1); }, 2000);
                    return;
                }

                setBtn(false);

                if (d.status === 'invalid_image') {
                    renderInvalid(
                        d.invalid_message || 'This image does not appear to be a plant leaf. Please upload a clear image of a plant for disease identification.',
                        d.confidence_score
                    );
                } else if (d.status === 'processed') {
                    renderSuccess(d);
                } else {
                    renderManualReview();
                }
            })
            .catch(function() {
                setBtn(false);
                renderManualReview();
            });
        }

        // ── Form submit ───────────────────────────────────────────────────────
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var file = input.files && input.files[0];
            if (!file) { alert("Please select an image."); return; }

            // Client-side size guard (10 MB)
            if (file.size > 10 * 1024 * 1024) {
                alert("Image is too large. Please upload an image under 10 MB.");
                return;
            }

            setBtn(true);
            resultWrap.style.display = "none";

            var formData = new FormData(form);

            fetch("{{ route('disease.detect') }}", {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) {
                    setBtn(false);
                    alert(res.message || "Submission failed. Please try again.");
                    return;
                }
                // Start polling with the report ID
                pollStatus(res.data.id);
            })
            .catch(function() {
                setBtn(false);
                alert("Network error. Please check your connection and try again.");
            });
        });
    })();
    </script>
@endsection
