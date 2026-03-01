<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expert Login — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%; max-width: 440px;
            padding: 2.5rem;
        }
        .login-card .brand { text-align: center; margin-bottom: 2rem; }
        .login-card .brand i { font-size: 3rem; color: #2e7d32; }
        .login-card .brand h4 { font-weight: 700; margin-top: .5rem; }
        .btn-expert { background: #1b5e20; color: #fff; border: none; }
        .btn-expert:hover { background: #134418; color: #fff; }
        .badge-expert {
            background: #e8f5e9; color: #2e7d32;
            border: 1px solid #c8e6c9; border-radius: .5rem;
            font-size: .75rem; padding: .3rem .7rem; display: inline-block;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <i class="bi bi-person-badge"></i>
        <h4>Expert &amp; Agency Panel</h4>
        <span class="badge-expert"><i class="bi bi-shield-check me-1"></i>Verified Agricultural Experts</span>
        <p class="text-muted small">Sign in to manage your consultations</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('expert.login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus
                       placeholder="expert@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('expert.password.request') }}" class="text-success small">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-expert w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
        </button>
    </form>

    <div class="text-center mt-3">
        <span class="text-muted small">Not registered yet?</span>
        <a href="{{ route('expert.register') }}" class="text-success small ms-1 fw-semibold">Apply as Expert</a>
    </div>
    <div class="text-center mt-2">
        <a href="{{ route('home') }}" class="text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Back to website
        </a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
