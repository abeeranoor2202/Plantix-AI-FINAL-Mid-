@extends('emails.layouts.master', [
    'heroIcon'       => '🔔',
    'heroTitle'      => $title,
    'heroSubtitle'   => config('app.name') . ' notification',
    'emailSubject'   => $title,
    'recipientEmail' => $recipientEmail ?? $user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $user->name ?? 'User' }}</strong>,</p>

<p>{{ $body }}</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Account</span><span class="info-value">{{ ucfirst($user->role ?? 'user') }}</span></div>
    <div class="info-row"><span class="info-label">Delivered At</span><span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Recipient</span><span class="info-value">{{ $recipientEmail ?? $user->email ?? '—' }}</span></div>
</div>

@if($actionUrl)
<div class="btn-wrap">
    <a href="{{ $actionUrl }}" class="btn">View Details</a>
</div>
@endif

<p style="font-size:13px;color:#667085;">If you did not expect this message, you can safely ignore it.</p>
@endsection