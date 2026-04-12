@php
    $config = [
        'pending_expert_approval' => ['icon' => '⏳', 'title' => 'Appointment Request Sent',    'badge' => 'badge-warning', 'label' => 'Awaiting Expert Review'],
        'confirmed'               => ['icon' => '✅', 'title' => 'Appointment Confirmed!',       'badge' => 'badge-success', 'label' => 'Confirmed'],
        'rejected'                => ['icon' => '❌', 'title' => 'Appointment Rejected',         'badge' => 'badge-danger',  'label' => 'Rejected'],
        'cancelled'               => ['icon' => '🚫', 'title' => 'Appointment Cancelled',        'badge' => 'badge-danger',  'label' => 'Cancelled'],
        'completed'               => ['icon' => '⭐', 'title' => 'Appointment Completed',        'badge' => 'badge-success', 'label' => 'Completed'],
        'reschedule_requested'    => ['icon' => '📅', 'title' => 'Reschedule Requested',         'badge' => 'badge-info',    'label' => 'Reschedule Pending'],
        'payment_failed'          => ['icon' => '⚠️',  'title' => 'Appointment Payment Failed',  'badge' => 'badge-danger',  'label' => 'Payment Failed'],
    ];
    $meta = $config[$appointment->status] ?? ['icon' => '📋', 'title' => 'Appointment Update', 'badge' => 'badge-secondary', 'label' => ucfirst($appointment->status)];
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $meta['icon'],
    'heroTitle'     => $meta['title'],
    'heroSubtitle'  => 'Booking #' . $appointment->id,
    'emailSubject'  => $meta['title'] . ' — Plantix AI',
    'recipientEmail'=> $appointment->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $appointment->user->name ?? 'Customer' }}</strong>,</p>

@if($appointment->status === 'confirmed')
<p>Your appointment with <strong>{{ $appointment->expert->user->name ?? 'Expert' }}</strong> has been <strong>confirmed</strong>. See you soon!</p>
@elseif($appointment->status === 'pending_expert_approval')
<p>Your consultation request has been sent to <strong>{{ $appointment->expert->user->name ?? 'our expert' }}</strong>. You will be notified once they confirm.</p>
@elseif($appointment->status === 'rejected')
<p>Unfortunately, your appointment request has been declined.</p>
@elseif($appointment->status === 'cancelled')
<p>Your appointment has been cancelled. @if($appointment->payment_status === 'paid') A refund will be processed within 5–7 business days. @endif</p>
@elseif($appointment->status === 'completed')
<p>Your consultation with <strong>{{ $appointment->expert->user->name ?? 'our expert' }}</strong> has been marked as completed. We hope it was helpful!</p>
@elseif($appointment->status === 'reschedule_requested')
<p>A reschedule has been requested for your appointment. The expert will confirm the new date shortly.</p>
@endif

{{-- Appointment details --}}
<div class="info-box">
    <div class="info-row"><span class="info-label">Booking ID</span>    <span class="info-value">#{{ $appointment->id }}</span></div>
    <div class="info-row"><span class="info-label">Expert</span>        <span class="info-value">{{ $appointment->expert->user->name ?? 'Expert' }}</span></div>
    <div class="info-row"><span class="info-label">Specialty</span>     <span class="info-value">{{ $appointment->expert->specialty ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Scheduled At</span>  <span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_at ?? $appointment->appointment_date)->format('D, d M Y · h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Duration</span>      <span class="info-value">{{ $appointment->expert->consultation_duration_minutes ?? 30 }} minutes</span></div>
    <div class="info-row"><span class="info-label">Fee</span>           <span class="info-value">₨{{ number_format($appointment->amount ?? $appointment->expert->consultation_price ?? 0, 0) }}</span></div>
    <div class="info-row"><span class="info-label">Status</span>        <span class="info-value"><span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span></span></div>
    @if($appointment->topic)<div class="info-row"><span class="info-label">Topic</span><span class="info-value">{{ $appointment->topic }}</span></div>@endif
</div>

@if($appointment->status === 'confirmed' && isset($appointment->meeting_link) && $appointment->meeting_link)
<div class="alert-box alert-info">🎥 <strong>Meeting Link:</strong> <a href="{{ $appointment->meeting_link }}" style="color:#0d47a1">{{ $appointment->meeting_link }}</a></div>
@endif

@if($appointment->status === 'rejected' && $appointment->reject_reason)
<div class="alert-box alert-danger"><strong>Reason:</strong> {{ $appointment->reject_reason }}</div>
@endif

@if(isset($note) && $note)
<div class="alert-box alert-info">{{ $note }}</div>
@endif

<div class="btn-wrap">
    <a href="{{ route('appointment.details', $appointment->id) }}" class="btn">📅 View Appointment</a>
</div>

@if($appointment->status === 'completed')
<div class="btn-wrap" style="margin-top:-16px">
    <a href="{{ route('appointment.details', $appointment->id) . '#review' }}" class="btn btn-secondary">⭐ Leave a Review</a>
</div>
@endif
@endsection
