@extends("layouts.frontend")

@section("title", "AI Crop Planning | Plantix-AI")

@section("footer")
@include("partials.footer-alt")
@endsection

@section("page_scripts")
<style>
.cp-card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:1.5rem; margin-bottom:1.25rem; }
.cp-section-title { font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:.75rem; }
.cp-badge { display:inline-block; padding:.25rem .75rem; border-radius:999px; font-size:.78rem; font-weight:600; }
.cp-badge-success { background:#d1fae5; color:#065f46; }
.cp-badge-warning { background:#fef3c7; color:#92400e; }
.cp-badge-danger  { background:#fee2e2; color:#991b1b; }
.cp-table th { background:#f9fafb; font-size:.8rem; font-weight:600; color:#374151; }
.cp-table td { font-size:.85rem; color:#4b5563; vertical-align:top; }
.cp-tip { background:#f0fdf4; border-left:3px solid #10b981; padding:.6rem 1rem; border-radius:0 8px 8px 0; font-size:.85rem; color:#065f46; margin-bottom:.5rem; }
.cp-threat { background:#fff7ed; border-left:3px solid #f59e0b; padding:.6rem 1rem; border-radius:0 8px 8px 0; font-size:.85rem; margin-bottom:.5rem; }
.cp-spinner { display:inline-block; width:1.2rem; height:1.2rem; border:3px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
@endsection

@section("content")

<div class="py-4 bg-light" style="border-bottom:1px solid #e5e7eb;">
    <div class="container-agri">
        <h1 class="fw-bold text-dark mb-2" style="font-size:28px;">AI Crop Planning</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="background:transparent;padding:0;font-size:14px;">
                <li class="breadcrumb-item"><a href="{{ route("home") }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">Crop Planning</li>
            </ol>
        </nav>
    </div>
</div>

<div class="py-5" style="background:#f8fafb;min-height:80vh;">
    <div class="container-agri pb-5">
        <div class="row g-5">

            {{-- ── Main Column ──────────────────────────────────────────────── --}}
            <div class="col-lg-8 order-lg-last">

                {{-- Form Card --}}
                <div class="cp-card p-lg-5 p-4 mb-4">
                    <div class="mb-4">
                        <span class="badge bg-success bg-opacity-10 text-success mb-2 px-3 py-2 fs-6 border border-success border-opacity-25 rounded-pill">
                            <i class="fas fa-robot me-2"></i> Powered by OpenRouter AI
                        </span>
                        <h2 class="fw-bold text-dark mb-2">Generate Your AI Crop Plan</h2>
                        <p class="text-muted mb-0" style="line-height:1.7;">
                            Fill in your farm details and our AI will generate a comprehensive, season-specific cultivation plan — including fertilizer schedules, irrigation timings, pest management, and yield estimates.
                        </p>
                    </div>

                    <form id="cropPlanForm">
                        @csrf
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Crop to Grow <span class="text-danger">*</span></label>
                                <select name="primary_crop" id="primary_crop" class="form-select" required>
                                    <option value="" disabled selected>Select crop...</option>
                                    <option value="Wheat">Wheat</option>
                                    <option value="Rice">Rice</option>
                                    <option value="Cotton">Cotton</option>
                                    <option value="Maize">Maize (Corn)</option>
                                    <option value="Sugarcane">Sugarcane</option>
                                    <option value="Tomato">Tomato</option>
                                    <option value="Potato">Potato</option>
                                    <option value="Onion">Onion</option>
                                    <option value="Chickpea">Chickpea</option>
                                    <option value="Soybean">Soybean</option>
                                    <option value="Sunflower">Sunflower</option>
                                    <option value="Mustard">Mustard</option>
                                    <option value="Mango">Mango</option>
                                    <option value="Banana">Banana</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Season <span class="text-danger">*</span></label>
                                <select name="season" id="season" class="form-select" required>
                                    <option value="" disabled selected>Select season...</option>
                                    <option value="Rabi">Rabi (Oct – Apr)</option>
                                    <option value="Kharif">Kharif (Jun – Oct)</option>
                                    <option value="Zaid">Zaid (Mar – Jun)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Planning Year <span class="text-danger">*</span></label>
                                <select name="year" id="year" class="form-select" required>
                                    @for ($y = now()->year; $y <= now()->year + 2; $y++)
                                        <option value="{{ $y }}" {{ $y == now()->year ? "selected" : "" }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Farm Size (Acres)</label>
                                <input type="number" name="farm_size_acres" id="farm_size_acres" class="form-control" placeholder="e.g. 5" min="0.1" max="10000" step="0.1" value="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Soil Type</label>
                                <select name="soil_type" id="soil_type" class="form-select">
                                    <option value="loamy" selected>Loamy</option>
                                    <option value="clay">Clay</option>
                                    <option value="sandy">Sandy</option>
                                    <option value="silt">Silty</option>
                                    <option value="peat">Peaty</option>
                                    <option value="chalky">Chalky</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Irrigation Type</label>
                                <select name="irrigation_type" id="irrigation_type" class="form-select">
                                    <option value="canal">Canal Irrigation</option>
                                    <option value="tube well">Tube Well</option>
                                    <option value="drip">Drip Irrigation</option>
                                    <option value="sprinkler">Sprinkler</option>
                                    <option value="rain-fed">Rain-fed</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Climate</label>
                                <select name="climate" id="climate" class="form-select">
                                    <option value="subtropical" selected>Subtropical</option>
                                    <option value="tropical">Tropical</option>
                                    <option value="temperate">Temperate</option>
                                    <option value="arid">Arid / Semi-arid</option>
                                    <option value="continental">Continental</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Water Availability</label>
                                <select name="water_availability" id="water_availability" class="form-select">
                                    <option value="abundant">Abundant</option>
                                    <option value="moderate" selected>Moderate</option>
                                    <option value="limited">Limited</option>
                                    <option value="scarce">Scarce</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" id="generateBtn" class="btn btn-success px-5 py-3 fw-bold fs-5 rounded-pill shadow-sm">
                                <i class="fas fa-robot me-2"></i> Generate AI Crop Plan
                            </button>
                        </div>
                    </form>
                </div>
                {{-- End Form Card --}}

                {{-- Result Area --}}
                <div id="planResult" style="display:none;"></div>

            </div>
            {{-- End Main Column --}}

            {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
            <div class="col-lg-4">
                <div class="cp-card mb-4 sticky-top" style="top:20px;">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">AI Tools</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                        <li><a href="{{ route("crop.recommendation") }}" class="d-flex align-items-center p-2 rounded-3 text-decoration-none text-muted">
                            <i class="fas fa-seedling me-3 text-secondary" style="width:20px;"></i> Crop Recommendation</a></li>
                        <li><a href="{{ route("crop.planning") }}" class="d-flex align-items-center p-2 rounded-3 text-decoration-none fw-semibold text-success" style="background:#f0fdf4;">
                            <i class="fas fa-calendar-alt me-3 text-success" style="width:20px;"></i> Crop Planning</a></li>
                        <li><a href="{{ route("disease.identification") }}" class="d-flex align-items-center p-2 rounded-3 text-decoration-none text-muted">
                            <i class="fas fa-microscope me-3 text-secondary" style="width:20px;"></i> Disease Identification</a></li>
                        <li><a href="{{ route("fertilizer.recommendation") }}" class="d-flex align-items-center p-2 rounded-3 text-decoration-none text-muted">
                            <i class="fas fa-flask me-3 text-secondary" style="width:20px;"></i> Fertilizer Recommendation</a></li>
                    </ul>
                </div>

                {{-- Recent Plans --}}
                @if($plans->isNotEmpty())
                <div class="cp-card">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Recent Plans</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        @foreach($plans->take(5) as $p)
                        <li class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                            <div>
                                <div class="fw-semibold small text-dark">{{ $p->primary_crop }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $p->season }} {{ $p->year }}</div>
                            </div>
                            <a href="{{ route("crop.planning.show", $p->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" style="font-size:.75rem;">View</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="cp-card bg-success text-white mt-3 text-center position-relative overflow-hidden">
                    <div class="position-absolute" style="top:-20px;right:-20px;font-size:120px;opacity:.08;"><i class="fas fa-leaf"></i></div>
                    <div class="position-relative">
                        <i class="fas fa-phone-alt fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold text-white mb-2">Need Expert Advice?</h6>
                        <p class="small text-white text-opacity-75 mb-3">Talk to our agronomy experts for complex field situations.</p>
                        <a href="{{ route("contact") }}" class="btn btn-light btn-sm rounded-pill fw-bold text-success">Contact Us</a>
                    </div>
                </div>
            </div>
            {{-- End Sidebar --}}

        </div>
    </div>
</div>

<script>
(function () {
    "use strict";

    var form    = document.getElementById("cropPlanForm");
    var btn     = document.getElementById("generateBtn");
    var result  = document.getElementById("planResult");
    var csrfToken = document.querySelector("meta[name=csrf-token]") ? document.querySelector("meta[name=csrf-token]").content : "";

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        startLoading();

        var data = {
            primary_crop:       document.getElementById("primary_crop").value,
            season:             document.getElementById("season").value,
            year:               parseInt(document.getElementById("year").value),
            farm_size_acres:    parseFloat(document.getElementById("farm_size_acres").value) || 1,
            soil_type:          document.getElementById("soil_type").value,
            irrigation_type:    document.getElementById("irrigation_type").value,
            climate:            document.getElementById("climate").value,
            water_availability: document.getElementById("water_availability").value,
            _token:             csrfToken,
        };

        fetch("{{ route("crop.planning.generate") }}", {
            method:  "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken, "Accept": "application/json" },
            body:    JSON.stringify(data),
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            stopLoading();
            if (json.success) {
                renderPlan(json.data);
            } else {
                showError(json.message || "Failed to generate plan. Please try again.");
            }
        })
        .catch(function (err) {
            stopLoading();
            showError("Network error. Please check your connection and try again.");
            console.error(err);
        });
    });

    function startLoading() {
        btn.disabled = true;
        btn.innerHTML = "<span class=\"cp-spinner me-2\"></span> Generating AI Plan...";
        result.style.display = "none";
        result.innerHTML = "";
    }

    function stopLoading() {
        btn.disabled = false;
        btn.innerHTML = "<i class=\"fas fa-robot me-2\"></i> Generate AI Crop Plan";
    }

    function showError(msg) {
        result.style.display = "block";
        result.innerHTML = "<div class=\"alert alert-danger rounded-3\"><i class=\"fas fa-exclamation-circle me-2\"></i>" + esc(msg) + "</div>";
        result.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    function renderPlan(d) {
        var ai = d.ai_plan_data;
        var html = "";

        // ── Header ──────────────────────────────────────────────────────────
        html += "<div class=\"cp-card p-4 mb-3\" style=\"border-left:4px solid #10b981;\">";
        html += "<div class=\"d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3\">";
        html += "<div>";
        html += "<h4 class=\"fw-bold text-dark mb-1\"><i class=\"fas fa-clipboard-check text-success me-2\"></i>" + esc(d.primary_crop) + " — " + esc(d.season) + " " + esc(String(d.year)) + "</h4>";
        if (d.ai_model) {
            html += "<small class=\"text-muted\"><i class=\"fas fa-robot me-1\"></i> " + esc(d.ai_model) + "</small>";
        }
        html += "</div>";

        // Suitability badge
        if (ai && ai.suitability) {
            var sc = ai.suitability.score || 0;
            var badgeClass = sc >= 75 ? "cp-badge-success" : (sc >= 50 ? "cp-badge-warning" : "cp-badge-danger");
            html += "<span class=\"cp-badge " + badgeClass + " fs-6\">Suitability: " + sc + "% — " + esc(ai.suitability.label || "") + "</span>";
        }
        html += "</div>";

        // Overview
        if (ai && ai.overview) {
            html += "<p class=\"text-muted mb-0\" style=\"line-height:1.7;\">" + esc(ai.overview) + "</p>";
        } else if (d.recommendations) {
            html += "<p class=\"text-muted mb-0\" style=\"line-height:1.7;\">" + esc(d.recommendations) + "</p>";
        }
        html += "</div>";

        if (!ai) {
            // Fallback: show basic plan data
            html += renderFallbackPlan(d);
            result.innerHTML = html;
            result.style.display = "block";
            result.scrollIntoView({ behavior: "smooth", block: "nearest" });
            return;
        }

        // ── Suitability Notes ────────────────────────────────────────────────
        if (ai.suitability && ai.suitability.notes && ai.suitability.notes.length) {
            html += "<div class=\"cp-card p-4 mb-3\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-info-circle me-1\"></i> Suitability Notes</div>";
            ai.suitability.notes.forEach(function (n) {
                html += "<div class=\"cp-tip\">" + esc(n) + "</div>";
            });
            html += "</div>";
        }

        // ── Two-column: Land Prep + Sowing ───────────────────────────────────
        html += "<div class=\"row g-3 mb-3\">";

        if (ai.land_preparation) {
            html += "<div class=\"col-md-6\">";
            html += "<div class=\"cp-card h-100\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-tractor me-1\"></i> Land Preparation</div>";
            if (ai.land_preparation.timing) {
                html += "<p class=\"small text-muted mb-2\"><strong>Timing:</strong> " + esc(ai.land_preparation.timing) + "</p>";
            }
            if (ai.land_preparation.steps && ai.land_preparation.steps.length) {
                html += "<ol class=\"ps-3 mb-0\">";
                ai.land_preparation.steps.forEach(function (s) { html += "<li class=\"small text-muted mb-1\">" + esc(s) + "</li>"; });
                html += "</ol>";
            }
            html += "</div></div>";
        }

        if (ai.sowing) {
            html += "<div class=\"col-md-6\">";
            html += "<div class=\"cp-card h-100\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-seedling me-1\"></i> Sowing Details</div>";
            var sowFields = [
                ["Best Time",  ai.sowing.best_time],
                ["Seed Rate",  ai.sowing.seed_rate],
                ["Spacing",    ai.sowing.spacing],
                ["Depth",      ai.sowing.depth],
            ];
            sowFields.forEach(function (f) {
                if (f[1]) html += "<p class=\"small text-muted mb-1\"><strong>" + esc(f[0]) + ":</strong> " + esc(f[1]) + "</p>";
            });
            html += "</div></div>";
        }

        html += "</div>"; // end row

        // ── Fertilizer Schedule ──────────────────────────────────────────────
        if (ai.fertilizer_schedule && ai.fertilizer_schedule.length) {
            html += "<div class=\"cp-card mb-3\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-flask me-1\"></i> Fertilizer Schedule</div>";
            html += "<div class=\"table-responsive\">";
            html += "<table class=\"table table-sm cp-table mb-0\">";
            html += "<thead><tr><th>Stage</th><th>Fertilizer</th><th>Dose / Acre</th><th>Notes</th></tr></thead><tbody>";
            ai.fertilizer_schedule.forEach(function (f) {
                html += "<tr><td class=\"fw-semibold\">" + esc(f.stage || "") + "</td><td>" + esc(f.fertilizer || "") + "</td><td>" + esc(f.dose || "") + "</td><td>" + esc(f.notes || "") + "</td></tr>";
            });
            html += "</tbody></table></div></div>";
        }

        // ── Irrigation Plan ──────────────────────────────────────────────────
        if (ai.irrigation_plan && ai.irrigation_plan.length) {
            html += "<div class=\"cp-card mb-3\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-water me-1\"></i> Irrigation Plan</div>";
            html += "<div class=\"table-responsive\">";
            html += "<table class=\"table table-sm cp-table mb-0\">";
            html += "<thead><tr><th>Stage</th><th>Timing</th><th>Amount</th><th>Notes</th></tr></thead><tbody>";
            ai.irrigation_plan.forEach(function (ir) {
                html += "<tr><td class=\"fw-semibold\">" + esc(ir.stage || "") + "</td><td>" + esc(ir.timing || "") + "</td><td>" + esc(ir.amount || "") + "</td><td>" + esc(ir.notes || "") + "</td></tr>";
            });
            html += "</tbody></table></div></div>";
        }

        // ── Pest & Disease ───────────────────────────────────────────────────
        if (ai.pest_disease_management && ai.pest_disease_management.length) {
            html += "<div class=\"cp-card mb-3\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-bug me-1\"></i> Pest & Disease Management</div>";
            ai.pest_disease_management.forEach(function (p) {
                html += "<div class=\"cp-threat\">";
                html += "<strong class=\"text-dark\">" + esc(p.threat || "") + "</strong>";
                if (p.symptoms) html += " <span class=\"text-muted\">— " + esc(p.symptoms) + "</span>";
                if (p.control)  html += "<div class=\"mt-1 small\"><i class=\"fas fa-check-circle text-success me-1\"></i>" + esc(p.control) + "</div>";
                html += "</div>";
            });
            html += "</div>";
        }

        // ── Harvest + Yield ──────────────────────────────────────────────────
        html += "<div class=\"row g-3 mb-3\">";

        if (ai.harvest) {
            html += "<div class=\"col-md-6\">";
            html += "<div class=\"cp-card h-100\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-cut me-1\"></i> Harvest</div>";
            var hvFields = [
                ["Days to Maturity", ai.harvest.days_to_maturity],
                ["Indicators",       ai.harvest.indicators],
                ["Method",           ai.harvest.method],
                ["Post-Harvest",     ai.harvest.post_harvest],
            ];
            hvFields.forEach(function (f) {
                if (f[1]) html += "<p class=\"small text-muted mb-1\"><strong>" + esc(f[0]) + ":</strong> " + esc(f[1]) + "</p>";
            });
            html += "</div></div>";
        }

        if (ai.expected_yield) {
            html += "<div class=\"col-md-6\">";
            html += "<div class=\"cp-card h-100\" style=\"background:#f0fdf4;border-color:#bbf7d0;\">";
            html += "<div class=\"cp-section-title text-success\"><i class=\"fas fa-chart-line me-1\"></i> Expected Yield & Revenue</div>";
            if (ai.expected_yield.range) {
                html += "<p class=\"fw-bold text-dark mb-1\" style=\"font-size:1.1rem;\">" + esc(ai.expected_yield.range) + " " + esc(ai.expected_yield.unit || "") + "</p>";
            }
            if (ai.expected_yield.revenue_estimate_pkr) {
                html += "<p class=\"small text-success mb-0\"><i class=\"fas fa-money-bill-wave me-1\"></i> Est. Revenue: <strong>" + esc(ai.expected_yield.revenue_estimate_pkr) + "</strong></p>";
            }
            html += "</div></div>";
        }

        html += "</div>"; // end row

        // ── Key Tips ─────────────────────────────────────────────────────────
        if (ai.key_tips && ai.key_tips.length) {
            html += "<div class=\"cp-card mb-3\">";
            html += "<div class=\"cp-section-title\"><i class=\"fas fa-lightbulb me-1\"></i> Key Tips</div>";
            ai.key_tips.forEach(function (t) {
                html += "<div class=\"cp-tip\">" + esc(t) + "</div>";
            });
            html += "</div>";
        }

        // ── Save confirmation ────────────────────────────────────────────────
        html += "<div class=\"alert alert-success d-flex align-items-center gap-2 rounded-3\">";
        html += "<i class=\"fas fa-check-circle fs-5\"></i>";
        html += "<div><strong>Plan saved!</strong> View it anytime from your <a href=\"{{ route("customer.dashboard") }}\" class=\"alert-link\">dashboard</a>. Plan ID: #" + esc(String(d.id)) + "</div>";
        html += "</div>";

        result.innerHTML = html;
        result.style.display = "block";
        result.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    function renderFallbackPlan(d) {
        var html = "";
        if (d.recommendations) {
            html += "<div class=\"cp-card p-4 mb-3\">";
            html += "<div class=\"cp-section-title\">Recommendations</div>";
            html += "<p class=\"text-muted small mb-0\" style=\"white-space:pre-line;\">" + esc(d.recommendations) + "</p>";
            html += "</div>";
        }
        if (d.soil_suitability_notes) {
            html += "<div class=\"cp-card p-4 mb-3\">";
            html += "<div class=\"cp-section-title\">Soil Suitability</div>";
            html += "<p class=\"text-muted small mb-0\">" + esc(d.soil_suitability_notes) + "</p>";
            html += "</div>";
        }
        return html;
    }

    function esc(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

})();
</script>

@endsection
