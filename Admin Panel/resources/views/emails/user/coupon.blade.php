@php
    $icon  = $type === 'expiring' ? '⏰' : '🎁';
    $title = $type === 'expiring' ? 'Your Coupon is Expiring Soon!' : 'You Have a New Coupon!';
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $icon,
    'heroTitle'     => $title,
    'heroSubtitle'  => 'Coupon Code: ' . strtoupper($coupon->code),
    'emailSubject'  => $title . ' — ' . strtoupper($coupon->code),
    'recipientEmail'=> $user->email,
])

@section('content')
<p>Hi <strong>{{ $user->name }}</strong>,</p>

@if($type === 'expiring')
<p>Your coupon <strong>{{ strtoupper($coupon->code) }}</strong> is expiring on <strong>{{ $coupon->expires_at?->format('d M Y') }}</strong>. Use it before it's gone!</p>
@else
<p>A new coupon has been assigned to your account. Use it on your next order to save!</p>
@endif

<div class="info-box" style="text-align:center">
    <div style="font-size:28px; font-weight:800; letter-spacing:.1em; color:#2e7d32; padding:12px 0;">
        {{ strtoupper($coupon->code) }}
    </div>
    <div class="info-row"><span class="info-label">Discount</span>
        <span class="info-value">
            @if($coupon->type === 'percent') {{ $coupon->discount }}% OFF
            @else ₨{{ number_format($coupon->discount, 0) }} OFF
            @endif
        </span>
    </div>
    @if($coupon->min_order_amount)<div class="info-row"><span class="info-label">Min. Order</span><span class="info-value">₨{{ number_format($coupon->min_order_amount, 0) }}</span></div>@endif
    @if($coupon->expires_at)<div class="info-row"><span class="info-label">Valid Until</span><span class="info-value">{{ $coupon->expires_at->format('d M Y') }}</span></div>@endif
    @if($coupon->usage_limit)<div class="info-row"><span class="info-label">Usage Limit</span><span class="info-value">{{ $coupon->usage_limit }} uses</span></div>@endif
</div>

<div class="btn-wrap">
    <a href="{{ route('shop') }}" class="btn">🛒 Shop Now</a>
</div>

<p style="font-size:12px;color:#aaa;text-align:center">Coupon will be applied automatically at checkout when you enter the code.</p>
@endsection
