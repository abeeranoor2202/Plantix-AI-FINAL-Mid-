@extends('layouts.frontend')

@section('title', 'Payment Failed | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row justify-content-center pt-4">
            <div class="col-lg-7">
                <div class="card-agri p-0 border-0 overflow-hidden text-center">

                    <!-- Failed header -->
                    <div class="p-5" style="background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);">
                        <div style="width: 80px; height: 80px; background: #EF4444; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-times text-white" style="font-size: 36px;"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Payment Failed</h2>
                        <p class="text-muted mb-0">We were unable to process your payment. No charge was made.</p>
                    </div>

                    <div class="p-4 p-md-5 bg-white">

                        <div class="alert border-0 text-start mb-4"
                             style="background: #FFF7ED; border-radius: var(--agri-radius-sm); color: #92400E;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Common reasons: insufficient funds, incorrect card details, or card declined by your bank.
                            Please try again with a different payment method.
                        </div>

                        <div class="d-flex gap-3 flex-wrap justify-content-center">
                            <a href="{{ route('orders') }}"
                               class="btn-agri btn-agri-primary text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-redo me-2"></i> Retry Payment
                            </a>
                            <a href="{{ route('appointments') }}"
                               class="btn-agri btn-agri-outline text-decoration-none"
                               style="padding: 12px 28px; font-size: 15px;">
                                <i class="fas fa-calendar-alt me-2"></i> My Appointments
                            </a>
                        </div>

                        <p class="text-muted small mt-4 mb-0">
                            Need help? <a href="{{ route('contact') }}" class="text-success text-decoration-none">Contact support</a>.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
