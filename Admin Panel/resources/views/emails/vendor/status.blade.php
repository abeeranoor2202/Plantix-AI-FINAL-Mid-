@php
    $config = [
        'approved'  => ['icon' => '✅', 'title' => 'Store Account Approved!',   'badge' => 'badge-success', 'alert' => 'alert-success'],
        'rejected'  => ['icon' => '❌', 'title' => 'Store Application Rejected', 'badge' => 'badge-danger',  'alert' => 'alert-danger'],
        'suspended' => ['icon' => '⛔', 'title' => 'Store Account Suspended',    'badge' => 'badge-danger',  'alert' => 'alert-danger'],
        'active'    => ['icon' => '🔓', 'title' => 'Store Account Reactivated',  'badge' => 'badge-success', 'alert' => 'alert-success'],
    ];
    $meta = $config[$status] ?? ['icon' => '📋', 'title' => 'Account Update', 'badge' => 'badge-secondary', 'alert' => 'alert-info'];
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $meta['icon'],
    'heroTitle'     => $meta['title'],
    'heroSubtitle'  => $vendor->title ?? 'Your Store',
    'emailSubject'  => $meta['title'] . ' — Plantix AI',
    'recipientEmail'=> $vendor->author->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $vendor->author->name ?? 'Vendor' }}</strong>,</p>

@if($status === 'approved')
<p>Congratulations! Your store <strong>{{ $vendor->title }}</strong> has been <strong>approved</strong> on Plantix AI. You can now start listing products and accepting orders.</p>
<div class="alert-box alert-success">🎉 Your store is live! Head to your Vendor Panel to set up your products.</div>
<ul class="step-list">
    <li><div class="step-num">1</div><div>Log in to your <strong>Vendor Panel</strong></div></li>
    <li><div class="step-num">2</div><div>Add your <strong>products</strong> with images and pricing</div></li>
    <li><div class="step-num">3</div><div>Set your <strong>delivery settings</strong> and availability</div></li>
    <li><div class="step-num">4</div><div>Start receiving <strong>orders</strong> from farmers!</div></li>
</ul>
@elseif($status === 'rejected')
<p>We're sorry, but your store application for <strong>{{ $vendor->title }}</strong> has been <strong>declined</strong> at this time.</p>
@if(isset($reason) && $reason)<div class="alert-box alert-danger"><strong>Reason:</strong> {{ $reason }}</div>@endif
<p>You may address the issues and <a href="{{ route('home') }}" style="color:#2e7d32">contact our support team</a> to reapply.</p>
@elseif($status === 'suspended')
<p>Your store <strong>{{ $vendor->title }}</strong> has been temporarily <strong>suspended</strong>. Active product listings are hidden from customers until the suspension is lifted.</p>
@if(isset($reason) && $reason)<div class="alert-box alert-danger"><strong>Reason:</strong> {{ $reason }}</div>@endif
<p>Please <a href="{{ config('app.url') }}/contact" style="color:#2e7d32">contact support</a> to resolve this issue.</p>
@elseif($status === 'active')
<p>Great news! Your store <strong>{{ $vendor->title }}</strong> has been <strong>reactivated</strong>. Your listings are now live again.</p>
<div class="alert-box alert-success">✅ Your store is active and accepting orders.</div>
@endif

<div class="info-box">
    <div class="info-row"><span class="info-label">Store Name</span>  <span class="info-value">{{ $vendor->title }}</span></div>
    <div class="info-row"><span class="info-label">Status</span>      <span class="info-value"><span class="badge {{ $meta['badge'] }}">{{ ucfirst($status) }}</span></span></div>
    <div class="info-row"><span class="info-label">Updated On</span>  <span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
</div>

@if($status === 'approved' || $status === 'active')
<div class="btn-wrap">
    <a href="{{ route('vendor.dashboard') }}" class="btn">🏪 Go to Vendor Panel</a>
</div>
@endif
@endsection
