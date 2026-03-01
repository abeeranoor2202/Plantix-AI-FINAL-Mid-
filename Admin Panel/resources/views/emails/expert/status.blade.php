@php
    $config = [
        'approved'  => ['icon' => '✅', 'title' => 'Expert Application Approved!', 'badge' => 'badge-success'],
        'rejected'  => ['icon' => '❌', 'title' => 'Application Not Accepted',     'badge' => 'badge-danger'],
        'suspended' => ['icon' => '⛔', 'title' => 'Expert Account Suspended',     'badge' => 'badge-danger'],
        'inactive'  => ['icon' => '😴', 'title' => 'Account Set to Inactive',      'badge' => 'badge-secondary'],
        'approved_again' => ['icon' => '🔓', 'title' => 'Account Reactivated',     'badge' => 'badge-success'],
    ];
    $meta = $config[$status] ?? ['icon' => '📋', 'title' => 'Account Update', 'badge' => 'badge-secondary'];
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $meta['icon'],
    'heroTitle'     => $meta['title'],
    'heroSubtitle'  => 'Expert & Agency Panel',
    'emailSubject'  => $meta['title'] . ' — Plantix AI Expert Portal',
    'recipientEmail'=> $expert->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $expert->user->name ?? 'Expert' }}</strong>,</p>

@if($status === 'approved')
<p>Congratulations! Your expert application has been <strong>reviewed and approved</strong> by our team. You are now a verified agricultural expert on Plantix AI.</p>
<div class="alert-box alert-success">🎉 Your expert profile is now live and visible to farmers seeking consultations!</div>
<ul class="step-list">
    <li><div class="step-num">1</div><div>Log in to the <strong>Expert Panel</strong> using your credentials</div></li>
    <li><div class="step-num">2</div><div>Complete your <strong>profile</strong> and set your availability schedule</div></li>
    <li><div class="step-num">3</div><div>Set your <strong>consultation fee</strong> and duration</div></li>
    <li><div class="step-num">4</div><div>Start <strong>accepting appointments</strong> from farmers!</div></li>
</ul>
@elseif($status === 'rejected')
<p>After reviewing your application, we are unable to approve your expert account at this time.</p>
@if(isset($reason) && $reason)<div class="alert-box alert-danger"><strong>Reason:</strong> {{ $reason }}</div>@endif
<p>You are welcome to address the concerns and <a href="{{ config('app.url') }}/contact" style="color:#2e7d32">contact our team</a> for guidance on reapplying.</p>
@elseif($status === 'suspended')
<p>Your expert account has been <strong>temporarily suspended</strong>. You will not be able to accept new appointments during this period. Existing appointments are under review.</p>
@if(isset($reason) && $reason)<div class="alert-box alert-danger"><strong>Reason:</strong> {{ $reason }}</div>@endif
<p>Please <a href="{{ config('app.url') }}/contact" style="color:#2e7d32">contact support</a> immediately to resolve this issue.</p>
@elseif($status === 'inactive')
<p>Your expert account has been set to <strong>inactive</strong>. Your profile will not appear in search results. You can reactivate it from your Expert Panel.</p>
@elseif($status === 'approved_again')
<p>Your expert account has been <strong>reactivated</strong>. You can now accept appointments again.</p>
<div class="alert-box alert-success">✅ Your profile is live again.</div>
@endif

<div class="info-box">
    <div class="info-row"><span class="info-label">Name</span>       <span class="info-value">{{ $expert->user->name }}</span></div>
    <div class="info-row"><span class="info-label">Specialty</span>  <span class="info-value">{{ $expert->specialty ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Status</span>     <span class="info-value"><span class="badge {{ $meta['badge'] }}">{{ ucfirst($status) }}</span></span></div>
    <div class="info-row"><span class="info-label">Updated</span>    <span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
</div>

@if(in_array($status, ['approved', 'approved_again']))
<div class="btn-wrap">
    <a href="{{ route('expert.login') }}" class="btn">🌿 Sign In to Expert Panel</a>
</div>
@endif
@endsection
