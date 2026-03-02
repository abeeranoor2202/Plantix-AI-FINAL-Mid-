@extends('emails.layouts.master', [
    'heroIcon'      => match($alertType) { 'disease' => '🦠', 'pest' => '🐛', 'nutrient' => '🥗', default => '⚠️' },
    'heroTitle'     => ucfirst($alertType) . ' Alert',
    'heroSubtitle'  => $cropName,
    'emailSubject'  => ucfirst($alertType) . ' Detected in Your ' . $cropName . ' — Plantix AI',
])

@section('content')
<p>Hi <strong>{{ $recipientName }},</strong></p>

<p>We've detected a <strong>{{ $alertType }}</strong> issue in your <strong>{{ $cropName }}</strong> crop. Please review the details below and take prompt action to minimize crop loss.</p>

{{-- Alert Details --}}
<div class="info-box">
    <div class="info-row">
        <span class="info-label">Issue</span>
        <span class="info-value">{{ $detectedIssue ?? ucfirst($alertType) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Severity</span>
        <span class="info-value">
            <span class="badge {{ match($severity) { 'critical' => 'badge-danger', 'high' => 'badge-warning', 'medium' => 'badge-info', default => 'badge-success' } }}">
                {{ ucfirst($severity) }}
            </span>
        </span>
    </div>
    <div class="info-row">
        <span class="info-label">Crop</span>
        <span class="info-value">{{ $cropName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Detected On</span>
        <span class="info-value">{{ now()->format('d M, Y · h:i A') }}</span>
    </div>
</div>

{{-- Recommendation --}}
@if($recommendation)
<div class="alert-box alert-info">
    <strong>📋 What to Do:</strong>
    <p style="margin-top: 8px;">{{ $recommendation }}</p>
</div>
@endif

{{-- Action Button --}}
<div class="btn-wrap">
    <a href="{{ route('customer.alerts') ?? url('/') }}" class="btn">🔍 View All Alerts</a>
</div>

{{-- Footer Note --}}
<p style="margin-top: 24px; padding: 12px; background: #f5f5f5; border-radius: 6px; color: #666; font-size: 13px;">
    <strong>💡 Tip:</strong> Early detection is key to preventing crop loss. Consult with an agricultural expert if you need personalized advice.
    <a href="{{ route('customer.experts') ?? url('/') }}" style="color: #2e7d32; font-weight: 600;">Book an Appointment →</a>
</p>
@endsection
