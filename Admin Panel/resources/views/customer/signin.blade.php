@extends('layouts.frontend')

@section('title', 'Sign In | Plantix-AI')

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
                        <h3 class="fw-bold text-dark" style="font-size: 24px;">Welcome Back</h3>
                        <p class="text-muted" style="font-size: 15px;">Sign in to your Plantix-AI account</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 14px;">Sign in as</label>
                        <div class="d-flex gap-3">
                            <div class="flex-grow-1 position-relative">
                                <input type="radio" class="btn-check position-absolute opacity-0" name="signinRole" id="roleCustomer" value="customer" checked style="width:0;height:0;">
                                <label class="btn-agri w-100 text-center" for="roleCustomer" style="border: 2px solid var(--agri-primary); background: transparent; color: var(--agri-primary); cursor: pointer; padding: 10px;">Customer</label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="radio" class="btn-check position-absolute opacity-0" name="signinRole" id="roleExpert" value="expert" style="width:0;height:0;">
                                <label class="btn-agri w-100 text-center" for="roleExpert" style="border: 2px solid var(--agri-border); background: transparent; color: var(--agri-text-muted); cursor: pointer; padding: 10px;">Expert</label>
                            </div>
                        </div>
                    </div>

                    <form id="signin-form">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Email</label>
                            <input id="signinEmail" type="email" class="form-agri" placeholder="Enter your email" required data-label="Email address">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Password</label>
                            <input id="signinPassword" type="password" class="form-agri" placeholder="Enter your password" minlength="8" required data-label="Password (min 8 characters)">
                        </div>
                        <div class="mb-4 d-flex justify-content-end">
                            <a id="forgotLink" href="{{ route('password.forgot') }}" class="text-success text-decoration-none" style="font-size: 14px; font-weight: 500;">Forgot password?</a>
                        </div>
                        <button class="btn-agri btn-agri-primary w-100 mb-4" type="submit" style="font-size: 16px; padding: 12px;">Sign In</button>
                    </form>

                    <p class="text-center text-muted mb-0" style="font-size: 15px;">
                        Don't have an account? <a id="go-to-signup" href="{{ route('signup') }}" class="text-success text-decoration-none fw-bold">Create one</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input[type="radio"]:checked + label {
        background-color: var(--agri-primary-light) !important;
        border-color: var(--agri-primary) !important;
        color: var(--agri-primary-dark) !important;
    }
</style>
@endsection
