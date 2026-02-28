@extends('layouts.frontend')

@section('title', 'Crop Planning | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
    <style>
        .feature-list-item-agri li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 12px;
            color: var(--bs-gray-700);
            line-height: 1.6;
        }
        .feature-list-item-agri li::before {
            content: '\f058'; /* fa-check-circle */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: var(--agri-success);
            font-size: 18px;
        }
    </style>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.01));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Crop Planning AI</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-success text-decoration-none">AI Tools</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Crop Planning</li>
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
                            <span class="badge bg-success bg-opacity-10 text-success mb-2 px-3 py-2 fs-6 border border-success border-opacity-25 rounded-pill"><i class="fas fa-calendar-check me-2"></i> Agronomy</span>
                            <h2 class="fw-bold text-dark mb-3">AI-Powered Crop Planning for Maximum Yield</h2>
                            <p class="text-muted" style="line-height: 1.8; font-size: 16px;">
                                Smart crop planning is the foundation of successful farming. Our AI-powered crop planning service helps farmers make informed decisions by analyzing critical field parameters including season, soil type, climate conditions, and water availability.
                            </p>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-list-ul text-primary me-2"></i> Key Features</h4>
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted small" style="line-height: 1.6;">
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Season-based crop recommendations</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Soil type analysis and matching</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Climate-specific selection</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Water availability optimization</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i> Real-time AI processing</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-4 rounded-3 h-100 border">
                                    <h4 class="fw-bold text-dark fs-5 mb-3"><i class="fas fa-seedling text-warning me-2"></i> Why it Matters</h4>
                                    <p class="text-muted mb-0 small" style="line-height: 1.6;">
                                        Proper crop planning can increase farm productivity by 30-40% and reduce resource wastage significantly. Our intelligent system considers multiple factors simultaneously to provide the most accurate recommendations for long-term farm success.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-25">

                        <!-- Tool Form -->
                        <div class="crop-planning-form p-4 rounded-4" style="background: rgba(16, 185, 129, 0.03); border: 2px dashed var(--agri-primary-light);">
                            <div class="text-center mb-4">
                                <div class="d-inline-flex bg-white text-primary p-3 rounded-circle shadow-sm mb-3">
                                    <i class="fas fa-calendar-alt fs-3"></i>
                                </div>
                                <h3 class="fw-bold text-dark fs-4">Generate Your Cultivation Plan</h3>
                                <p class="text-muted mb-0 mx-auto" style="max-width: 600px;">
                                    Enter your field parameters below to instantly generate a comprehensive, actionable cultivation guide for your chosen crop.
                                </p>
                            </div>

                            <form id="cropPlanningForm" class="contact-form">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="p-4 bg-white rounded-3 border shadow-sm border-start border-success border-4">
                                            <label for="cropToGrow" class="form-label fw-bold text-dark fs-5 mb-2">Target Crop</label>
                                            <p class="text-muted small mb-3">Select the main crop you intend to cultivate this season.</p>
                                            <select id="cropToGrow" class="form-agri form-select-lg w-100 bg-light" required>
                                                <option value="" disabled selected>Select a crop...</option>
                                                <option value="wheat">Wheat</option>
                                                <option value="rice">Rice</option>
                                                <option value="cotton">Cotton</option>
                                                <option value="maize">Maize (Corn)</option>
                                                <option value="sugarcane">Sugarcane</option>
                                                <option value="tomato">Tomato</option>
                                                <option value="potato">Potato</option>
                                                <option value="onion">Onion</option>
                                                <option value="chickpea">Chickpea</option>
                                                <option value="soybean">Soybean</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="season" class="form-label fw-bold text-dark small">Growing Season</label>
                                        <select class="form-agri" id="season" name="season" required>
                                            <option value="" disabled selected>Select Season</option>
                                            <option value="spring">Spring (March - May)</option>
                                            <option value="summer">Summer (June - August)</option>
                                            <option value="autumn">Autumn (September - November)</option>
                                            <option value="winter">Winter (December - February)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="soilType" class="form-label fw-bold text-dark small">Soil Texture</label>
                                        <select class="form-agri" id="soilType" name="soilType" required>
                                            <option value="" disabled selected>Select Soil Type</option>
                                            <option value="clay">Clay Soil</option>
                                            <option value="sandy">Sandy Soil</option>
                                            <option value="loamy">Loamy Soil</option>
                                            <option value="silty">Silty Soil</option>
                                            <option value="peaty">Peaty Soil</option>
                                            <option value="chalky">Chalky Soil</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="climate" class="form-label fw-bold text-dark small">Local Climate</label>
                                        <select class="form-agri" id="climate" name="climate" required>
                                            <option value="" disabled selected>Select Climate</option>
                                            <option value="tropical">Tropical</option>
                                            <option value="subtropical">Subtropical</option>
                                            <option value="temperate">Temperate</option>
                                            <option value="arid">Arid/Desert</option>
                                            <option value="mediterranean">Mediterranean</option>
                                            <option value="continental">Continental</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="waterAvailability" class="form-label fw-bold text-dark small">Water Availability</label>
                                        <select class="form-agri" id="waterAvailability" name="waterAvailability" required>
                                            <option value="" disabled selected>Select Water Status</option>
                                            <option value="abundant">Abundant (Regular watering)</option>
                                            <option value="moderate">Moderate (Seasonal)</option>
                                            <option value="limited">Limited (Drought-prone)</option>
                                            <option value="scarce">Scarce (Very dry)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-center mt-5">
                                    <button type="submit" class="btn-agri btn-agri-primary px-5 py-3 fs-5 shadow-sm">
                                        <i class="fas fa-file-invoice me-2"></i> Generate Detailed Crop Plan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Result Area -->
                        <div id="cropRecommendation" class="mt-4" style="display: none;">
                            <div class="card-agri p-4 border-0" style="background: linear-gradient(to right bottom, #ffffff, #f8fcf9); border: 1px solid var(--agri-border) !important;">
                                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <h4 id="recommendationTitle" class="fw-bold text-dark mb-0 m-0">Crop Plan Generated</h4>
                                </div>
                                <div id="recommendationContent"></div>
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
                                <a href="{{ route('crop-recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-seedling text-secondary"></i></div>
                                    <span class="fw-medium">Crop Recommendation</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('crop-planning') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none" style="background: var(--agri-primary-light); color: var(--agri-primary);">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-primary" style="width: 36px; height: 36px;"><i class="fas fa-calendar-alt text-primary"></i></div>
                                    <span class="fw-bold">Crop Planning</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('disease-identification') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-microscope text-secondary"></i></div>
                                    <span class="fw-medium">Disease Identification</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('fertilizer-recommendation') }}" class="d-flex align-items-center p-3 rounded-3 text-decoration-none text-muted" style="transition: all 0.2s;">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;"><i class="fas fa-flask text-secondary"></i></div>
                                    <span class="fw-medium">Fertilizer Recommendation</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-agri p-4 border-0 bg-success text-white position-relative overflow-hidden text-center sticky-top" style="top: 380px;">
                        <div class="position-absolute" style="top: -20px; right: -20px; font-size: 150px; opacity: 0.1; transform: rotate(-15deg);">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="position-relative z-index-1">
                            <div class="bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 64px; height: 64px; font-size: 28px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-white">Need Expert Advice?</h4>
                            <p class="mb-4 text-white text-opacity-75 small">
                                Complex field situation? Call our helpline to get customized planting schedules from agronomy masters.
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
        document.getElementById('cropPlanningForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Get form values
            var crop = document.getElementById('cropToGrow').value;
            var season = document.getElementById('season').value;
            var soilType = document.getElementById('soilType').value;
            var climate = document.getElementById('climate').value;
            var waterAvailability = document.getElementById('waterAvailability').value;

            // Show loading
            var btn = this.querySelector('button[type="submit"]');
            var originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating Plan...';
            btn.disabled = true;

            setTimeout(function() {
                // Generate a detailed grow plan for the selected crop
                var ctx = { season: season, soilType: soilType, climate: climate, waterAvailability: waterAvailability };
                var planHtml = generateGrowPlan(crop, ctx);

                // Display recommendation
                var title = (cropGuides[crop] ? cropGuides[crop].name : crop);
                document.getElementById('recommendationTitle').innerHTML = 'Cultivation Plan: <span class="text-primary">' + title + '</span>';
                document.getElementById('recommendationContent').innerHTML = planHtml;
                
                var box = document.getElementById('cropRecommendation');
                box.style.display = 'block';

                // Scroll to recommendation
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;
            }, 800);
        });

        // --- Data: concise cultivation guides for popular crops ---
        var cropGuides = {
            wheat: {
                name: 'Wheat',
                seasons: ['winter'], // sowing in winter (Rabi)
                climates: ['temperate', 'subtropical'],
                soil: { preferred: ['loamy', 'clay'], ph: '6.0 – 7.5' },
                seedRate: '50–60 kg/acre (125–150 kg/ha)',
                spacing: 'Rows 20–22.5 cm apart; sow 4–5 cm deep',
                fertilizer: [
                    'Basal: 20–25 kg P₂O₅ + 10–15 kg K₂O per acre before sowing',
                    'Nitrogen: 40–50 kg N/acre split: 50% basal + 50% at first irrigation (CRI/tillering)'
                ],
                irrigation: '4–6 irrigations: pre-sowing, CRI (20-25 DAS), booting, milking and grain-filling. Avoid water stress at CRI and heading.',
                pests: 'Aphids, rusts (yellow/brown). Use resistant varieties, timely sowing, and recommended fungicides.',
                harvest: '140–150 days; harvest when grains are hard and straw turns golden; target 12-14% grain moisture.',
                yield: '25–35 maunds/acre (1.0-1.4 t/acre) under good management.',
                icon: 'fas fa-leaf text-success'
            },
            rice: {
                name: 'Rice',
                seasons: ['summer'],
                climates: ['tropical', 'subtropical'],
                soil: { preferred: ['clay', 'loamy'], ph: '5.5 – 7.0' },
                seedRate: '8–10 kg/acre in nursery (transplanted) or 12–15 kg/acre (direct seeded)',
                spacing: 'Transplant 20 x 15 cm, 2–3 seedlings/hill',
                fertilizer: [
                    'Basal: 20–25 kg P₂O₅ + 10–15 kg K₂O/acre before puddling',
                    'Nitrogen: 40–50 kg N/acre split 3 times: 50% basal, 25% tillering, 25% panicle initiation'
                ],
                irrigation: 'Maintain 2–5 cm standing water from transplanting to dough stage; drain before harvest.',
                pests: 'Stem borer, leaf folder, BPH. Use pheromone traps, need-based IPM, and recommended insecticides.',
                harvest: '110–140 days depending on variety; harvest at 20-22% grain moisture.',
                yield: '30–45 maunds/acre with improved practices.',
                icon: 'fas fa-seedling text-success'
            },
            cotton: {
                name: 'Cotton',
                seasons: ['spring', 'summer'],
                climates: ['subtropical', 'tropical'],
                soil: { preferred: ['loamy', 'clay'], ph: '6.0 – 8.0' },
                seedRate: '3–4 kg/acre (delinted/treated)',
                spacing: '75 cm rows, 20–30 cm plant spacing',
                fertilizer: [
                    'Basal: 15–20 kg P₂O₅ + 10–15 kg K₂O/acre',
                    'Nitrogen: 35–45 kg N/acre in 2-3 splits from squaring to boll formation'
                ],
                irrigation: 'First at 35–40 DAS, then 10–12 day interval; avoid waterlogging at flowering/boll-set.',
                pests: 'Pink bollworm, whitefly, jassid. Early sowing, pheromone traps, refuge, and IPM essential.',
                harvest: '150–170 days; pick when bolls are fully open and dry.',
                yield: '18–25 maunds/acre seed-cotton in good fields.',
                icon: 'fas fa-tree text-success'
            },
            maize: {
                name: 'Maize (Corn)',
                seasons: ['spring', 'autumn'],
                climates: ['subtropical', 'temperate', 'tropical'],
                soil: { preferred: ['loamy', 'sandy'], ph: '5.8 – 7.2' },
                seedRate: '8–10 kg/acre',
                spacing: '60–75 cm rows, 20–25 cm plants; depth 4–5 cm',
                fertilizer: [
                    'Basal: 20–25 kg P₂O₅ + 10–15 kg K₂O/acre',
                    'Nitrogen: 35–45 kg N/acre: 25% basal, 50% knee-high, 25% tasseling'
                ],
                irrigation: 'Critical at knee-high, tasseling/silking and grain-filling; maintain moist, not waterlogged soil.',
                pests: 'Fall armyworm, stem borer; scout weekly, use IPM and recommended controls.',
                harvest: '90–110 days; harvest cobs at 20-25% grain moisture for shelling.',
                yield: '25–40 maunds/acre with hybrids.',
                icon: 'fab fa-pagelines text-success'
            },
            sugarcane: {
                name: 'Sugarcane',
                seasons: ['spring'],
                climates: ['tropical', 'subtropical'],
                soil: { preferred: ['loamy', 'clay'], ph: '6.0 – 7.5' },
                seedRate: '12,000–15,000 three-bud setts/acre',
                spacing: '90–120 cm rows; 2-bud setts placed end-to-end at 5–7 cm depth',
                fertilizer: [
                    'Basal: 20–25 kg P₂O₅ + 20 kg K₂O/acre',
                    'Nitrogen: 60–80 kg N/acre in 3 splits up to grand growth phase'
                ],
                irrigation: 'Heavy initial irrigation; 7–10 day interval in summer, 15-20 day in winter; excellent drainage required.',
                pests: 'Early/Late shoot borer, pyrilla; use healthy seed, trash mulching, and IPM.',
                harvest: '10–12 months; harvest when brix/tonnage peak and tops dry.',
                yield: '30–45 tons/acre in well-managed fields.',
                icon: 'fas fa-seedling text-success'
            },
            tomato: {
                name: 'Tomato',
                seasons: ['autumn', 'winter', 'spring'],
                climates: ['subtropical', 'temperate', 'tropical'],
                soil: { preferred: ['loamy', 'sandy'], ph: '6.0 – 7.0' },
                seedRate: '30–40 g/acre nursery seed; transplant healthy seedlings',
                spacing: '60 x 45 cm (stake for indeterminate types)',
                fertilizer: [
                    'Basal: 15 kg P₂O₅ + 10 kg K₂O/acre',
                    'Nitrogen: 25–30 kg N/acre split 3-4 times from transplanting to fruit set'
                ],
                irrigation: 'Keep evenly moist; avoid wetting foliage; mulch to reduce blossom-end rot.',
                pests: 'Fruit borer, whitefly; use traps, pruning, and IPM.',
                harvest: '60–80 days after transplanting; pick at breaker stage for transport.',
                yield: '8–12 tons/acre (variety-dependent).',
                icon: 'fas fa-apple-alt text-danger'
            },
            potato: {
                name: 'Potato',
                seasons: ['autumn', 'winter'],
                climates: ['temperate', 'subtropical'],
                soil: { preferred: ['loamy', 'sandy'], ph: '5.5 – 6.8' },
                seedRate: '8–10 quintals/acre (30-40 mm graded seed tubers)',
                spacing: 'Rows 60–67.5 cm; plants 20–25 cm',
                fertilizer: [
                    'Basal: 15–20 kg P₂O₅ + 15–20 kg K₂O/acre',
                    'Nitrogen: 25–30 kg N/acre split 2-3 times, last before tuber bulking'
                ],
                irrigation: 'Light frequent irrigations; avoid waterlogging to reduce late blight.',
                pests: 'Cutworm, aphids, late blight; ridge and spray preventively in conducive weather.',
                harvest: '90–110 days; haulms yellow and skins set; cure before storage.',
                yield: '70–100 bags/acre depending on variety.',
                icon: 'fas fa-leaf text-warning'
            },
            onion: {
                name: 'Onion',
                seasons: ['autumn', 'winter'],
                climates: ['subtropical', 'temperate'],
                soil: { preferred: ['loamy', 'sandy'], ph: '6.0 – 7.5' },
                seedRate: '1–1.5 kg/acre nursery seed',
                spacing: '15 x 10 cm; shallow transplanting',
                fertilizer: [
                    'Basal: 12–15 kg P₂O₅ + 10–12 kg K₂O/acre',
                    'Nitrogen: 20–25 kg N/acre split up to bulb formation'
                ],
                irrigation: 'Frequent light irrigation; reduce before harvest for curing.',
                pests: 'Thrips, purple blotch; keep fields clean and use IPM.',
                harvest: '90–120 days; tops fall and necks thin; cure bulbs well.',
                yield: '6–10 tons/acre with good management.',
                icon: 'fas fa-circle text-warning'
            },
            chickpea: {
                name: 'Chickpea (Gram)',
                seasons: ['winter'],
                climates: ['subtropical', 'temperate'],
                soil: { preferred: ['loamy', 'sandy'], ph: '6.0 – 7.5' },
                seedRate: '30–35 kg/acre (desi types)',
                spacing: '45 cm rows, 10–12 cm plants',
                fertilizer: [
                    'Basal: 10–12 kg P₂O₅/acre; inoculate seed with Rhizobium',
                    'Nitrogen: 5–7 kg N/acre starter only'
                ],
                irrigation: 'Usually rain-fed; one irrigation at flowering/pod fill boosts yield.',
                pests: 'Pod borer; install pheromone traps and spray need-based.',
                harvest: '110–130 days; pods turn brown; avoid shattering.',
                yield: '10–15 maunds/acre in rain-fed; higher with irrigation.',
                icon: 'fas fa-seedling text-warning'
            },
            soybean: {
                name: 'Soybean',
                seasons: ['summer'],
                climates: ['subtropical', 'temperate', 'tropical'],
                soil: { preferred: ['loamy'], ph: '6.0 – 7.0' },
                seedRate: '25–30 kg/acre',
                spacing: '45 cm rows, 5–7 cm plants; depth 3-4 cm',
                fertilizer: [
                    'Basal: 10–12 kg P₂O₅/acre; inoculate with Bradyrhizobium',
                    'Nitrogen: Starter 4-6 kg N/acre only'
                ],
                irrigation: 'Critical at flowering and pod filling; avoid waterlogging.',
                pests: 'Girdle beetle, caterpillars; weekly scouting and IPM.',
                harvest: '90–110 days; harvest when 80% pods turn brown and seeds rattle.',
                yield: '12–20 maunds/acre depending on season.',
                icon: 'fas fa-seedling text-success'
            }
        };

        function titleCase(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }

        function generateGrowPlan(cropKey, ctx) {
            var g = cropGuides[cropKey];
            if (!g) {
                return '<div class="alert alert-danger"><p class="m-0">Sorry, we don\'t yet have a detailed guide for this crop.</p></div>';
            }

            // Suitability checks against user context
            var matches = 0;
            var notes = [];
            if (g.seasons.indexOf(ctx.season) > -1) { matches++; } else { notes.push('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Season mismatch: prefers ' + g.seasons.map(titleCase).join(', ')); }
            if (g.soil.preferred.indexOf(ctx.soilType) > -1) { matches++; } else { notes.push('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Soil mismatch: prefers ' + g.soil.preferred.map(titleCase).join(' / ')); }
            if (g.climates.indexOf(ctx.climate) > -1) { matches++; } else { notes.push('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Climate mismatch: suited to ' + g.climates.map(titleCase).join(', ')); }
            if (ctx.waterAvailability !== 'scarce') { matches++; } else { notes.push('<i class="fas fa-exclamation-triangle text-warning me-1"></i> Water scarce: ensure timely irrigation.'); }

            var suitabilityScore = (matches / 4) * 100;
            var suitabilityClass = suitabilityScore >= 75 ? 'success' : (suitabilityScore >= 50 ? 'warning' : 'danger');

            var html = '<div class="recommendation-details">';
            
            // Score Header
            html += '<div class="bg-light p-4 rounded-3 mb-4 border d-flex align-items-center justify-content-between">';
            html += '<div><h5 class="fw-bold text-dark m-0"><i class="fas fa-chart-pie text-muted me-2"></i> Artificial Intelligence Diagnosis</h5></div>';
            html += '<div>';
            html += '<span class="badge bg-' + suitabilityClass + ' fs-6 px-3 py-2 rounded-pill shadow-sm">Fit Score: ' + suitabilityScore + '% (' + matches + '/4 Match)</span>';
            html += '</div></div>';

            // Warning notes if any
            if (notes.length) {
                html += '<div class="alert alert-warning border-warning border-opacity-50 py-2 px-3 mb-4">';
                html += '<ul class="list-unstyled m-0 text-dark small d-flex flex-column gap-1">';
                notes.forEach(function(n) { html += '<li>' + n + '</li>'; });
                html += '</ul></div>';
            }

            // Layout Two Columns for Guide
            html += '<div class="row g-4">';
            
            // Left Column: Soil & Prep
            html += '<div class="col-md-6">';
            
            html += '<div class="mb-4">';
            html += '<h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-tractor text-secondary me-2"></i> Land Prep & Sowing</h6>';
            html += '<p class="text-muted small mb-2"><strong>Sowing Seasons:</strong> ' + g.seasons.map(titleCase).join(', ') + '</p>';
            html += '<p class="text-muted small mb-2"><strong>Preferred Soils:</strong> ' + g.soil.preferred.map(titleCase).join(' / ') + ' <span class="badge bg-light text-dark ms-1">pH ' + g.soil.ph + '</span></p>';
            html += '<p class="text-muted small mb-2"><strong>Seed Rate:</strong> ' + g.seedRate + '</p>';
            html += '<p class="text-muted small m-0"><strong>Spacing:</strong> ' + g.spacing + '</p>';
            html += '</div>';

            html += '<div class="mb-4">';
            html += '<h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-water text-primary me-2"></i> Irrigation Management</h6>';
            html += '<p class="text-muted small m-0" style="line-height:1.6;">' + g.irrigation + '</p>';
            html += '</div>';
            
            html += '<div class="mb-4">';
            html += '<h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-box-open text-warning me-2"></i> Expected Return</h6>';
            html += '<p class="text-dark fw-bold mb-1">' + g.yield + '</p>';
            html += '<p class="text-muted small m-0"><i class="fas fa-clock me-1"></i> ' + g.harvest + '</p>';
            html += '</div>';

            html += '</div>';

            // Right Column: Nutrition & Pest
            html += '<div class="col-md-6">';
            html += '<div class="card bg-success bg-opacity-10 border-success border-opacity-25 pb-0 mb-4 h-100 shadow-none">';
            html += '<div class="card-body">';
            html += '<h6 class="fw-bold text-success border-bottom border-success border-opacity-25 pb-2 mb-3"><i class="fas fa-flask me-2"></i> Fertilizer Schedule</h6>';
            html += '<ul class="feature-list-item-agri small m-0">';
            g.fertilizer.forEach(function (line) { html += '<li>' + line + '</li>'; });
            html += '</ul>';
            html += '</div></div>';
            html += '</div>';

            // Bottom Full-width
            html += '<div class="col-12">';
            html += '<div class="bg-light p-3 rounded-3 border mt-2">';
            html += '<h6 class="fw-bold text-danger mb-2"><i class="fas fa-bug border-danger me-2"></i> Pest & Disease Vigilance</h6>';
            html += '<p class="text-muted small m-0" style="line-height:1.6;">' + g.pests + '</p>';
            html += '</div></div>';

            html += '</div>'; // close row

            html += '</div>';

            return html;
        }
    </script>
@endsection
