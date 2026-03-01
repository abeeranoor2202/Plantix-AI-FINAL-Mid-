@extends('layouts.frontend')

@section('title', 'Secure Payment | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')

    <!-- Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border);">
        <div class="container-agri">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('checkout') }}" class="text-success text-decoration-none">Checkout</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Payment</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="py-5" style="background: var(--agri-bg); min-height: 80vh;">
        <div class="container-agri pb-5 mb-5">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 48px; height: 48px; background: white; color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: var(--agri-shadow-sm);">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 class="fw-bold mb-0 text-dark">Secure Payment</h2>
            </div>

            <div class="row justify-content-center g-4">

                <!-- Order Summary -->
                <div class="col-lg-4 order-lg-2">
                    <div class="card-agri p-0 border-0 overflow-hidden position-sticky" style="top: 20px;">
                        <div class="p-4 border-bottom" style="background: var(--agri-primary-light);">
                            <h5 class="fw-bold text-dark mb-0">Order #{{ $order->order_number ?? $order->id }}</h5>
                        </div>
                        <div class="p-4 bg-white">
                            <div class="d-flex flex-column gap-3 mb-4">
                                @foreach($order->items ?? [] as $item)
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-dark fw-medium">{{ $item->product->name ?? 'Product' }}
                                        <span class="text-muted">x{{ $item->quantity }}</span>
                                    </span>
                                    <span class="text-muted">PKR {{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex flex-column gap-2 pt-3 border-top small">
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Subtotal</span>
                                    <span>PKR {{ number_format($order->subtotal ?? 0, 2) }}</span>
                                </div>
                                @if(($order->discount ?? 0) > 0)
                                <div class="d-flex justify-content-between text-success">
                                    <span>Discount</span>
                                    <span>- PKR {{ number_format($order->discount, 2) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Shipping</span>
                                    <span>PKR {{ number_format($order->shipping_fee ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Tax</span>
                                    <span>PKR {{ number_format($order->tax ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-dark fs-6 pt-2 border-top">
                                    <span>Total</span>
                                    <span style="color: var(--agri-primary);">PKR {{ number_format($order->total ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Form -->
                <div class="col-lg-7 order-lg-1">
                    <div class="card-agri p-0 border-0 overflow-hidden">
                        <div class="p-4 border-bottom" style="background: var(--agri-primary-light);">
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fas fa-credit-card" style="color: var(--agri-primary);"></i>
                                Card Details
                                <span class="ms-auto badge bg-success-subtle text-success border border-success border-opacity-25 fw-normal" style="font-size: 12px;">
                                    <i class="fas fa-shield-alt me-1"></i> Test Mode
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
                                Test mode — use card <strong>4242 4242 4242 4242</strong>, any future expiry, any 3-digit CVC.
                            </div>

                            <form method="POST" action="{{ route('checkout.pay.confirm', $order->id) }}">
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
                                               value="{{ old('card_name', auth('web')->user()->name ?? '') }}"
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
                                                <i class="fab fa-cc-visa text-muted" style="font-size: 22px;"></i>
                                                <i class="fab fa-cc-mastercard text-muted" style="font-size: 22px;"></i>
                                            </div>
                                        </div>
                                        @error('card_number')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold text-dark" style="font-size: 14px;">
                                            Expiry Date <span class="text-danger">*</span>
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
                                                   placeholder="123"
                                                   maxlength="4"
                                                   required>
                                            <i class="fas fa-question-circle position-absolute text-muted"
                                               style="right: 14px; top: 50%; transform: translateY(-50%); cursor: help;"
                                               title="3 or 4 digit security code on the back of your card"></i>
                                        </div>
                                        @error('card_cvc')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                    </div>

                                    <!-- Total -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3"
                                             style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                                            <span class="fw-bold text-dark fs-6">Total Due</span>
                                            <span class="fw-bold fs-5" style="color: var(--agri-primary);">
                                                PKR {{ number_format($order->total ?? 0, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-12 d-flex gap-3">
                                        <button type="submit" class="btn-agri btn-agri-primary flex-fill py-3 fw-bold" style="font-size: 16px;">
                                            <i class="fas fa-lock me-2"></i>
                                            Pay PKR {{ number_format($order->total ?? 0, 2) }}
                                        </button>
                                        <a href="{{ route('checkout') }}" class="btn-agri btn-agri-outline py-3 px-4">
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
// ── Auto-formatters ─────────────────────────────────────────────────────────
document.getElementById('cardNumber')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
});
document.getElementById('cardExp')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
    this.value = v;
});
document.getElementById('cardCvc')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').substring(0, 4);
});

// ── Client-side submit guard ─────────────────────────────────────────────────
document.querySelector('form')?.addEventListener('submit', function (e) {
    let ok = true;

    function err(id, msg) {
        const el = document.getElementById(id);
        el.classList.add('is-invalid');
        let fb = el.parentElement.querySelector('.invalid-feedback') ||
                 el.closest('.col-12, .col-6')?.querySelector('.text-danger');
        if (!fb) { fb = document.createElement('div'); fb.className = 'text-danger mt-1 small'; el.after(fb); }
        fb.textContent = msg;
        ok = false;
    }
    function clear(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    }

    ['cardNumber','cardExp','cardCvc'].forEach(clear);

    const num = document.getElementById('cardNumber').value;
    if (!/^\d{4} \d{4} \d{4} \d{4}$/.test(num))
        err('cardNumber', 'Enter a valid 16-digit card number.');

    const exp = document.getElementById('cardExp').value;
    if (!/^\d{2} \/ \d{2}$/.test(exp)) {
        err('cardExp', 'Use MM / YY format.');
    } else {
        const [m, y] = exp.split(' / ').map(Number);
        const now = new Date();
        const expDate = new Date(2000 + y, m, 0); // last day of that month
        if (m < 1 || m > 12) err('cardExp', 'Invalid month.');
        else if (expDate < now) err('cardExp', 'This card has expired.');
    }

    const cvc = document.getElementById('cardCvc').value;
    if (!/^\d{3,4}$/.test(cvc))
        err('cardCvc', 'CVC must be 3 or 4 digits.');

    if (!ok) e.preventDefault();
});
</script>
@endpush

@endsection
