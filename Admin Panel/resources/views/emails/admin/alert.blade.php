@php
    $configs = [
        'new_vendor'       => ['icon' => '🏪', 'title' => 'New Vendor Registration',     'badge' => 'badge-warning'],
        'new_expert'       => ['icon' => '👨🏫','title' => 'New Expert Application',       'badge' => 'badge-info'],
        'new_order'        => ['icon' => '🛒', 'title' => 'New Order Placed',             'badge' => 'badge-success'],
        'payment_failed'   => ['icon' => '⚠️',  'title' => 'Payment Failure Alert',        'badge' => 'badge-danger'],
        'refund_request'   => ['icon' => '↩️',  'title' => 'Refund Request Received',      'badge' => 'badge-warning'],
        'flagged_content'  => ['icon' => '🚩', 'title' => 'Forum Content Flagged',        'badge' => 'badge-danger'],
        'critical_error'   => ['icon' => '🔴', 'title' => 'Critical System Error',        'badge' => 'badge-danger'],
        'expert_suspended' => ['icon' => '⛔', 'title' => 'Expert Suspension Notice',     'badge' => 'badge-danger'],
        'register_expert'  => ['icon' => '📝', 'title' => 'New Expert Signup Pending',   'badge' => 'badge-info'],
    ];
    $meta = $configs[$alertType] ?? ['icon' => '🔔', 'title' => 'Admin Alert', 'badge' => 'badge-secondary'];
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $meta['icon'],
    'heroTitle'     => $meta['title'],
    'heroSubtitle'  => 'Admin Notification — ' . config('app.name'),
    'emailSubject'  => '[Admin] ' . $meta['title'] . ' — ' . config('app.name'),
    'recipientEmail'=> $adminEmail ?? config('mail.from.address'),
])

@section('content')
<p>Hi <strong>Admin</strong>,</p>
<p>{{ $headline ?? 'A system event requires your attention.' }}</p>

{{-- Alert severity --}}
@if($alertType === 'critical_error' || $alertType === 'payment_failed')
<div class="alert-box alert-danger">🚨 <strong>Immediate Action Required</strong> — Please review this alert immediately.</div>
@elseif($alertType === 'flagged_content')
<div class="alert-box alert-warning">🚩 Content has been flagged by the community and needs moderation.</div>
@endif

{{-- Dynamic payload table --}}
@if(!empty($details))
<div class="info-box">
    @foreach($details as $label => $value)
    <div class="info-row">
        <span class="info-label">{{ $label }}</span>
        <span class="info-value">{{ $value }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Extra context --}}
@if(!empty($extraHtml))
{!! $extraHtml !!}
@endif

@if(isset($actionUrl) && $actionUrl)
<div class="btn-wrap">
    <a href="{{ $actionUrl }}" class="btn @if($alertType === 'critical_error' || $alertType === 'payment_failed') btn-danger @endif">
        {{ $actionLabel ?? 'Review in Admin Panel' }}
    </a>
</div>
@else
<div class="btn-wrap">
    <a href="{{ route('admin.dashboard') }}" class="btn">🔧 Go to Admin Panel</a>
</div>
@endif

<hr class="divider">
<p style="font-size:12px;color:#aaa;text-align:center">This is an automated system notification. Do not reply to this email.</p>
@endsection
