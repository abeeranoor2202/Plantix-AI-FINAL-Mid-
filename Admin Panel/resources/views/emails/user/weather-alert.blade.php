@extends('emails.layouts.master', [
    'heroIcon'      => match($weatherType) { 'rainfall' => '🌧️', 'temperature' => '🌡️', 'humidity' => '💧', 'wind' => '💨', default => '🌤️' },
    'heroTitle'     => 'Weather ' . ucfirst($severity),
    'heroSubtitle'  => ucfirst($weatherType),
    'emailSubject'  => 'Weather ' . ucfirst($severity) . ': ' . ucfirst($weatherType) . ' — Plantix AI',
])

@section('content')
<p>Hi <strong>{{ $recipientName }},</strong></p>

<p>A <strong>{{ $severity }}</strong> has been issued regarding <strong>{{ $weatherType }}</strong> conditions that may impact your crops. Please review the details and take necessary precautions.</p>

{{-- Weather Details --}}
<div class="info-box">
    <div class="info-row">
        <span class="info-label">Alert Type</span>
        <span class="info-value">{{ ucfirst($weatherType) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Severity</span>
        <span class="info-value">
            <span class="badge {{ match($severity) { 'alert' => 'badge-danger', 'warning' => 'badge-warning', default => 'badge-info' } }}">
                {{ ucfirst($severity) }}
            </span>
        </span>
    </div>
    @if($affectedArea)
    <div class="info-row">
        <span class="info-label">Location</span>
        <span class="info-value">{{ $affectedArea }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Issued At</span>
        <span class="info-value">{{ now()->format('d M, Y · h:i A') }}</span>
    </div>
</div>

{{-- Description --}}
<div class="alert-box alert-warning">
    <strong>📢 Details:</strong>
    <p style="margin-top: 8px;">{{ $description }}</p>
</div>

{{-- Recommendation --}}
@if($recommendation)
<div class="alert-box alert-info">
    <strong>🛡️ What to Do:</strong>
    <p style="margin-top: 8px;">{{ $recommendation }}</p>
</div>
@endif

{{-- Action Button --}}
<div class="btn-wrap">
    <a href="{{ route('customer.weather') ?? url('/') }}" class="btn">🌤️ View Weather Forecast</a>
</div>

{{-- Footer Note --}}
<p style="margin-top: 24px; padding: 12px; background: #f5f5f5; border-radius: 6px; color: #666; font-size: 13px;">
    <strong>📌 Important:</strong> Stay updated with weather forecasts and adjust your farming activities accordingly. For expert guidance,
    <a href="{{ route('customer.experts') ?? url('/') }}" style="color: #2e7d32; font-weight: 600;">consult an expert →</a>
</p>
@endsection
