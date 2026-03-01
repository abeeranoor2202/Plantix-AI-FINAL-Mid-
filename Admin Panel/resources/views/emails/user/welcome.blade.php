@extends('emails.layouts.master', [
    'heroIcon'     => '👋',
    'heroTitle'    => 'Welcome to Plantix AI!',
    'heroSubtitle' => 'Your smart agriculture journey starts here.',
    'emailSubject' => 'Welcome to Plantix AI, ' . $user->name,
    'recipientEmail' => $user->email,
])

@section('content')
<p>Hi <strong>{{ $user->name }}</strong>,</p>
<p>Thank you for joining <strong>Plantix AI</strong> — the platform connecting farmers with expert agronomists, quality agri-inputs, and AI-powered crop insights.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Account Email</span><span class="info-value">{{ $user->email }}</span></div>
    <div class="info-row"><span class="info-label">Registered On</span><span class="info-value">{{ $user->created_at->format('d M Y') }}</span></div>
    <div class="info-row"><span class="info-label">Account Type</span><span class="info-value">{{ ucfirst($user->role) }}</span></div>
</div>

<p><strong>Here's what you can do on Plantix AI:</strong></p>
<ul class="step-list">
    <li><div class="step-num">1</div><div><strong>Shop Agri-Inputs</strong> — Browse quality seeds, fertilizers, and pesticides.</div></li>
    <li><div class="step-num">2</div><div><strong>Book Expert Consultations</strong> — Connect with certified agricultural experts.</div></li>
    <li><div class="step-num">3</div><div><strong>AI Disease Detection</strong> — Upload crop images and get instant disease reports.</div></li>
    <li><div class="step-num">4</div><div><strong>Join the Forum</strong> — Ask questions and share knowledge with the farming community.</div></li>
</ul>

@if(isset($verificationUrl))
<div class="alert-box alert-warning">
    <strong>⚠️ Please verify your email address</strong> to activate all features.
</div>
<div class="btn-wrap">
    <a href="{{ $verificationUrl }}" class="btn">✉️ Verify Email Address</a>
</div>
@else
<div class="btn-wrap">
    <a href="{{ route('home') }}" class="btn">🌿 Explore Plantix AI</a>
</div>
@endif

<hr class="divider">
<p style="font-size:13px; color:#888;">Need help getting started? Visit our <a href="{{ config('app.url') }}/contact" style="color:#2e7d32;">Support Centre</a> or reply to this email.</p>
@endsection
