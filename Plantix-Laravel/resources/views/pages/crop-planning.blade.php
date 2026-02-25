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
                    <h1>Crop Planning</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Crop Planning</li>
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
                            <img src="{{ asset('assets/img/field.jpg') }}" alt="Thumb">
                        </div>
                        <h2>AI-Powered Crop Planning for Maximum Yield</h2>
                        <p>
                            Smart crop planning is the foundation of successful farming. Our AI-powered crop planning
                            service helps farmers make informed decisions by analyzing critical field parameters
                            including season, soil type, climate conditions, and water availability. By leveraging
                            advanced machine learning algorithms and agricultural expertise, we provide personalized
                            crop recommendations that maximize yield while optimizing resource usage.
                        </p>
                        <div class="features mt-40 mt-xs-30 mb-30 mb-xs-20">
                            <div class="row">
                                <div class="col-xl-5 col-lg-12 col-md-6">
                                    <div class="content">
                                        <h3>Key Features</h3>
                                        <ul class="feature-list-item">
                                            <li>Season-based crop recommendations</li>
                                            <li>Soil type analysis and matching</li>
                                            <li>Climate-specific crop selection</li>
                                            <li>Water availability optimization</li>
                                            <li>Alternative crop suggestions</li>
                                            <li>Real-time AI recommendations</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xl-7 col-lg-12 col-md-6 mt-xs-30">
                                    <div class="content">
                                        <h3>Why Crop Planning Matters</h3>
                                        <p>
                                            Proper crop planning can increase farm productivity by 30-40% and reduce
                                            resource wastage significantly. By selecting the right crop for your
                                            specific field conditions, you can ensure optimal growth, reduce pest and
                                            disease risks, and maximize your return on investment. Our intelligent
                                            system considers multiple factors simultaneously to provide the most
                                            accurate recommendations.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <blockquote>The right crop at the right time in the right soil can transform your farming
                            success.</blockquote>
                        <h2>How Our Crop Planning Works</h2>
                        <p>
                            Our crop planning tool uses a comprehensive database of crop characteristics, regional
                            climate data, and soil science to match the perfect crop to your field conditions. Simply
                            input your season, soil type, climate zone, and water availability, and our AI engine
                            analyzes thousands of data points to recommend the most suitable crops. The system considers
                            crop rotation benefits, market demand, and cultivation requirements to ensure practical and
                            profitable recommendations for your farm.
                        </p>

                        <div class="crop-planning-form mt-40">
                            <h2 class="mb-25">Plan Your Crop</h2>
                            <p class="mb-30">Enter your field parameters below to get AI-powered crop recommendations
                            </p>
                            <form id="cropPlanningForm" class="contact-form">
                                <!-- Choose a specific crop to get a full grow plan -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="cropToGrow">Crop to Grow</label>
                                            <select id="cropToGrow" class="form-control" required data-label="Crop to grow">
                                                <option value="">Select a crop...</option>
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
                                            <small class="form-text text-muted">Pick the crop you want to grow to get a
                                                full cultivation plan.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="season" class="mb-2"><strong>Season</strong></label>
                                            <select class="form-control" id="season" name="season" required data-label="Season">
                                                <option value="">Select Season</option>
                                                <option value="spring">Spring (March - May)</option>
                                                <option value="summer">Summer (June - August)</option>
                                                <option value="autumn">Autumn (September - November)</option>
                                                <option value="winter">Winter (December - February)</option>
                                            </select>
                                            <span class="alert-error"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="soilType" class="mb-2"><strong>Soil Type</strong></label>
                                            <select class="form-control" id="soilType" name="soilType" required data-label="Soil type">
                                                <option value="">Select Soil Type</option>
                                                <option value="clay">Clay Soil</option>
                                                <option value="sandy">Sandy Soil</option>
                                                <option value="loamy">Loamy Soil</option>
                                                <option value="silty">Silty Soil</option>
                                                <option value="peaty">Peaty Soil</option>
                                                <option value="chalky">Chalky Soil</option>
                                            </select>
                                            <span class="alert-error"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="climate" class="mb-2"><strong>Climate</strong></label>
                                            <select class="form-control" id="climate" name="climate" required data-label="Climate">
                                                <option value="">Select Climate</option>
                                                <option value="tropical">Tropical</option>
                                                <option value="subtropical">Subtropical</option>
                                                <option value="temperate">Temperate</option>
                                                <option value="arid">Arid/Desert</option>
                                                <option value="mediterranean">Mediterranean</option>
                                                <option value="continental">Continental</option>
                                            </select>
                                            <span class="alert-error"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="waterAvailability" class="mb-2"><strong>Water
                                                    Availability</strong></label>
                                            <select class="form-control" id="waterAvailability" name="waterAvailability"
                                                required data-label="Water availability">
                                                <option value="">Select Water Availability</option>
                                                <option value="abundant">Abundant (Regular rainfall/irrigation)</option>
                                                <option value="moderate">Moderate (Seasonal rainfall)</option>
                                                <option value="limited">Limited (Drought-prone)</option>
                                                <option value="scarce">Scarce (Very dry conditions)</option>
                                            </select>
                                            <span class="alert-error"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <button type="submit" name="submit" class="btn btn-theme mt-3">
                                            <i class="fas fa-seedling"></i> Get Crop Planning
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Recommendation Result -->
                            <div id="cropRecommendation" class="alert alert-success mt-40 hidden">
                                <h4 id="recommendationTitle" class="mb-3"><i class="fas fa-check-circle"></i> Crop Plan
                                </h4>
                                <div id="recommendationContent"></div>
                            </div>
                        </div>

                        <script>
                            document.getElementById('cropPlanningForm').addEventListener('submit', function (e) {
                                e.preventDefault();

                                // Get form values
                                var crop = document.getElementById('cropToGrow').value;
                                var season = document.getElementById('season').value;
                                var soilType = document.getElementById('soilType').value;
                                var climate = document.getElementById('climate').value;
                                var waterAvailability = document.getElementById('waterAvailability').value;

                                // Validate form
                                if (!crop || !season || !soilType || !climate || !waterAvailability) {
                                    alert('Please fill in all fields and select a crop');
                                    return;
                                }

                                // Generate a detailed grow plan for the selected crop
                                var ctx = { season: season, soilType: soilType, climate: climate, waterAvailability: waterAvailability };
                                var planHtml = generateGrowPlan(crop, ctx);

                                // Display recommendation
                                var title = (cropGuides[crop] ? cropGuides[crop].name : crop);
                                document.getElementById('recommendationTitle').innerHTML = '<i class="fas fa-check-circle"></i> Crop Plan: ' + title;
                                document.getElementById('recommendationContent').innerHTML = planHtml;
                                var box = document.getElementById('cropRecommendation');
                                box.classList.remove('hidden');
                                box.style.display = 'block';

                                // Scroll to recommendation
                                document.getElementById('cropRecommendation').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
                                    irrigation: '4–6 irrigations: pre‑sowing, CRI (20–25 DAS), booting, milking and grain‑filling. Avoid water stress at CRI and heading.',
                                    pests: 'Aphids, rusts (yellow/brown). Use resistant varieties, timely sowing, and recommended fungicides if needed.',
                                    harvest: '140–150 days; harvest when grains are hard and straw turns golden; target 12–14% grain moisture.',
                                    yield: '25–35 maunds/acre (1.0–1.4 t/acre) under good management.'
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
                                    pests: 'Stem borer, leaf folder, BPH. Use pheromone traps, need‑based IPM, and recommended insecticides.',
                                    harvest: '110–140 days depending on variety; harvest at 20–22% grain moisture.',
                                    yield: '30–45 maunds/acre with improved practices.'
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
                                        'Nitrogen: 35–45 kg N/acre in 2–3 splits from squaring to boll formation'
                                    ],
                                    irrigation: 'First at 35–40 DAS, then 10–12 day interval; avoid waterlogging at flowering/boll‑set.',
                                    pests: 'Pink bollworm, whitefly, jassid. Early sowing, pheromone traps, refuge, and IPM essential.',
                                    harvest: '150–170 days; pick when bolls are fully open and dry.',
                                    yield: '18–25 maunds/acre seed‑cotton in good fields.'
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
                                        'Nitrogen: 35–45 kg N/acre: 25% basal, 50% knee‑high, 25% tasseling'
                                    ],
                                    irrigation: 'Critical at knee‑high, tasseling/silking and grain‑filling; maintain moist, not waterlogged soil.',
                                    pests: 'Fall armyworm, stem borer; scout weekly, use IPM and recommended controls.',
                                    harvest: '90–110 days; harvest cobs at 20–25% grain moisture for shelling.',
                                    yield: '25–40 maunds/acre with hybrids.'
                                },
                                sugarcane: {
                                    name: 'Sugarcane',
                                    seasons: ['spring'],
                                    climates: ['tropical', 'subtropical'],
                                    soil: { preferred: ['loamy', 'clay'], ph: '6.0 – 7.5' },
                                    seedRate: '12,000–15,000 three‑bud setts/acre',
                                    spacing: '90–120 cm rows; 2‑bud setts placed end‑to‑end at 5–7 cm depth',
                                    fertilizer: [
                                        'Basal: 20–25 kg P₂O₅ + 20 kg K₂O/acre',
                                        'Nitrogen: 60–80 kg N/acre in 3 splits up to grand growth phase'
                                    ],
                                    irrigation: 'Heavy initial irrigation; 7–10 day interval in summer, 15–20 day in winter; excellent drainage required.',
                                    pests: 'Early/Late shoot borer, pyrilla; use healthy seed, trash mulching, and IPM.',
                                    harvest: '10–12 months; harvest when brix/tonnage peak and tops dry.',
                                    yield: '30–45 tons/acre in well‑managed fields.'
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
                                        'Nitrogen: 25–30 kg N/acre split 3–4 times from transplanting to fruit set'
                                    ],
                                    irrigation: 'Keep evenly moist; avoid wetting foliage; mulch to reduce blossom‑end rot.',
                                    pests: 'Fruit borer, whitefly; use traps, pruning, and IPM.',
                                    harvest: '60–80 days after transplanting; pick at breaker stage for transport.',
                                    yield: '8–12 tons/acre (variety‑dependent).'
                                },
                                potato: {
                                    name: 'Potato',
                                    seasons: ['autumn', 'winter'],
                                    climates: ['temperate', 'subtropical'],
                                    soil: { preferred: ['loamy', 'sandy'], ph: '5.5 – 6.8' },
                                    seedRate: '8–10 quintals/acre (30–40 mm graded seed tubers)',
                                    spacing: 'Rows 60–67.5 cm; plants 20–25 cm',
                                    fertilizer: [
                                        'Basal: 15–20 kg P₂O₅ + 15–20 kg K₂O/acre',
                                        'Nitrogen: 25–30 kg N/acre split 2–3 times, last before tuber bulking'
                                    ],
                                    irrigation: 'Light frequent irrigations; avoid waterlogging to reduce late blight.',
                                    pests: 'Cutworm, aphids, late blight; ridge and spray preventively in conducive weather.',
                                    harvest: '90–110 days; haulms yellow and skins set; cure before storage.',
                                    yield: '70–100 bags/acre depending on variety.'
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
                                    yield: '6–10 tons/acre with good management.'
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
                                    irrigation: 'Usually rain‑fed; one irrigation at flowering/pod fill boosts yield.',
                                    pests: 'Pod borer; install pheromone traps and spray need‑based.',
                                    harvest: '110–130 days; pods turn brown; avoid shattering.',
                                    yield: '10–15 maunds/acre in rain‑fed; higher with irrigation.'
                                },
                                soybean: {
                                    name: 'Soybean',
                                    seasons: ['summer'],
                                    climates: ['subtropical', 'temperate', 'tropical'],
                                    soil: { preferred: ['loamy'], ph: '6.0 – 7.0' },
                                    seedRate: '25–30 kg/acre',
                                    spacing: '45 cm rows, 5–7 cm plants; depth 3–4 cm',
                                    fertilizer: [
                                        'Basal: 10–12 kg P₂O₅/acre; inoculate with Bradyrhizobium',
                                        'Nitrogen: Starter 4–6 kg N/acre only'
                                    ],
                                    irrigation: 'Critical at flowering and pod filling; avoid waterlogging.',
                                    pests: 'Girdle beetle, caterpillars; weekly scouting and IPM.',
                                    harvest: '90–110 days; harvest when 80% pods turn brown and seeds rattle.',
                                    yield: '12–20 maunds/acre depending on season.'
                                }
                            };

                            function titleCase(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }

                            function generateGrowPlan(cropKey, ctx) {
                                var g = cropGuides[cropKey];
                                if (!g) {
                                    return '<p>Sorry, I don\'t yet have a guide for this crop.</p>';
                                }

                                // Suitability checks against user context
                                var matches = 0;
                                var notes = [];
                                if (g.seasons.indexOf(ctx.season) > -1) { matches++; } else { notes.push('Season caution: preferred ' + g.seasons.map(titleCase).join(', ')); }
                                if (g.soil.preferred.indexOf(ctx.soilType) > -1) { matches++; } else { notes.push('Soil caution: prefers ' + g.soil.preferred.map(titleCase).join(' / ')); }
                                if (g.climates.indexOf(ctx.climate) > -1) { matches++; } else { notes.push('Climate caution: suited to ' + g.climates.map(titleCase).join(', ')); }
                                if (ctx.waterAvailability !== 'scarce') { matches++; } else { notes.push('Water caution: ensure timely irrigation for stable yields.'); }

                                var suitability = '<span class="badge bg-success" style="background:#28a745;color:#fff;padding:6px 10px;border-radius:12px;">Fit score: ' + matches + '/4</span>';
                                if (notes.length) {
                                    suitability += ' <small class="text-muted">(' + notes.join('; ') + ')</small>';
                                }

                                var html = '';
                                html += '<div class="recommendation-details">';
                                html += '<p class="mb-2"><strong>Your field parameters</strong></p>';
                                html += '<ul class="feature-list-item mb-3">';
                                html += '<li><strong>Season:</strong> ' + titleCase(ctx.season) + '</li>';
                                html += '<li><strong>Soil Type:</strong> ' + titleCase(ctx.soilType) + ' (preferred pH ' + g.soil.ph + ')</li>';
                                html += '<li><strong>Climate:</strong> ' + titleCase(ctx.climate) + '</li>';
                                html += '<li><strong>Water Availability:</strong> ' + titleCase(ctx.waterAvailability) + '</li>';
                                html += '</ul>';

                                html += '<div class="alert alert-info mb-3"><strong>Suitability:</strong> ' + suitability + '</div>';

                                html += '<h5 class="mt-3">Sowing window</h5>';
                                html += '<p class="mb-2">Recommended seasons: <strong>' + g.seasons.map(titleCase).join(', ') + '</strong>.</p>';

                                html += '<h5 class="mt-3">Seed rate & treatment</h5>';
                                html += '<ul class="feature-list-item">';
                                html += '<li>Seed rate: ' + g.seedRate + '</li>';
                                html += '<li>Treat seed with a broad‑spectrum fungicide/inoculant as per label where applicable.</li>';
                                html += '</ul>';

                                html += '<h5 class="mt-3">Land preparation & spacing</h5>';
                                html += '<ul class="feature-list-item">';
                                html += '<li>Preferred soils: ' + g.soil.preferred.map(titleCase).join(' / ') + '; pH ' + g.soil.ph + '.</li>';
                                html += '<li>Spacing: ' + g.spacing + '.</li>';
                                html += '</ul>';

                                html += '<h5 class="mt-3">Fertilizer schedule (per acre)</h5>';
                                html += '<ul class="feature-list-item">';
                                g.fertilizer.forEach(function (line) { html += '<li>' + line + '</li>'; });
                                html += '</ul>';

                                html += '<h5 class="mt-3">Irrigation</h5>';
                                html += '<p>' + g.irrigation + '</p>';

                                html += '<h5 class="mt-3">Pest & disease watch</h5>';
                                html += '<p>' + g.pests + '</p>';

                                html += '<h5 class="mt-3">Harvest & yield</h5>';
                                html += '<p>' + g.harvest + ' <br><strong>Expected yield:</strong> ' + g.yield + '</p>';

                                html += '<p class="mt-3"><small><em>Note: Always verify doses with local extension advisories and your soil test. Adjust irrigation/fertilizer to variety and weather.</em></small></p>';
                                html += '</div>';

                                return html;
                            }
                        </script>
                    </div>

                    <div class="col-xl-4 col-lg-5 mt-md-100 mt-xs-50 services-sidebar">
                        <!-- Single Widget -->
                        <div class="single-widget services-list-widget">
                            <div class="content">
                                <ul>
                                    <li><a href="{{ route('crop-recommendation') }}">Crop Recommendation</a></li>
                                    <li class="current-item"><a href="{{ route('crop-planning') }}">Crop Planning</a></li>
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
                                <!-- Removed deleted file link: Contact Us button -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End Services Details Area -->
@endsection

