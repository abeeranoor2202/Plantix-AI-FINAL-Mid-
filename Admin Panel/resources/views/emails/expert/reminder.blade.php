@extends('emails.layouts.master', [
    'heroIcon'      => '⏰',
    'heroTitle'     => 'Appointment Reminder',
    'heroSubtitle'  => ($hoursAway ?? 24) . 'h before your session',
    'emailSubject'  => 'Reminder: Consultation in ' . ($hoursAway ?? 24) . ' hour(s) — #' . $appointment->id,
    'recipientEmail'=> $recipientEmail ?? '',
])

@section('content')
@php $isExpert = $recipientRole === 'expert'; @endphp

<p>Hi <strong>{{ $recipientName }}</strong>,</p>
<p>This is a friendly reminder that you have a consultation session coming up in <strong>{{ $hoursAway ?? 24 }} hour(s)</strong>.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Booking ID</span>    <span class="info-value">#{{ $appointment->id }}</span></div>
    @if($isExpert)
    <div class="info-row"><span class="info-label">Farmer</span>        <span class="info-value">{{ $appointment->user->name ?? 'Customer' }}</span></div>
    @else
    <div class="info-row"><span class="info-label">Expert</span>        <span class="info-value">{{ $appointment->expert->user->name ?? 'Expert' }}</span></div>
    @endif
    <div class="info-row"><span class="info-label">Date &amp; Time</span><span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_at ?? $appointment->appointment_date)->format('D, d M Y · h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Duration</span>      <span class="info-value">{{ $appointment->expert->consultation_duration_minutes ?? 30 }} minutes</span></div>
    @if($appointment->topic)<div class="info-row"><span class="info-label">Topic</span><span class="info-value">{{ $appointment->topic }}</span></div>@endif
</div>

@if(isset($appointment->meeting_link) && $appointment->meeting_link)
<div class="alert-box alert-info">
    🎥 <strong>Meeting Link:</strong><br>
    <a href="{{ $appointment->meeting_link }}" style="color:#0d47a1; word-break:break-all;">{{ $appointment->meeting_link }}</a>
</div>
@endif

<div class="alert-box alert-success">⏰ Please be ready a few minutes before the scheduled time.</div>

<div class="btn-wrap">
    @if($isExpert)
        <a href="{{ route('expert.appointments.show', $appointment->id) }}" class="btn">📅 View Appointment</a>
    @else
        <a href="{{ route('customer.appointments.show', $appointment->id) }}" class="btn">📅 View Appointment</a>
    @endif
</div>
@endsection
