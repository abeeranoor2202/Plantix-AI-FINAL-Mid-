@extends('layouts.frontend')

@section('title', 'Forgot Password | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div id="password-forgot-page" class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 80px); background: #f8fafc; padding: 40px 0;">
    <div class="container-agri w-100">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card-agri" style="padding: 40px;">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Plantix-AI" style="height: 48px; margin-bottom: 24px;">
                        <h3 class="fw-bold text-dark" style="font-size: 24px;">Reset your password</h3>
                        <p class="text-muted" style="font-size: 15px;">Enter your email to receive a reset link</p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                            <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark" style="font-size: 14px;">Email</label>
                            <input type="email" name="email" class="form-agri @error('email') is-invalid @enderror" placeholder="Enter your registered email" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button class="btn-agri btn-agri-primary w-100 mb-4" type="submit" style="font-size: 16px; padding: 12px;">Send Reset Link</button>
                    </form>

                    <p class="text-center text-muted mb-0" style="font-size: 15px;">
                        Remembered it? <a href="{{ route('signin') }}" class="text-success text-decoration-none fw-bold">Back to Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
