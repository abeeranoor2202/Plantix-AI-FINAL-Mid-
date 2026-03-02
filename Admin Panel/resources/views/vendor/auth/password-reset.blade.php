<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — Vendor Panel | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .card-box {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%; max-width: 440px;
            padding: 2.5rem;
        }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand i { font-size: 3rem; color: #2e7d32; }
        .brand h4 { font-weight: 700; margin-top: .5rem; }
        .btn-vendor { background: #2e7d32; color: #fff; border: none; }
        .btn-vendor:hover { background: #1b5e20; color: #fff; }
    </style>
</head>
<body>
<div class="card-box">
    <div class="brand">
        <i class="bi bi-shop"></i>
        <h4>Vendor Panel</h4>
        <p class="text-muted small">Set a new password</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email ?? '') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       required minlength="8" placeholder="Min. 8 characters">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat password">
            </div>
        </div>

        <button type="submit" class="btn btn-vendor w-100 py-2 fw-semibold">
            <i class="bi bi-check-circle me-2"></i>Reset Password
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('vendor.login') }}" class="text-decoration-none text-success small">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
