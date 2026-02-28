@extends('layouts.frontend')

@section('title', 'Sign Up | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px); background: #f8fafc; padding: 40px 0;">
    <div class="container-agri w-100">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card-agri" style="padding: 40px;">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Plantix-AI" style="height: 48px; margin-bottom: 24px;">
                        <h3 class="fw-bold text-dark" style="font-size: 24px;">Create your account</h3>
                        <p class="text-muted" style="font-size: 15px;">Join the Plantix-AI farming community</p>
                    </div>

                    <form id="signup-form">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Full Name</label>
                            <input id="signupName" type="text" class="form-agri" placeholder="Your full name" required data-label="Full name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Email</label>
                            <input id="signupEmail" type="email" class="form-agri" placeholder="Enter your email" required data-label="Email address">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Password</label>
                            <input id="signupPassword" type="password" class="form-agri" placeholder="Choose a password" minlength="8" required data-label="Password (min 8 characters)">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Phone (Optional)</label>
                            <input id="signupPhone" type="tel" class="form-agri" placeholder="Your phone number" data-label="Phone number">
                        </div>
                        <button class="btn-agri btn-agri-primary w-100 mb-4" type="submit" style="font-size: 16px; padding: 12px;">Create Account</button>
                    </form>

                    <p class="text-center text-muted mb-0" style="font-size: 15px;">
                        Already have an account? <a id="go-to-signin" href="{{ route('signin') }}" class="text-success text-decoration-none fw-bold">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
