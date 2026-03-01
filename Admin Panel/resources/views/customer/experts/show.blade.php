@extends('layouts.frontend')

@section('title', $expert->display_name . ' | Expert Profile | Plantix')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Enforce min date on datetime-local (must be at least 1 hour from now)
    const dtInput = document.getElementById('scheduled_at');
    if (dtInput) {
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const pad = n => String(n).padStart(2, '0');
        const minStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        dtInput.setAttribute('min', minStr);
        if (!dtInput.value) dtInput.value = minStr;
    }

    // Smooth scroll to booking form
    document.querySelectorAll('[data-scroll-to]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById(this.dataset.scrollTo);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Star rating display
    document.querySelectorAll('.star-rating').forEach(container => {
        const val = parseFloat(container.dataset.rating || 0);
        let html = '';
        for (let i = 1; i <= 5; i++) {
            const fill = Math.min(Math.max(val - (i - 1), 0), 1);
            if (fill >= 0.8)      html += '<i class="fas fa-star" style="color:#f59e0b;"></i>';
            else if (fill >= 0.3) html += '<i class="fas fa-star-half-alt" style="color:#f59e0b;"></i>';
            else                   html += '<i class="far fa-star" style="color:#f59e0b;"></i>';
        }
        container.innerHTML = html;
    });
});
</script>
@endsection

@section('content')
<div style="background: var(--agri-bg); min-height: calc(100vh - 80px);">

    {{-- ── Breadcrumb + back ────────────────────────────────────────────────── --}}
    <div style="background:white;border-bottom:1px solid #e8ede9;padding:14px 0;">
        <div class="container-agri">
            <a href="{{ route('experts.index') }}" class="text-decoration-none d-inline-flex align-items-center" style="color:var(--agri-text-muted);font-size:14px;gap:6px;">
                <i class="fas fa-arrow-left" style="font-size:12px;"></i> Back to All Experts
            </a>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="container-agri pt-4">
        <div class="alert alert-success d-flex align-items-center gap-3" style="border-radius:12px;border:1px solid #bbf7d0;background:#f0fdf4;">
            <i class="fas fa-check-circle text-success fs-5"></i>
            <div>
                <strong>Booking Confirmed!</strong> {{ session('success') }}
                <br><a href="{{ route('appointments') }}" class="text-success fw-semibold">View my appointments →</a>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="container-agri pt-4">
        <div class="alert alert-danger d-flex align-items-center gap-3" style="border-radius:12px;border:1px solid #fecaca;background:#fef2f2;">
            <i class="fas fa-exclamation-circle text-danger fs-5"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="container-agri py-4">
        <div class="row g-4">

            {{-- ══════════════════════════════════════════════════
                 LEFT COLUMN — Profile details
            ══════════════════════════════════════════════════ --}}
            <div class="col-lg-8">

                {{-- Hero card --}}
                <div class="card-agri mb-4" style="overflow:hidden;">
                    <div style="height:200px;background:linear-gradient(135deg,var(--agri-primary) 0%,#1a5c3a 100%);position:relative;">
                        {{-- Cover decoration --}}
                        <div style="position:absolute;right:-40px;top:-40px;width:200px;height:200px;background:rgba(255,255,255,0.07);border-radius:50%;"></div>
                    </div>

                    <div class="px-4 pb-4" style="margin-top:-70px;position:relative;z-index:1;">
                        <div class="d-flex flex-wrap align-items-end gap-3">
                            {{-- Avatar --}}
                            <div style="width:110px;height:110px;border-radius:50%;border:4px solid white;overflow:hidden;flex-shrink:0;box-shadow:0 4px 16px rgba(0,0,0,0.15);">
                                @if($expert->profile_image)
                                    <img src="{{ asset($expert->profile_image) }}" alt="{{ $expert->display_name }}"
                                         style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <div style="width:100%;height:100%;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-user-circle" style="font-size:60px;color:var(--agri-primary);opacity:0.5;"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1 mt-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h2 class="fw-bold mb-0" style="font-size:clamp(20px,3vw,28px);color:#1a1a1a;">{{ $expert->display_name }}</h2>
                                    @if($expert->verified_at)
                                    <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:0.5px;">
                                        <i class="fas fa-shield-alt me-1"></i>Verified
                                    </span>
                                    @endif
                                    <span style="background:{{ $expert->is_available ? 'rgba(39,174,96,0.12)' : 'rgba(100,116,139,0.12)' }};color:{{ $expert->is_available ? '#15803d' : '#475569' }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $expert->is_available ? '#22c55e' : '#94a3b8' }};display:inline-block;margin-right:5px;"></span>
                                        {{ $expert->is_available ? 'Available' : 'Unavailable' }}
                                    </span>
                                </div>
                                <p style="font-size:16px;color:var(--agri-primary);font-weight:600;margin-bottom:6px;">{{ $expert->specialty }}</p>
                                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size:13px;">
                                    @if($expert->profile?->city)
                                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $expert->profile->city }}{{ $expert->profile->country ? ', '.$expert->profile->country : '' }}</span>
                                    @endif
                                    @if($expert->profile?->experience_years)
                                    <span><i class="fas fa-briefcase me-1"></i>{{ $expert->profile->experience_years }} yrs experience</span>
                                    @endif
                                    @if($expert->total_completed > 0)
                                    <span><i class="fas fa-calendar-check me-1"></i>{{ $expert->total_completed }} sessions completed</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Rating + scroll CTA --}}
                            <div class="text-center ms-auto">
                                @if($expert->rating_avg > 0)
                                <div class="star-rating mb-1 fs-5" data-rating="{{ $expert->rating_avg }}"></div>
                                <div class="fw-bold" style="font-size:22px;color:#1a1a1a;line-height:1;">{{ number_format($expert->rating_avg, 1) }}</div>
                                <div class="text-muted" style="font-size:11px;">Rating</div>
                                @endif
                            </div>
                        </div>

                        {{-- Quick stats --}}
                        <div class="row g-2 mt-3">
                            <div class="col-6 col-sm-3">
                                <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:12px;text-align:center;">
                                    <div class="fw-bold" style="font-size:20px;color:var(--agri-primary);">{{ $expert->total_completed ?? 0 }}</div>
                                    <div class="text-muted" style="font-size:11px;">Completed</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:12px;text-align:center;">
                                    <div class="fw-bold" style="font-size:20px;color:var(--agri-primary);">{{ $expert->specializations->count() }}</div>
                                    <div class="text-muted" style="font-size:11px;">Specializations</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:12px;text-align:center;">
                                    <div class="fw-bold" style="font-size:20px;color:var(--agri-primary);">{{ $expert->profile?->experience_years ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:11px;">Years exp.</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:12px;text-align:center;">
                                    <div class="fw-bold" style="font-size:20px;color:var(--agri-primary);">
                                        {{ $expert->consultation_duration_minutes ?? 30 }}<span style="font-size:13px;">m</span>
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">Per session</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>{{-- /hero card --}}

                {{-- Bio --}}
                @if($expert->bio)
                <div class="card-agri mb-4 p-4">
                    <h5 class="fw-bold mb-3" style="font-size:17px;color:#1a1a1a;">
                        <i class="fas fa-user me-2" style="color:var(--agri-primary);"></i>About
                    </h5>
                    <p class="text-muted mb-0" style="font-size:15px;line-height:1.8;">{{ $expert->bio }}</p>
                </div>
                @endif

                {{-- Specializations --}}
                @if($expert->specializations->isNotEmpty())
                <div class="card-agri mb-4 p-4">
                    <h5 class="fw-bold mb-3" style="font-size:17px;color:#1a1a1a;">
                        <i class="fas fa-seedling me-2" style="color:var(--agri-primary);"></i>Areas of Expertise
                    </h5>
                    <div class="row g-3">
                        @foreach($expert->specializations as $spec)
                        <div class="col-sm-6">
                            <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-leaf" style="color:var(--agri-primary);font-size:14px;"></i>
                                    </div>
                                    <span class="fw-semibold text-dark" style="font-size:14px;">{{ $spec->name }}</span>
                                </div>
                                @if($spec->level)
                                <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;flex-shrink:0;
                                    @if(strtolower($spec->level)==='expert') background:#fef3c7;color:#92400e;
                                    @elseif(strtolower($spec->level)==='intermediate') background:#dbeafe;color:#1e40af;
                                    @else background:#dcfce7;color:#166534; @endif">
                                    {{ ucfirst($spec->level) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Profile details --}}
                @if($expert->profile && ($expert->profile->certifications || $expert->profile->agency_name || $expert->profile->website || $expert->profile->linkedin || $expert->profile->contact_phone))
                <div class="card-agri mb-4 p-4">
                    <h5 class="fw-bold mb-3" style="font-size:17px;color:#1a1a1a;">
                        <i class="fas fa-id-card me-2" style="color:var(--agri-primary);"></i>Profile Details
                    </h5>
                    <div class="row g-3">
                        @if($expert->profile->agency_name)
                        <div class="col-sm-6 d-flex align-items-start gap-3">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-building" style="color:var(--agri-primary);font-size:14px;"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Organisation</div>
                                <div class="text-dark fw-semibold" style="font-size:14px;">{{ $expert->profile->agency_name }}</div>
                            </div>
                        </div>
                        @endif

                        @if($expert->profile->certifications)
                        <div class="col-12 d-flex align-items-start gap-3">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-award" style="color:var(--agri-primary);font-size:14px;"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Certifications</div>
                                <div class="text-dark" style="font-size:14px;">{{ $expert->profile->certifications }}</div>
                            </div>
                        </div>
                        @endif

                        @if($expert->profile->website)
                        <div class="col-sm-6 d-flex align-items-start gap-3">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-globe" style="color:var(--agri-primary);font-size:14px;"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">Website</div>
                                <a href="{{ $expert->profile->website }}" target="_blank" class="text-decoration-none" style="font-size:14px;color:var(--agri-primary);">{{ $expert->profile->website }}</a>
                            </div>
                        </div>
                        @endif

                        @if($expert->profile->linkedin)
                        <div class="col-sm-6 d-flex align-items-start gap-3">
                            <div style="width:36px;height:36px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fab fa-linkedin" style="color:#1d4ed8;font-size:16px;"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">LinkedIn</div>
                                <a href="{{ $expert->profile->linkedin }}" target="_blank" class="text-decoration-none" style="font-size:14px;color:#1d4ed8;">View Profile</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Recent reviews / completed sessions --}}
                @if($expert->appointments->isNotEmpty())
                <div class="card-agri mb-4 p-4">
                    <h5 class="fw-bold mb-3" style="font-size:17px;color:#1a1a1a;">
                        <i class="fas fa-comments me-2" style="color:var(--agri-primary);"></i>Recent Consultations
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        @foreach($expert->appointments as $session)
                        <div style="background:var(--agri-bg);border-radius:var(--agri-radius-sm);padding:16px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:var(--agri-primary);">
                                        {{ strtoupper(substr($session->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size:13px;">{{ $session->user->name ?? 'Anonymous' }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $session->scheduled_at?->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                @if($session->rating)
                                <div class="d-flex align-items-center gap-1">
                                    @for($r=1;$r<=5;$r++)
                                    <i class="{{ $r <= $session->rating ? 'fas' : 'far' }} fa-star" style="color:#f59e0b;font-size:11px;"></i>
                                    @endfor
                                </div>
                                @endif
                            </div>
                            @if($session->topic)
                            <p class="text-muted mb-1" style="font-size:13px;"><strong>Topic:</strong> {{ $session->topic }}</p>
                            @endif
                            @if($session->feedback)
                            <p class="text-muted mb-0" style="font-size:13px;font-style:italic;">"{{ $session->feedback }}"</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>{{-- /col-lg-8 --}}

            {{-- ══════════════════════════════════════════════════
                 RIGHT COLUMN — Booking form
            ══════════════════════════════════════════════════ --}}
            <div class="col-lg-4">
                <div class="sticky-top" style="top:100px; z-index:99;">

                    {{-- Price card --}}
                    <div class="card-agri p-4 mb-4 text-center" style="background:linear-gradient(135deg,var(--agri-primary) 0%,#1a5c3a 100%);color:white;">
                        @if($expert->consultation_price)
                        <div class="mb-1" style="font-size:13px;opacity:0.8;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Consultation Fee</div>
                        <div class="fw-bold" style="font-size:36px;line-height:1.1;">₨ {{ number_format($expert->consultation_price) }}</div>
                        <div style="font-size:12px;opacity:0.7;margin-bottom:16px;">per {{ $expert->consultation_duration_minutes ?? 30 }}-minute session</div>
                        @else
                        <div class="fw-bold mb-3" style="font-size:22px;">Free Consultation</div>
                        @endif
                        @auth('web')
                            @if($expert->is_available)
                            <a href="#book-form" data-scroll-to="book-form" class="btn-agri" style="background:white;color:var(--agri-primary);font-weight:700;padding:12px 28px;border-radius:30px;text-decoration:none;display:inline-block;">
                                <i class="fas fa-calendar-plus me-2"></i>Book Now
                            </a>
                            @else
                            <div style="background:rgba(255,255,255,0.15);border-radius:10px;padding:12px;font-size:14px;">Expert is temporarily unavailable</div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-agri" style="background:white;color:var(--agri-primary);font-weight:700;padding:12px 28px;border-radius:30px;text-decoration:none;display:inline-block;">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                            </a>
                        @endauth
                    </div>

                    {{-- ── Booking form (logged-in users only) ──────────────── --}}
                    @auth('web')
                    @if($expert->is_available)
                    <div class="card-agri p-4" id="book-form">
                        <h5 class="fw-bold mb-1" style="font-size:17px;color:#1a1a1a;">
                            <i class="fas fa-calendar-plus me-2" style="color:var(--agri-primary);"></i>Quick Book
                        </h5>
                        <p class="text-muted mb-4" style="font-size:13px;">Fill in the details and we'll confirm your appointment.</p>

                        @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;border-radius:10px;">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('experts.quick-book', $expert->id) }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="scheduled_at" style="font-size:13px;color:#374151;">
                                    Preferred Date & Time <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="datetime-local"
                                    id="scheduled_at"
                                    name="scheduled_at"
                                    class="form-agri @error('scheduled_at') is-invalid @enderror"
                                    value="{{ old('scheduled_at') }}"
                                    style="width:100%;"
                                    required>
                                @error('scheduled_at')
                                <div class="invalid-feedback" style="display:block;font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="topic" style="font-size:13px;color:#374151;">
                                    Topic / Purpose <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="topic"
                                    name="topic"
                                    class="form-agri @error('topic') is-invalid @enderror"
                                    value="{{ old('topic') }}"
                                    placeholder="e.g. Crop disease in wheat"
                                    style="width:100%;"
                                    required>
                                @error('topic')
                                <div class="invalid-feedback" style="display:block;font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="notes" style="font-size:13px;color:#374151;">
                                    Additional Notes <span class="text-muted">(optional)</span>
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    class="form-agri @error('notes') is-invalid @enderror"
                                    rows="3"
                                    placeholder="Describe your problem, attach any relevant details…"
                                    style="width:100%;resize:vertical;">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback" style="display:block;font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn-agri btn-agri-primary w-100" style="padding:13px;font-size:15px;font-weight:700;">
                                <i class="fas fa-check-circle me-2"></i>
                                Confirm Booking
                                @if($expert->consultation_price)
                                &nbsp;— ₨ {{ number_format($expert->consultation_price) }}
                                @endif
                            </button>

                            <p class="text-center text-muted mt-3 mb-0" style="font-size:12px;">
                                <i class="fas fa-lock me-1"></i> Secure booking. You'll receive a confirmation shortly.
                            </p>
                        </form>
                    </div>
                    @else
                    <div class="card-agri p-4 text-center">
                        <div style="width:60px;height:60px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-clock" style="font-size:24px;color:#94a3b8;"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Currently Unavailable</h6>
                        <p class="text-muted mb-3" style="font-size:13px;">This expert is not taking new bookings at the moment. Check back soon or browse other experts.</p>
                        <a href="{{ route('experts.index') }}" class="btn-agri btn-agri-outline w-100" style="font-size:13px;">Browse Other Experts</a>
                    </div>
                    @endif
                    @else
                    <div class="card-agri p-4 text-center">
                        <div style="width:60px;height:60px;border-radius:50%;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-user-lock" style="font-size:24px;color:var(--agri-primary);"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Login Required</h6>
                        <p class="text-muted mb-3" style="font-size:13px;">Please create an account or log in to book a consultation with this expert.</p>
                        <a href="{{ route('login') }}" class="btn-agri btn-agri-primary w-100 mb-2" style="font-size:13px;">Login</a>
                        <a href="{{ route('signup') }}" class="btn-agri btn-agri-outline w-100" style="font-size:13px;">Create Account</a>
                    </div>
                    @endauth

                </div>{{-- /sticky-top --}}
            </div>{{-- /col-lg-4 --}}

        </div>{{-- /row --}}
    </div>{{-- /container-agri --}}
</div>
@endsection
