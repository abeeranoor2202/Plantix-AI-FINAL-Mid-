@php
    $config = [
        'pending_expert_approval' => ['icon' => '📬', 'title' => 'New Appointment Request',    'badge' => 'badge-warning', 'label' => 'Awaiting Your Approval'],
        'confirmed'               => ['icon' => '✅', 'title' => 'Appointment Confirmed',       'badge' => 'badge-success', 'label' => 'Confirmed'],
        'cancelled'               => ['icon' => '❌', 'title' => 'Appointment Cancelled',       'badge' => 'badge-danger',  'label' => 'Cancelled by Customer'],
        'completed'               => ['icon' => '⭐', 'title' => 'Session Marked Complete',     'badge' => 'badge-success', 'label' => 'Completed'],
        'reschedule_requested'    => ['icon' => '📅', 'title' => 'Customer Reschedule Request', 'badge' => 'badge-info',    'label' => 'Reschedule Requested'],
        'rescheduled'             => ['icon' => '🗓️', 'title' => 'Appointment Rescheduled',      'badge' => 'badge-info',    'label' => 'Rescheduled'],
    ];
    $meta = $config[$appointment->status] ?? ['icon' => '📋', 'title' => 'Appointment Update', 'badge' => 'badge-secondary', 'label' => ucfirst($appointment->status)];
    $isNew = $appointment->status === 'pending_expert_approval';
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $meta['icon'],
    'heroTitle'     => $meta['title'],
    'heroSubtitle'  => 'Booking #' . $appointment->id,
    'emailSubject'  => $meta['title'] . ' — Booking #' . $appointment->id,
    'recipientEmail'=> $appointment->expert->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $appointment->expert->user->name ?? 'Expert' }}</strong>,</p>

@if($isNew)
<p>You have a new consultation request from a farmer. Please review and respond within <strong>24 hours</strong>.</p>
@elseif($appointment->status === 'cancelled')
<p>A farmer has <strong>cancelled</strong> their appointment. The time slot is now available for rebooking.</p>
@elseif($appointment->status === 'reschedule_requested')
<p>A farmer has requested to <strong>reschedule</strong> their appointment. Please confirm the new time.</p>
@elseif($appointment->status === 'rescheduled')
<p>The appointment has been successfully <strong>rescheduled</strong> to the newly accepted time.</p>
@elseif($appointment->status === 'completed')
<p>The consultation session has been marked as <strong>completed</strong>.</p>
@endif

<div class="info-box">
    <div class="info-row"><span class="info-label">Booking ID</span>    <span class="info-value">#{{ $appointment->id }}</span></div>
    <div class="info-row"><span class="info-label">Farmer</span>        <span class="info-value">{{ $appointment->user->name ?? 'Customer' }}</span></div>
    <div class="info-row"><span class="info-label">Farmer Phone</span>  <span class="info-value">{{ $appointment->user->phone ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Scheduled At</span>  <span class="info-value">{{ \Carbon\Carbon::parse($appointment->scheduled_at ?? $appointment->appointment_date)->format('D, d M Y · h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Duration</span>      <span class="info-value">{{ $appointment->expert->consultation_duration_minutes ?? 30 }} minutes</span></div>
    <div class="info-row"><span class="info-label">Fee</span>           <span class="info-value">₨{{ number_format($appointment->amount ?? $appointment->expert->consultation_price ?? 0, 0) }}</span></div>
    @if($appointment->topic)<div class="info-row"><span class="info-label">Topic</span><span class="info-value">{{ $appointment->topic }}</span></div>@endif
    <div class="info-row"><span class="info-label">Status</span>        <span class="info-value"><span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span></span></div>
</div>

@if($appointment->notes)
<p><strong>Customer Notes:</strong></p>
<blockquote style="border-left:4px solid #c8e6c9; padding:12px 16px; background:#f9fbe7; border-radius:0 6px 6px 0; color:#444; font-style:italic; margin:0 0 20px;">{{ $appointment->notes }}</blockquote>
@endif

@if($isNew)
<div class="alert-box alert-warning">⏱️ Please respond within <strong>24 hours</strong>. Unanswered requests may affect your response rate.</div>
<div class="btn-wrap">
    <a href="{{ route('expert.appointments.show', $appointment->id) }}" class="btn">✅ Review &amp; Respond</a>
</div>
@else
<div class="btn-wrap">
    <a href="{{ route('expert.appointments.show', $appointment->id) }}" class="btn">📅 View Appointment</a>
</div>
@endif
@endsection
