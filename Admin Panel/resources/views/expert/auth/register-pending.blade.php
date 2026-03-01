<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Received — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3c34 0%, #2e7d32 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .pending-card {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%; max-width: 520px;
            padding: 3rem 2.5rem; text-align: center;
        }
        .icon-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: #e8f5e9; display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 2.5rem; color: #2e7d32; margin-bottom: 1.5rem;
        }
        .step-item {
            display: flex; align-items: flex-start; gap: .75rem;
            text-align: left; margin-bottom: 1rem;
        }
        .step-dot {
            flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%;
            background: #e8f5e9; color: #2e7d32; font-weight: 700;
            display: flex; align-items: center; justify-content: center; font-size: .8rem;
        }
        .btn-expert { background: #1b5e20; color: #fff; border: none; }
        .btn-expert:hover { background: #134418; color: #fff; }
    </style>
</head>
<body>
<div class="pending-card">

    <div class="icon-circle">
        <i class="bi bi-hourglass-split"></i>
    </div>

    <h4 class="fw-bold mb-2">Application Submitted!</h4>
    <p class="text-muted mb-4">
        Thank you for applying to join Plantix AI as an agricultural expert.
        @if(session('registered_email'))
            A confirmation has been sent to <strong>{{ session('registered_email') }}</strong>.
        @endif
    </p>

    {{-- Review steps --}}
    <div class="mb-4">
        <div class="step-item">
            <div class="step-dot">1</div>
            <div>
                <div class="fw-semibold">Application Received</div>
                <small class="text-muted">Your profile and credentials are in our queue.</small>
            </div>
        </div>
        <div class="step-item">
            <div class="step-dot">2</div>
            <div>
                <div class="fw-semibold">Admin Review</div>
                <small class="text-muted">Our team verifies your qualifications and experience (1–3 business days).</small>
            </div>
        </div>
        <div class="step-item">
            <div class="step-dot">3</div>
            <div>
                <div class="fw-semibold">Email Notification</div>
                <small class="text-muted">You'll receive an email once your application is approved or if we need more info.</small>
            </div>
        </div>
        <div class="step-item">
            <div class="step-dot">4</div>
            <div>
                <div class="fw-semibold">Activate Your Dashboard</div>
                <small class="text-muted">Sign in and start accepting consultations with farmers.</small>
            </div>
        </div>
    </div>

    <a href="{{ route('expert.login') }}" class="btn btn-expert w-100 py-2 fw-semibold mb-3">
        <i class="bi bi-box-arrow-in-right me-1"></i>Go to Expert Login
    </a>
    <a href="{{ route('home') }}" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to website
    </a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
