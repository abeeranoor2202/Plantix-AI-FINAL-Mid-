@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-card">
    <div class="auth-brand">
        <i class="bi bi-envelope-check"></i>
        <h4>Forgot Password</h4>
        <p class="text-muted small">Enter your admin email to receive a reset link</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus
                       placeholder="admin@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-auth w-100 py-2">
            <i class="bi bi-send me-1"></i>Send Password Reset Link
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('admin.login') }}" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Back to login
        </a>
    </div>
</div>
@endsection
