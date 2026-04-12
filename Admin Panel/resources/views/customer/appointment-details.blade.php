@extends('layouts.frontend')

@section('title', 'Appointment Details | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4 justify-content-center">
            <div class="col-lg-8">
                
                <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('appointments') }}" class="btn-agri btn-agri-outline d-flex align-items-center p-2 rounded-circle border-0" style="width: 40px; height: 40px; justify-content: center; background: white; box-shadow: var(--agri-shadow-sm);">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="fw-bold mb-0 text-dark d-flex align-items-center gap-3">
                            Appointment #{{ $appointment->id }}
                            <span class="badge rounded-pill fw-medium fs-6" style="background: {{ $appointment->status === 'completed' ? 'rgba(16, 185, 129, 0.1); color: #10B981;' : ($appointment->status === 'cancelled' ? 'rgba(239, 68, 68, 0.1); color: #EF4444;' : 'rgba(245, 158, 11, 0.1); color: #F59E0B;') }} padding: 6px 12px; font-size: 14px; vertical-align: middle;">
                                {{ ucwords(str_replace('_', ' ', $appointment->status)) }}
                            </span>
                        </h2>
                    </div>
                    
                    <div class="d-flex gap-2">
                        @if($appointment->status === 'pending_payment')
                        <a href="{{ route('appointment.pay', $appointment->id) }}" class="btn-agri btn-agri-primary text-decoration-none d-flex align-items-center gap-2" style="padding: 8px 20px;">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                        @endif
                        @if(in_array($appointment->status, ['pending','confirmed']))
                        <form method="POST" action="{{ route('appointment.cancel', $appointment->id) }}">
                            @csrf
                            <button class="btn-agri text-danger" style="padding: 8px 16px; background: rgba(239, 68, 68, 0.1); border: none;" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel Appointment</button>
                        </form>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="card-agri p-0 overflow-hidden border-0 mb-4">
                    <div class="p-4" style="background: var(--agri-primary-dark); color: white;">
                        <h4 class="fw-bold mb-3 text-white"><i class="far fa-calendar-alt me-2 text-warning"></i> Expected Meeting Session</h4>
                        <div class="d-flex flex-wrap gap-4 align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="far fa-clock"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-white-50 fs-sm text-uppercase fw-bold" style="letter-spacing: 0.5px;">Scheduled Date</p>
                                    <h5 class="mb-0 text-white">{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('l, d M Y') : 'Pending' }}</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-white-50 fs-sm text-uppercase fw-bold" style="letter-spacing: 0.5px;">Time</p>
                                    <h5 class="mb-0 text-white">{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('h:i A') : 'TBD' }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-0">
                        <div class="col-md-6 border-end border-bottom">
                            <div class="p-4 h-100">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Consulting Expert</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 48px; height: 48px; background: var(--agri-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <i class="fas fa-user-md text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $appointment->expert->user->name ?? 'Any Available Expert' }}</h5>
                                        <p class="text-muted mb-0 small">Agricultural Specialist</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 border-bottom">
                            <div class="p-4 h-100">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Patient / Customer</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 48px; height: 48px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ substr(auth('web')->user()->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ auth('web')->user()->name }}</h5>
                                        <p class="text-muted mb-0 small">{{ auth('web')->user()->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-white">
                        <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 13px; letter-spacing: 0.5px;">Diagnostic Notes / Descriptions</h6>
                        <div class="p-4 bg-light rounded-3 text-dark" style="font-size: 15px; line-height: 1.6; border: 1px solid var(--agri-border);">
                            {{ $appointment->notes ?: 'No additional notes provided by the user for this appointment.' }}
                        </div>
                    </div>
                </div>

                <div id="review" class="card-agri p-4 border-0 mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-star text-warning me-2"></i>Appointment Review</h4>
                            <p class="text-muted mb-0">Share feedback about your consultation with the expert.</p>
                        </div>
                        @if($appointment->status === 'completed' && $appointment->customer_rating)
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Review submitted</span>
                        @endif
                    </div>

                    @if($appointment->status !== 'completed')
                        <div class="alert alert-light border mb-0">
                            You can leave a review once the appointment has been completed.
                        </div>
                    @else
                        @if($appointment->customer_rating)
                            <div class="alert alert-success d-flex align-items-start gap-2 mb-4" role="alert">
                                <i class="fas fa-check-circle mt-1"></i>
                                <div>
                                    <strong>Your review is saved.</strong>
                                    <div class="small">You can update it below if needed.</div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('appointment.review.store', $appointment->id) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Rating</label>
                                    <select name="customer_rating" class="form-select @error('customer_rating') is-invalid @enderror" required>
                                        <option value="">Choose a rating</option>
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}" @selected((int) old('customer_rating', $appointment->customer_rating) === $rating)>
                                                {{ $rating }} Star{{ $rating > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('customer_rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Written Review</label>
                                    <textarea name="customer_review" rows="5" class="form-control @error('customer_review') is-invalid @enderror" placeholder="Tell us about the consultation quality, advice, and experience.">{{ old('customer_review', $appointment->customer_review) }}</textarea>
                                    @error('customer_review')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn-agri btn-agri-primary" style="padding: 10px 22px;">
                                        <i class="fas fa-paper-plane me-2"></i>{{ $appointment->customer_rating ? 'Update Review' : 'Submit Review' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
