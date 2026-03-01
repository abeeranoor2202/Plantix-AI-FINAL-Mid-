@extends('layouts.frontend')

@section('title', 'Find Expert Agronomists | Plantix')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate cards on load
    document.querySelectorAll('.expert-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 60);
    });
});
</script>
@endsection

@section('content')
<div style="background: var(--agri-bg); min-height: calc(100vh - 80px);">

    {{-- ── Hero banner ─────────────────────────────────────────────────────── --}}
    <div style="background: linear-gradient(135deg, var(--agri-primary) 0%, #1a5c3a 100%); padding: 60px 0 50px; position: relative; overflow: hidden;">
        <div style="position: absolute; right: -60px; top: -60px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <div style="position: absolute; left: 20%; bottom: -80px; width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
        <div class="container-agri" style="position: relative; z-index: 1;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2" style="color: rgba(255,255,255,0.7); font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">EXPERT PANEL</p>
                    <h1 class="fw-bold text-white mb-3" style="font-size: clamp(26px,4vw,42px);">Book Consultations with Specialists</h1>
                    <p class="mb-0" style="color: rgba(255,255,255,0.75); font-size: 16px; max-width: 540px;">
                        Connect with certified agronomists, crop scientists, and irrigation experts for personalised advice on your farm.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="{{ route('appointments') }}" class="btn-agri btn-agri-outline" style="color: white; border-color: rgba(255,255,255,0.6); padding: 12px 24px; font-size: 15px;">
                        <i class="fas fa-calendar-alt me-2"></i>My Appointments
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-agri py-5">

        {{-- ── Search + filter bar ──────────────────────────────────────────── --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('experts.index') }}" class="d-flex flex-wrap gap-3 align-items-center">
                <div class="flex-grow-1" style="min-width: 260px;">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute" style="left:14px; top:50%; transform:translateY(-50%); color: var(--agri-text-muted);"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-agri ps-5" placeholder="Search by name, specialty…" style="height:46px;">
                    </div>
                </div>
                <div style="min-width: 190px;">
                    <select name="specialization" class="form-agri" style="height:46px; cursor:pointer;">
                        <option value="">All Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') === $spec ? 'selected' : '' }}>{{ $spec }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 160px;">
                    <select name="sort" class="form-agri" style="height:46px; cursor:pointer;">
                        <option value="rating"      {{ ($sort ?? 'rating') === 'rating'      ? 'selected' : '' }}>Top Rated</option>
                        <option value="experience"  {{ ($sort ?? '') === 'experience'         ? 'selected' : '' }}>Most Experienced</option>
                        <option value="price_asc"   {{ ($sort ?? '') === 'price_asc'          ? 'selected' : '' }}>Price: Low → High</option>
                        <option value="price_desc"  {{ ($sort ?? '') === 'price_desc'         ? 'selected' : '' }}>Price: High → Low</option>
                    </select>
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height:46px; padding: 0 24px;">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                @if(request()->hasAny(['search','specialization','sort']))
                    <a href="{{ route('experts.index') }}" class="btn-agri btn-agri-outline" style="height:46px; padding:0 18px; font-size:14px; display:flex; align-items:center;">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- ── Results summary ─────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0" style="font-size:14px;">
                Showing <strong>{{ $experts->firstItem() }}–{{ $experts->lastItem() }}</strong> of <strong>{{ $experts->total() }}</strong> available expert{{ $experts->total() !== 1 ? 's' : '' }}
            </p>
            @if($experts->hasPages())
                <span class="text-muted" style="font-size:13px;">Page {{ $experts->currentPage() }} of {{ $experts->lastPage() }}</span>
            @endif
        </div>

        {{-- ── Expert grid ──────────────────────────────────────────────────── --}}
        @if($experts->isEmpty())
            <div class="text-center py-5">
                <div style="width:90px;height:90px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:var(--agri-shadow-sm);">
                    <i class="fas fa-user-md" style="font-size:40px;color:var(--agri-text-muted);"></i>
                </div>
                <h5 class="fw-bold text-dark">No experts found</h5>
                <p class="text-muted">Try adjusting your search or removing filters.</p>
                <a href="{{ route('experts.index') }}" class="btn-agri btn-agri-outline mt-2">View All Experts</a>
            </div>
        @else
        <div class="row g-4">
            @foreach($experts as $expert)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="expert-card card-agri h-100 d-flex flex-column" style="border:none;overflow:hidden;cursor:pointer;" onclick="window.location='{{ route('experts.show', $expert->id) }}'">

                    {{-- Photo --}}
                    <div style="height:220px;overflow:hidden;position:relative;">
                        @if($expert->profile_image)
                            <img src="{{ asset($expert->profile_image) }}" alt="{{ $expert->display_name }}"
                                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease;"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='scale(1)'">
                        @else
                            <div style="width:100%;height:100%;background:var(--agri-primary-light);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-user-circle" style="font-size:80px;color:var(--agri-primary);opacity:0.5;"></i>
                            </div>
                        @endif
                        {{-- Available badge --}}
                        <div style="position:absolute;top:12px;right:12px;">
                            <span style="background:rgba(39,174,96,0.9);color:white;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;display:flex;align-items:center;gap:4px;">
                                <span style="width:6px;height:6px;background:white;border-radius:50%;display:inline-block;"></span> Available
                            </span>
                        </div>
                    </div>

                    <div class="p-3 d-flex flex-column flex-grow-1">
                        {{-- Name + rating --}}
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="fw-bold mb-0 text-dark" style="font-size:15px;line-height:1.3;">{{ $expert->display_name }}</h5>
                            @if($expert->rating_avg > 0)
                            <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                                <i class="fas fa-star" style="color:#f59e0b;font-size:12px;"></i>
                                <span class="fw-bold" style="font-size:13px;color:#1a1a1a;">{{ number_format($expert->rating_avg, 1) }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Specialty --}}
                        <p class="mb-2" style="font-size:13px;color:var(--agri-primary);font-weight:600;">{{ $expert->specialty }}</p>

                        {{-- Location --}}
                        @if($expert->profile?->city)
                        <p class="text-muted mb-2" style="font-size:12px;">
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $expert->profile->city }}{{ $expert->profile->country ? ', '.$expert->profile->country : '' }}
                        </p>
                        @endif

                        {{-- Specialization pills --}}
                        @if($expert->specializations->isNotEmpty())
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach($expert->specializations->take(3) as $spec)
                            <span style="font-size:10px;background:var(--agri-primary-light);color:var(--agri-primary);padding:2px 8px;border-radius:20px;font-weight:600;">{{ $spec->name }}</span>
                            @endforeach
                            @if($expert->specializations->count() > 3)
                            <span style="font-size:10px;background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:20px;">
                                +{{ $expert->specializations->count() - 3 }} more
                            </span>
                            @endif
                        </div>
                        @endif

                        {{-- Experience --}}
                        @if($expert->profile?->experience_years)
                        <p class="text-muted mb-2" style="font-size:12px;">
                            <i class="fas fa-briefcase me-1"></i>{{ $expert->profile->experience_years }} yr{{ $expert->profile->experience_years != 1 ? 's' : '' }} experience
                        </p>
                        @endif

                        <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                            {{-- Price --}}
                            @if($expert->consultation_price)
                            <div>
                                <span class="fw-bold text-dark" style="font-size:15px;">₨ {{ number_format($expert->consultation_price) }}</span>
                                <span class="text-muted" style="font-size:11px;">/ session</span>
                            </div>
                            @else
                            <span class="text-muted" style="font-size:13px;">Free consultation</span>
                            @endif

                            <a href="{{ route('experts.show', $expert->id) }}"
                               class="btn-agri btn-agri-primary text-decoration-none"
                               style="font-size:13px;padding:7px 16px;"
                               onclick="event.stopPropagation();">
                                Book →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($experts->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $experts->links() }}
        </div>
        @endif
        @endif

    </div>{{-- /container-agri --}}
</div>
@endsection
