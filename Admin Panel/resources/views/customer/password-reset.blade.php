@extends('layouts.frontend')

@section('title', 'Reset Password | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div id="password-reset-page" class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px); background: #f8fafc; padding: 40px 0;">
    <div class="container-agri w-100">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card-agri" style="padding: 40px;">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Plantix-AI" style="height: 48px; margin-bottom: 24px;">
                        <h3 class="fw-bold text-dark" style="font-size: 24px;">Choose a new password</h3>
                        <p class="text-muted" style="font-size: 15px;">Please enter your new password below</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Email</label>
                            <input type="email" name="email" class="form-agri @error('email') is-invalid @enderror" placeholder="Enter your email" value="{{ old('email', request('email')) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">New Password</label>
                            <input type="password" name="password" class="form-agri @error('password') is-invalid @enderror" placeholder="Enter a new password (min 8 char)" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-agri" placeholder="Repeat new password" required>
                        </div>

                        <button class="btn-agri btn-agri-primary w-100 mb-4" type="submit" style="font-size: 16px; padding: 12px;">Reset Password</button>
                    </form>

                    <p class="text-center text-muted mb-0" style="font-size: 15px;">
                        Back to <a href="{{ route('signin') }}" class="text-success text-decoration-none fw-bold">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
