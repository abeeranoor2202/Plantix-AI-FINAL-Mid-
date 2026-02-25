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
    <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light"
        style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Fertilizer Recommendation</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="active">Fertilizer Recommendation</li>
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
                            <img src="{{ asset('assets/img/fertilizer 3.png') }}" alt="Thumb">
                        </div>
                        <h2>Fertilizer Recommendation & Crop Nutrition</h2>
                        <p>
                            Use this quick fertilizer advisor to get practical guidance on what to sow and how to
                            balance nutrients for healthy crops. Enter your field conditions and soil test values (N, P,
                            K) and receive a demo recommendation for a suitable crop plus simple fertilizer suggestions
                            to correct nutrient deficits. This is a client-side planning tool — for production use
                            connect to a verified soil-lab analysis or agronomic model.
                        </p>
                        <div class="features mt-40 mt-xs-30 mb-30 mb-xs-20">
                            <div class="row">
                                <div class="col-xl-5 col-lg-12 col-md-6">
                                    <div class="content">
                                        <h3>Services offered</h3>
                                        <ul class="feature-list-item">
                                            <li>Agriculture Consulting</li>
                                            <li>Custom farming rules</li>
                                            <li>Real-time rate shopping</li>
                                            <li>100 freight shipments / month</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xl-7 col-lg-12 col-md-6 mt-xs-30">
                                    <div class="content">
                                        <h3>The Challange</h3>
                                        <p>
                                            Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus
                                            saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.
                                            Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis
                                            voluptatibus maiores.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <blockquote>Smart nutrition decisions start with understanding your soil and crop needs.
                        </blockquote>
                        <h2>How our fertilizer recommendation works</h2>
                        <p>
                            Enter your field conditions (temperature, humidity, soil moisture), choose soil type and
                            crop type, and add your soil test values for Nitrogen (N), Phosphorus (P), and Potassium
                            (K). Our demo engine scores crops that fit your climate and moisture profile, then
                            highlights nutrient gaps so you can balance N‑P‑K before sowing. You’ll get a suggested crop
                            to sow, plus simple fertilizer tips to correct deficits.
                        </p>
                        <ul class="feature-list-item">
                            <li>Suggested crop to sow with 2–3 alternatives</li>
                            <li>Deficit-based N‑P‑K guidance (e.g., Urea, SSP, MOP)</li>
                            <li>Light vs. heavy top‑up based on distance from targets</li>
                            <li>Client‑side demo — connect to lab data/models for production</li>
                        </ul>

                        <div class="common-faq mt-40">
                            <h2 class="mb-25">Fertilizer & Crop Suggestion</h2>
                            <p>Enter field and soil parameters below to get a quick recommendation on what to sow and a
                                simple fertilizer suggestion. This is a demo (client-side) helper — replace with a
                                server-side model for production.</p>

                            <form id="fertilizerForm" class="mt-30">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cropType" class="form-label">Crop Type</label>
                                        <select id="cropType" class="form-control" data-label="Crop type">
                                            <option value="rice">Rice</option>
                                            <option value="wheat">Wheat</option>
                                            <option value="maize">Maize</option>
                                            <option value="soybean">Soybean</option>
                                            <option value="cotton">Cotton</option>
                                            <option value="potato">Potato</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="soilType" class="form-label">Soil Type</label>
                                        <select id="soilType" class="form-control" data-label="Soil type">
                                            <option value="loamy">Loamy</option>
                                            <option value="sandy">Sandy</option>
                                            <option value="clay">Clay</option>
                                            <option value="silty">Silty</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="temperature" class="form-label">Temperature (°C)</label>
                                        <input type="number" step="0.1" id="temperature" class="form-control"
                                            placeholder="e.g. 25" data-label="Temperature (°C)">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="humidity" class="form-label">Humidity (%)</label>
                                        <input type="number" step="0.1" id="humidity" class="form-control"
                                            placeholder="e.g. 60" data-label="Humidity (%)">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="moisture" class="form-label">Soil Moisture (%)</label>
                                        <input type="number" step="0.1" id="moisture" class="form-control"
                                            placeholder="e.g. 30" data-label="Soil moisture (%)">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="nitrogen" class="form-label">Nitrogen (N) ppm</label>
                                        <input type="number" step="1" id="nitrogen" class="form-control"
                                            placeholder="e.g. 20" data-label="Nitrogen (N) ppm">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phosphorus" class="form-label">Phosphorus (P) ppm</label>
                                        <input type="number" step="1" id="phosphorus" class="form-control"
                                            placeholder="e.g. 15" data-label="Phosphorus (P) ppm">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="potassium" class="form-label">Potassium (K) ppm</label>
                                        <input type="number" step="1" id="potassium" class="form-control"
                                            placeholder="e.g. 40" data-label="Potassium (K) ppm">
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-primary">Get Recommendation</button>
                                        <button type="button" id="resetBtn" class="btn btn-light ms-2">Reset</button>
                                    </div>
                                </div>
                            </form>

                            <div id="fertResult" class="alert alert-info mt-30" style="display:none;"></div>

                            <script>
                                (function () {
                                    function suggestFertilizer(n, p, k, crop) {
                                        // Simple deficit-based suggestion (demo)
                                        var rec = [];
                                        var target = { "rice": { N: 40, P: 20, K: 40 }, "wheat": { N: 50, P: 25, K: 30 }, "maize": { N: 60, P: 30, K: 40 }, "soybean": { N: 20, P: 20, K: 30 }, "cotton": { N: 50, P: 25, K: 40 }, "potato": { N: 80, P: 50, K: 150 } }[crop] || { N: 40, P: 20, K: 40 };
                                        var dn = target.N - n; var dp = target.P - p; var dk = target.K - k;
                                        if (dn > 10) rec.push('Apply nitrogen-rich fertilizer (e.g., Urea) approx ' + Math.max(0, Math.round(dn)) + ' kg/ha equivalent');
                                        else if (dn > 0) rec.push('Light nitrogen dressing (~' + Math.round(dn) + ' kg/ha equivalent)');
                                        else rec.push('Nitrogen level adequate — avoid extra N');

                                        if (dp > 5) rec.push('Apply phosphorus fertilizer (e.g., Single Super Phosphate) approx ' + Math.max(0, Math.round(dp)) + ' kg/ha equivalent');
                                        else if (dp > 0) rec.push('Small phosphorus top-up (~' + Math.round(dp) + ' kg/ha equivalent)');
                                        else rec.push('Phosphorus level adequate');

                                        if (dk > 10) rec.push('Apply potassium fertilizer (e.g., Muriate of Potash) approx ' + Math.max(0, Math.round(dk)) + ' kg/ha equivalent');
                                        else if (dk > 0) rec.push('Light potassium top-up (~' + Math.round(dk) + ' kg/ha equivalent)');
                                        else rec.push('Potassium level adequate');

                                        return rec;
                                    }

                                    function chooseCrop(params) {
                                        // Very simple scoring demo: prefer crop matching temperature and moisture ranges
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
                                            // temperature match
                                            if (t >= cfg.t[0] && t <= cfg.t[1]) cfg.score += 2;
                                            else cfg.score -= 1;
                                            // moisture match
                                            if (m >= cfg.m[0] && m <= cfg.m[1]) cfg.score += 2;
                                            else cfg.score -= 1;
                                            // soil type and crop preference (small bonus)
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

                                        var ranked = chooseCrop(params);
                                        var primary = ranked[0].crop;
                                        var alternatives = ranked.slice(1, 4).map(function (r) { return r.crop; });

                                        var fert = suggestFertilizer(params.nitrogen, params.phosphorus, params.potassium, primary);

                                        var html = '<h4>Recommendation</h4>';
                                        html += '<p><strong>Suggested crop to sow:</strong> ' + primary.charAt(0).toUpperCase() + primary.slice(1) + '.</p>';
                                        html += '<p><strong>Why:</strong> Matched temperature/moisture profile and your preference.</p>';
                                        html += '<p><strong>Alternative crops:</strong> ' + alternatives.map(function (a) { return a.charAt(0).toUpperCase() + a.slice(1) }).join(', ') + '.</p>';
                                        html += '<h5>Fertilizer suggestions</h5><ul>' + fert.map(function (f) { return '<li>' + f + '</li>'; }).join('') + '</ul>';

                                        html += '<p class="small text-muted">Note: this is a demo suggestion for planning purposes only.</p>';

                                        result.innerHTML = html;
                                        result.style.display = 'block';
                                        result.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        // mark todo 2 completed in the managed list (we'll update outside)
                                    });

                                    document.getElementById('resetBtn').addEventListener('click', function () {
                                        form.reset();
                                        result.style.display = 'none';
                                        result.innerHTML = '';
                                    });
                                })();
                            </script>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5 mt-md-100 mt-xs-50 services-sidebar">
                        <!-- Single Widget -->
                        <div class="single-widget services-list-widget">
                            <div class="content">
                                <ul>
                                    <li><a href="{{ route('crop-recommendation') }}">Crop Recommendation</a></li>
                                    <li><a href="{{ route('crop-planning') }}">Crop Planning</a></li>
                                    <li><a href="{{ route('disease-identification') }}">Disease Identification</a></li>
                                    <li class="current-item"><a href="{{ route('fertilizer-recommendation') }}">Fertilizer
                                            Recommendation</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- End Single Widget -->
                        <div class="single-widget quick-contact-widget text-light"
                            style="background-image: url({{ asset('assets/img/800x800.png') }});">
                            <div class="content">
                                <h3>Need Help?</h3>
                                <p>
                                    Need help using this tool or understanding fertilizer suggestions? Talk to a
                                    Plantix‑AI agronomy specialist — call our office and we will connect you with an
                                    expert.
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

