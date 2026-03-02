@extends('emails.layouts.master', [
    'heroIcon'      => '📅',
    'heroTitle'     => 'Appointment Scheduled',
    'heroSubtitle'  => 'Booking #' . $appointment->id,
    'emailSubject'  => 'Appointment Confirmed with ' . ($expert->name ?? 'Your Expert') . ' — Plantix AI',
])

@section('content')
<p>Hi <strong>{{ $customer->name ?? 'Customer' }},</strong></p>

<p>Great news! Your appointment has been successfully scheduled with <strong>{{ $expert->name ?? 'our expert' }}</strong>. You're all set for your consultation.</p>

{{-- Appointment Details --}}
<div class="info-box">
    <div class="info-row">
        <span class="info-label">Expert</span>
        <span class="info-value">{{ $expert->name ?? '—' }}</span>
    </div>
    @if($expert->profile?->specializations?->first())
    <div class="info-row">
        <span class="info-label">Specialty</span>
        <span class="info-value">{{ $expert->profile->specializations->first()->name ?? '—' }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Date & Time</span>
        <span class="info-value">{{ $appointment->scheduled_at?->format('D, d M Y · h:i A') ?? $appointment->appointment_date?->format('D, d M Y · h:i A') ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Duration</span>
        <span class="info-value">{{ $appointment->expert?->consultation_duration_minutes ?? 30 }} minutes</span>
    </div>
    <div class="info-row">
        <span class="info-label">Fee</span>
        <span class="info-value">{{ config('plantix.currency_symbol', 'Rs') }}{{ number_format($appointment->amount ?? $appointment->expert?->consultation_price ?? 0, 0) }}</span>
    </div>
    @if($appointment->topic)
    <div class="info-row">
        <span class="info-label">Topic</span>
        <span class="info-value">{{ $appointment->topic }}</span>
    </div>
    @endif
</div>

{{-- Meeting Link (if available) --}}
@if($appointment->meeting_link)
<div class="alert-box alert-success">
    <strong>🎥 Meeting Link:</strong><br/>
    <a href="{{ $appointment->meeting_link }}" style="color: #0d47a1; font-weight: 600; word-break: break-all;">{{ $appointment->meeting_link }}</a>
</div>
@endif

{{-- Important Info --}}
<div class="alert-box alert-info">
    <strong>⏰ Remember:</strong>
    <ul style="margin-top: 8px; padding-left: 20px;">
        <li>Join 5 minutes before the scheduled time</li>
        <li>Have your questions ready for the expert</li>
        <li>A confirmation reminder will be sent 24 hours before</li>
    </ul>
</div>

{{-- Action Buttons --}}
<div class="btn-wrap">
    <a href="{{ route('customer.appointments.show', $appointment->id) }}" class="btn">📋 View Appointment Details</a>
</div>

@if($appointment->meeting_link)
<div class="btn-wrap" style="margin-top: -16px;">
    <a href="{{ $appointment->meeting_link }}" class="btn btn-secondary">🎥 Join Meeting</a>
</div>
@endif
@endsection
