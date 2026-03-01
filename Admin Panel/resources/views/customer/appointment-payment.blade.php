@extends('layouts.frontend')

@section('title', 'Pay for Appointment | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4 justify-content-center">
            <div class="col-lg-7">

                <!-- Back button + Page title -->
                <div class="mb-4 d-flex align-items-center gap-3">
                    <a href="{{ route('appointment.details', $appointment->id) }}"
                       class="btn-agri btn-agri-outline d-flex align-items-center p-2 rounded-circle border-0"
                       style="width: 40px; height: 40px; justify-content: center; background: white; box-shadow: var(--agri-shadow-sm);">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="fw-bold mb-0 text-dark">Secure Payment</h2>
                </div>

                <!-- Appointment Summary -->
                <div class="card-agri p-0 border-0 overflow-hidden mb-4">
                    <div class="p-4 border-bottom" style="background: var(--agri-primary-light);">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-check" style="color: var(--agri-primary);"></i>
                            Appointment Summary
                        </h5>
                    </div>
                    <div class="p-4 bg-white">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">Expert</div>
                                <div class="fw-bold text-dark">
                                    {{ $appointment->expert->user->name ?? 'Any Available Expert' }}
                                </div>
                                @if($appointment->expert->specialization ?? false)
                                    <div class="small text-muted">{{ $appointment->expert->specialization }}</div>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">Date &amp; Time</div>
                                <div class="fw-bold text-dark">
                                    {{ $appointment->scheduled_at
                                        ? $appointment->scheduled_at->format('d M Y, h:i A')
                                        : '—' }}
                                </div>
                            </div>
                            @if($appointment->topic)
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">Topic</div>
                                <div class="text-dark">{{ $appointment->topic }}</div>
                            </div>
                            @endif
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">Consultation Fee</div>
                                <div class="fw-bold" style="color: var(--agri-primary); font-size: 18px;">
                                    PKR {{ number_format((float) $appointment->fee, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="card-agri p-0 border-0 overflow-hidden">
                    <div class="p-4 border-bottom" style="background: var(--agri-primary-light);">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-lock" style="color: var(--agri-primary);"></i>
                            Card Details
                            <span class="ms-auto badge bg-success-subtle text-success border border-success border-opacity-25 fw-normal" style="font-size: 12px;">
                                <i class="fas fa-shield-alt me-1"></i> Demo / Test Mode
                            </span>
                        </h5>
                    </div>

                    <div class="p-4 p-md-5 bg-white">

                        @if(session('error'))
                            <div class="alert alert-danger border-0 mb-4" style="border-radius: var(--agri-radius-sm);">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        <div class="alert border-0 mb-4"
                             style="background: #EFF6FF; border-radius: var(--agri-radius-sm); color: #1E40AF;">
                            <i class="fas fa-info-circle me-2"></i>
                            Test mode — use <strong>4242 4242 4242 4242</strong> for success,
                            any future expiry, any 3-digit CVC.
                        </div>

                        <form method="POST" action="{{ route('appointment.pay.process', $appointment->id) }}">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">
                                        Name on Card <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="card_name"
                                           class="form-agri @error('card_name') is-invalid @enderror"
                                           placeholder="Full name as on card"
                                           value="{{ old('card_name', auth('web')->user()->name) }}"
                                           required>
                                    @error('card_name')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">
                                        Card Number <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text"
                                               name="card_number"
                                               id="cardNumber"
                                               class="form-agri @error('card_number') is-invalid @enderror"
                                               placeholder="4242 4242 4242 4242"
                                               maxlength="19"
                                               required>
                                        <div class="position-absolute d-flex gap-1" style="right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                            <i class="fab fa-cc-visa text-muted" style="font-size: 20px;"></i>
                                            <i class="fab fa-cc-mastercard text-muted" style="font-size: 20px;"></i>
                                        </div>
                                    </div>
                                    @error('card_number')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">
                                        Expiry <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="card_exp"
                                           id="cardExp"
                                           class="form-agri @error('card_exp') is-invalid @enderror"
                                           placeholder="MM / YY"
                                           maxlength="7"
                                           required>
                                    @error('card_exp')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">
                                        CVC <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text"
                                               name="card_cvc"
                                               id="cardCvc"
                                               class="form-agri @error('card_cvc') is-invalid @enderror"
                                               placeholder="CVC"
                                               maxlength="4"
                                               required>
                                        <i class="fas fa-question-circle position-absolute text-muted"
                                           style="right: 14px; top: 50%; transform: translateY(-50%); cursor: help;"
                                           title="3 or 4 digits on the back of your card"></i>
                                    </div>
                                    @error('card_cvc')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <!-- Total -->
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3"
                                         style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                        <span class="fw-bold text-dark">Total Due</span>
                                        <span class="fw-bold" style="color: var(--agri-primary); font-size: 20px;">
                                            PKR {{ number_format((float) $appointment->fee, 2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 d-flex gap-3">
                                    <button type="submit" class="btn-agri btn-agri-primary flex-fill py-3 fw-bold">
                                        <i class="fas fa-lock me-2"></i>
                                        Pay PKR {{ number_format((float) $appointment->fee, 2) }}
                                    </button>
                                    <a href="{{ route('appointment.details', $appointment->id) }}"
                                       class="btn-agri btn-agri-outline py-3 px-4">
                                        Cancel
                                    </a>
                                </div>

                                <div class="col-12 text-center text-muted small">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secured by <strong>Stripe</strong> &nbsp;|&nbsp;
                                    <i class="fab fa-cc-visa me-1"></i>
                                    <i class="fab fa-cc-mastercard me-1"></i>
                                    <i class="fab fa-cc-amex"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-format card number with spaces
document.getElementById('cardNumber')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
});
// Auto-format expiry
document.getElementById('cardExp')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
    this.value = v;
});
// Digits only for CVC
document.getElementById('cardCvc')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').substring(0, 4);
});
</script>
@endpush
@endsection
