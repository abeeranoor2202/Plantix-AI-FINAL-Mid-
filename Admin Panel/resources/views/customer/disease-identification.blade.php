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

                            <form id="diseaseForm" class="contact-form" enctype="multipart/form-data">
                                <div class="row align-items-center g-4">
                                    <div class="col-md-7">
                                        <div class="bg-white p-3 rounded-3 border">
                                            <input
                                                type="file"
                                                class="form-control border-0"
                                                id="cropImage"
                                                name="cropImage"
                                                accept="image/*"
                                                required
                                            />
                                        </div>
                                        <div class="mt-2 text-muted small"><i class="fas fa-info-circle me-1"></i> Tip: Use a sharp, well-lit image focusing on the affected area.</div>
                                    </div>
                                    <div class="col-md-5 text-center">
                                        <div id="imagePreview" class="bg-white border rounded-3 d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="height: 140px;">
                                            <span class="text-muted small"><i class="far fa-image fs-4 mb-2 d-block"></i> Image Preview</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn-agri btn-agri-primary px-5 py-3 fs-5 shadow-sm">
                                        <i class="fas fa-search me-2"></i> Analyze &amp; Identify Disease
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Diagnosis Result -->
                        <div id="diseaseResult" class="mt-4" style="display: none;">
                            <div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">
                                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-0 m-0">Diagnosis Complete</h4>
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
            var input = document.getElementById("cropImage");
            var preview = document.getElementById("imagePreview");
            var form = document.getElementById("diseaseForm");
            var resultWrap = document.getElementById("diseaseResult");
            var resultContent = document.getElementById("diseaseResultContent");

            if (!input || !preview || !form) return;

            // Preview image
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

            // Dummy disease profiles
            var diseases = [
                {
                    name: "Early Blight (Tomato/Potato)",
                    symptoms: "Brown concentric rings on older leaves; yellowing around lesions.",
                    treatment: "Remove affected leaves; apply fungicide (chlorothalonil/maneb); rotate crops.",
                },
                {
                    name: "Late Blight (Tomato/Potato)",
                    symptoms: "Water-soaked lesions turning brown/black; white mold on undersides in humid weather.",
                    treatment: "Destroy infected plants; copper-based sprays; avoid overhead irrigation.",
                },
                {
                    name: "Powdery Mildew",
                    symptoms: "White powdery growth on both sides of leaves; distorted growth.",
                    treatment: "Improve air flow; sulfur or potassium bicarbonate sprays.",
                },
                {
                    name: "Downy Mildew",
                    symptoms: "Yellow angular spots on upper leaf; grayish downy growth underside.",
                    treatment: "Remove debris; use phosphonate/copper; ensure good drainage.",
                },
                {
                    name: "Leaf Rust (Wheat)",
                    symptoms: "Orange-brown pustules on leaves; reduced vigor and yield.",
                    treatment: "Resistant varieties; triazole fungicides; remove volunteer hosts.",
                },
                {
                    name: "Rice Blast",
                    symptoms: "Diamond/elliptical lesions with gray centers on leaves/nodes.",
                    treatment: "Balanced nitrogen; seed treatment; tricyclazole where permitted.",
                },
                {
                    name: "Bacterial Leaf Spot",
                    symptoms: "Small dark water-soaked spots; yellow halos; leaf tattering.",
                    treatment: "Copper sprays; avoid handling when wet; sanitize tools.",
                },
                {
                    name: "Citrus Canker",
                    symptoms: "Raised corky lesions with yellow halos on leaves/fruit.",
                    treatment: "Prune and burn infected twigs; copper sprays; windbreaks.",
                },
                {
                    name: "Anthracnose",
                    symptoms: "Dark sunken lesions on fruit/stems; leaf blight.",
                    treatment: "Sanitation; resistant cultivars; appropriate fungicides.",
                },
                {
                    name: "Fusarium Wilt",
                    symptoms: "Unilateral yellowing/wilting; brown vascular discoloration.",
                    treatment: "Resistant varieties; crop rotation; soil solarization.",
                },
                {
                    name: "Black Sigatoka (Banana)",
                    symptoms: "Dark streaks turning into necrotic patches; reduced leaf area.",
                    treatment: "Prune infected leaves; systemic fungicides; improved airflow.",
                },
            ];

            function hashFilename(name) {
                var h = 0;
                for (var i = 0; i < name.length; i++) {
                    h = (h << 5) - h + name.charCodeAt(i);
                    h |= 0;
                }
                return Math.abs(h);
            }

            form.addEventListener("submit", function (e) {
                e.preventDefault();
                var file = input.files && input.files[0];
                if (!file) {
                    alert("Please upload an image.");
                    return;
                }

                // Show loading state
                var btn = form.querySelector('button[type="submit"]');
                var originalBtnHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analyzing...';
                btn.disabled = true;

                setTimeout(function() {
                    // Deterministic pseudo-identification based on filename hash (demo)
                    var idx = hashFilename(file.name) % diseases.length;
                    var diag = diseases[idx];

                    var html = "";
                    html += '<div class="alert alert-warning mb-4 bg-warning bg-opacity-10 border-warning border-opacity-25">';
                    html += '<div class="d-flex align-items-center gap-3">';
                    html += '<div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;"><i class="fas fa-virus fs-4"></i></div>';
                    html += '<div>';
                    html += '<h6 class="text-warning text-uppercase fw-bold mb-1" style="font-size: 12px; letter-spacing: 1px;">Likely Disease Found</h6>';
                    html += '<h4 class="fw-bold text-dark mb-0">' + diag.name + '</h4>';
                    html += '</div></div></div>';
                    
                    html += '<div class="mb-3 px-3">';
                    html += '<h6 class="fw-bold text-dark mb-2"><i class="fas fa-search-plus text-muted me-2"></i> Common Symptoms:</h6>';
                    html += '<p class="text-muted">' + diag.symptoms + '</p>';
                    html += '</div>';

                    html += '<div class="mb-4 px-3 pb-3 border-bottom">';
                    html += '<h6 class="fw-bold text-success mb-2"><i class="fas fa-tools text-success me-2"></i> Suggested Action:</h6>';
                    html += '<p class="text-dark fw-medium">' + diag.treatment + '</p>';
                    html += '</div>';

                    // Suggest 2 alternative possibilities
                    var alt1 = diseases[(idx + 1) % diseases.length];
                    var alt2 = diseases[(idx + 2) % diseases.length];
                    html += '<div class="px-3">';
                    html += '<h6 class="fw-bold text-dark mb-3">Other Possibilities:</h6>';
                    html += '<div class="d-flex flex-wrap gap-2">';
                    html += '<span class="badge bg-light text-muted border px-3 py-2">' + alt1.name + '</span>';
                    html += '<span class="badge bg-light text-muted border px-3 py-2">' + alt2.name + '</span>';
                    html += '</div>';
                    html += '</div>';

                    html += '<div class="mt-4 pt-3 text-center">';
                    html += '<div class="alert alert-secondary py-2 px-3 d-inline-block m-0" style="font-size: 13px;">';
                    html += '<i class="fas fa-exclamation-triangle text-muted me-1"></i> Disclaimer: This is a demo. For accurate diagnosis, combine field scouting with lab tests or expert advice.';
                    html += '</div></div>';

                    resultContent.innerHTML = html;
                    resultWrap.style.display = "block";
                    resultWrap.scrollIntoView({ behavior: "smooth", block: "nearest" });

                    // Reset button
                    btn.innerHTML = originalBtnHtml;
                    btn.disabled = false;
                }, 1500); // Simulated delay
            });
        })();
    </script>
@endsection
