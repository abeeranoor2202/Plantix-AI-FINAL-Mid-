@extends('emails.layouts.master', [
    'heroIcon'      => '✅',
    'heroTitle'     => 'Appointment Complete',
    'heroSubtitle'  => 'Booking #' . $appointment->id,
    'emailSubject'  => 'Your Appointment with ' . ($expert->name ?? 'Your Expert') . ' is Complete — Plantix AI',
])

@section('content')
<p>Hi <strong>{{ $customer->name ?? 'Customer' }},</strong></p>

<p>Your consultation with <strong>{{ $expert->name ?? 'our expert' }}</strong> has been completed. We hope you found it helpful and gained valuable insights for your farm.</p>

{{-- Appointment Summary --}}
<div class="info-box">
    <div class="info-row">
        <span class="info-label">Expert</span>
        <span class="info-value">{{ $expert->name ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Appointment Date</span>
        <span class="info-value">{{ $appointment->scheduled_at?->format('d M Y, h:i A') ?? $appointment->appointment_date?->format('d M Y, h:i A') ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-success">Completed</span></span>
    </div>
    @if($appointment->topic)
    <div class="info-row">
        <span class="info-label">Topic Discussed</span>
        <span class="info-value">{{ $appointment->topic }}</span>
    </div>
    @endif
</div>

{{-- Expert Notes --}}
@if($expertNotes)
<div class="alert-box alert-info">
    <strong>📝 Expert Notes:</strong>
    <p style="margin-top: 8px;">{{ $expertNotes }}</p>
</div>
@endif

{{-- Review Invitation --}}
<div class="alert-box" style="border-left: 4px solid #ffc107; background: #fffaf0;">
    <strong>⭐ Share Your Experience:</strong>
    <p style="margin-top: 8px;">Help other farmers by leaving a review about your consultation. Your feedback helps us improve our services and helps experts grow their reputation.</p>
</div>

{{-- Action Buttons --}}
<div class="btn-wrap">
    <a href="{{ route('appointment.details', $appointment->id) }}" class="btn">📋 View Appointment</a>
</div>

<div class="btn-wrap" style="margin-top: -16px;">
    <a href="{{ route('appointment.details', $appointment->id) . '#review' }}" class="btn btn-secondary">⭐ Leave a Review</a>
</div>

{{-- Next Steps --}}
<div class="info-box" style="background: #f0f7ff; border-left: 4px solid #0d47a1;">
    <strong style="color: #0d47a1;">💡 What's Next?</strong>
    <ul style="margin-top: 8px; padding-left: 20px; color: #333;">
        <li>Implement the recommendations shared during your consultation</li>
        <li>Monitor your crops for any changes or issues</li>
        <li>Book a follow-up appointment if needed</li>
        <li>Check the Plantix AI app for crop health alerts and weather updates</li>
    </ul>
</div>

{{-- Book Another --}}
<div class="btn-wrap" style="margin-top: 24px;">
    <a href="{{ route('customer.experts') ?? url('/experts') }}" class="btn">📅 Book Another Appointment</a>
</div>

<p style="margin-top: 24px; padding: 12px; background: #f5f5f5; border-radius: 6px; color: #666; font-size: 13px;">
    <strong>❓ Questions?</strong> Reply to this email or visit your appointment details page. We're here to help!
</p>
@endsection
