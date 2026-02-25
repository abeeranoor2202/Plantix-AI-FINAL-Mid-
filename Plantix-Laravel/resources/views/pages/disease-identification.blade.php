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
    <div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
      style="background-image: url({{ asset('assets/img/banner7.jpg') }})"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Disease Identification</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="active">Disease Identification</li>
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
            <div
              class="col-xl-8 col-lg-7 pl-45 pl-md-15 pl-xs-15 services-single-content order-lg-last"
            >
              <div class="thumb">
                <img src="{{ asset('assets/img/field.jpg') }}" alt="Thumb" />
              </div>
              <h2>AI-Powered Plant Disease Identification</h2>
              <p>
                Quickly identify likely diseases from a photo of a leaf or plant
                part. This demo simulates an AI diagnosis to help farmers and
                agronomists triage field problems faster. Upload a clear image
                and get a probable diagnosis with symptoms and suggested
                actions.
              </p>
              <div class="features mt-40 mt-xs-30 mb-30 mb-xs-20">
                <div class="row">
                  <div class="col-xl-5 col-lg-12 col-md-6">
                    <div class="content">
                      <h3>What we provide</h3>
                      <ul class="feature-list-item">
                        <li>Instant demo diagnosis from an uploaded photo</li>
                        <li>List of likely diseases and brief symptoms</li>
                        <li>Practical, field-tested mitigation suggestions</li>
                        <li>Clear image preview before submission</li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-xl-7 col-lg-12 col-md-6 mt-xs-30">
                    <div class="content">
                      <h3>Why use it</h3>
                      <p>
                        Early detection reduces spread and protects yield. Even
                        a demo diagnosis helps prioritize scouting and control
                        measures so you can act faster and save inputs. Combine
                        this tool with local advice for best results.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <blockquote>
                Upload a photo — see a likely diagnosis and next steps in
                seconds.
              </blockquote>
              <h2>How the demo works</h2>
              <p>
                The page accepts an image upload and generates a deterministic,
                simulated diagnosis from a curated list of common plant
                diseases. It shows symptoms, suggested treatments, and
                alternative possibilities. For production, replace the demo
                engine with a trained model or API.
              </p>

              <div class="disease-id-form mt-40">
                <h2 class="mb-25">AI-Powered Plant Disease Identification</h2>
                <p>
                  Upload a clear photo of a crop leaf or plant part. We’ll
                  simulate an AI diagnosis and show a likely disease with basic
                  guidance. This is a demo for UX only.
                </p>

                <form
                  id="diseaseForm"
                  class="contact-form"
                  enctype="multipart/form-data"
                >
                  <div class="row">
                    <div class="col-md-8 mb-3">
                      <label for="cropImage" class="form-label"
                        >Upload Crop Image (JPG/PNG)</label
                      >
                      <input
                        type="file"
                        class="form-control"
                        id="cropImage"
                        name="cropImage"
                        accept="image/*"
                        required
                        data-label="Crop image"
                      />
                      <small class="text-muted"
                        >Tip: Use a sharp, well-lit image focusing on affected
                        area.</small
                      >
                    </div>
                    <div class="col-md-4 mb-3 text-center">
                      <div
                        id="imagePreview"
                        class="border rounded p-2"
                        style="
                          min-height: 140px;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          background: #fafafa;
                        "
                      >
                        <span class="text-muted">Image preview</span>
                      </div>
                    </div>
                  </div>

                  <div class="text-center mt-2">
                    <button type="submit" class="btn btn-theme btn-md">
                      <i class="fas fa-search"></i> Identify Disease
                    </button>
                  </div>
                </form>

                <!-- Diagnosis Result -->
                <div
                  id="diseaseResult"
                  class="alert alert-success mt-40"
                  style="display: none"
                >
                  <h4 class="mb-3">
                    <i class="fas fa-notes-medical"></i> Diagnosis
                  </h4>
                  <div id="diseaseResultContent"></div>
                </div>
              </div>

              <script>
                (function () {
                  var input = document.getElementById("cropImage");
                  var preview = document.getElementById("imagePreview");
                  var form = document.getElementById("diseaseForm");
                  var resultWrap = document.getElementById("diseaseResult");
                  var resultContent = document.getElementById(
                    "diseaseResultContent"
                  );

                  if (!input || !preview || !form) return;

                  // Preview image
                  input.addEventListener("change", function () {
                    var file = this.files && this.files[0];
                    if (!file) {
                      preview.innerHTML =
                        '<span class="text-muted">Image preview</span>';
                      return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (e) {
                      preview.innerHTML =
                        '<img src="' +
                        e.target.result +
                        '" alt="Preview" style="max-width:100%; max-height:180px; object-fit:contain;" />';
                    };
                    reader.readAsDataURL(file);
                  });

                  // Dummy disease profiles (10+)
                  var diseases = [
                    {
                      name: "Early Blight (Tomato/Potato)",
                      symptoms:
                        "Brown concentric rings on older leaves; yellowing around lesions.",
                      treatment:
                        "Remove affected leaves; apply fungicide (chlorothalonil/maneb); rotate crops.",
                    },
                    {
                      name: "Late Blight (Tomato/Potato)",
                      symptoms:
                        "Water-soaked lesions turning brown/black; white mold on undersides in humid weather.",
                      treatment:
                        "Destroy infected plants; copper-based sprays; avoid overhead irrigation.",
                    },
                    {
                      name: "Powdery Mildew",
                      symptoms:
                        "White powdery growth on both sides of leaves; distorted growth.",
                      treatment:
                        "Improve air flow; sulfur or potassium bicarbonate sprays.",
                    },
                    {
                      name: "Downy Mildew",
                      symptoms:
                        "Yellow angular spots on upper leaf; grayish downy growth underside.",
                      treatment:
                        "Remove debris; use phosphonate/copper; ensure good drainage.",
                    },
                    {
                      name: "Leaf Rust (Wheat)",
                      symptoms:
                        "Orange-brown pustules on leaves; reduced vigor and yield.",
                      treatment:
                        "Resistant varieties; triazole fungicides; remove volunteer hosts.",
                    },
                    {
                      name: "Rice Blast",
                      symptoms:
                        "Diamond/elliptical lesions with gray centers on leaves/nodes.",
                      treatment:
                        "Balanced nitrogen; seed treatment; tricyclazole where permitted.",
                    },
                    {
                      name: "Bacterial Leaf Spot",
                      symptoms:
                        "Small dark water-soaked spots; yellow halos; leaf tattering.",
                      treatment:
                        "Copper sprays; avoid handling when wet; sanitize tools.",
                    },
                    {
                      name: "Citrus Canker",
                      symptoms:
                        "Raised corky lesions with yellow halos on leaves/fruit.",
                      treatment:
                        "Prune and burn infected twigs; copper sprays; windbreaks.",
                    },
                    {
                      name: "Anthracnose",
                      symptoms:
                        "Dark sunken lesions on fruit/stems; leaf blight.",
                      treatment:
                        "Sanitation; resistant cultivars; appropriate fungicides.",
                    },
                    {
                      name: "Fusarium Wilt",
                      symptoms:
                        "Unilateral yellowing/wilting; brown vascular discoloration.",
                      treatment:
                        "Resistant varieties; crop rotation; soil solarization.",
                    },
                    {
                      name: "Black Sigatoka (Banana)",
                      symptoms:
                        "Dark streaks turning into necrotic patches; reduced leaf area.",
                      treatment:
                        "Prune infected leaves; systemic fungicides; improved airflow.",
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

                    // Deterministic pseudo-identification based on filename hash (demo)
                    var idx = hashFilename(file.name) % diseases.length;
                    var diag = diseases[idx];

                    var html = "";
                    html +=
                      "<p><strong>Uploaded file:</strong> " +
                      file.name +
                      "</p>";
                    html += '<div class="alert alert-warning mb-3">';
                    html +=
                      '<h5 class="mb-2"><i class="fas fa-virus"></i> Likely Disease</h5>';
                    html +=
                      '<p class="mb-0"><strong>' + diag.name + "</strong></p>";
                    html += "</div>";
                    html +=
                      "<p><strong>Common symptoms:</strong> " +
                      diag.symptoms +
                      "</p>";
                    html +=
                      "<p><strong>Suggested action:</strong> " +
                      diag.treatment +
                      "</p>";

                    // Suggest 2 alternative possibilities
                    var alt1 = diseases[(idx + 1) % diseases.length];
                    var alt2 = diseases[(idx + 2) % diseases.length];
                    html += "<p><strong>Other possibilities:</strong></p>";
                    html += '<ul class="feature-list-item">';
                    html += "<li>" + alt1.name + "</li>";
                    html += "<li>" + alt2.name + "</li>";
                    html += "</ul>";

                    html +=
                      '<p class="mt-3"><small><em>Disclaimer: This is a demo. For accurate diagnosis, combine field scouting with lab tests or expert advice.</em></small></p>';

                    resultContent.innerHTML = html;
                    resultWrap.style.display = "block";
                    resultWrap.scrollIntoView({
                      behavior: "smooth",
                      block: "nearest",
                    });
                  });
                })();
              </script>
            </div>

            <div class="col-xl-4 col-lg-5 mt-md-100 mt-xs-50 services-sidebar">
              <!-- Single Widget -->
              <div class="single-widget services-list-widget">
                <div class="content">
                  <ul>
                    <li>
                      <a href="{{ route('crop-recommendation') }}">Crop Recommendation</a>
                    </li>
                    <li><a href="{{ route('crop-planning') }}">Crop Planning</a></li>
                    <li class="current-item">
                      <a href="{{ route('disease-identification') }}"
                        >Disease Identification</a
                      >
                    </li>
                    <li>
                      <a href="{{ route('fertilizer-recommendation') }}"
                        >Fertilizer Recommendation</a
                      >
                    </li>
                  </ul>
                </div>
              </div>
              <!-- End Single Widget -->
              <div
                class="single-widget quick-contact-widget text-light"
                style="background-image: url({{ asset('assets/img/800x800.png') }})"
              >
                <div class="content">
                  <h3>Need Help?</h3>
                  <p>
                    Need help diagnosing a disease or interpreting a result?
                    Talk to a Plantix‑AI agronomy specialist — call our office
                    and we will connect you with an expert.
                  </p>
                  <h2>+92 330 088123</h2>
                  <h4>
                    <a href="mailto:info@plantixai.com">info@plantixai.com</a>
                  </h4>
                  <a href="{{ route('contact') }}" class="btn btn-light mt-3"
                    >Contact Us</a
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Services Details Area -->
@endsection

