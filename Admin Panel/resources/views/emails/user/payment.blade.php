@php
    $success = $status === 'success';
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $success ? '💳' : '⚠️',
    'heroTitle'     => $success ? 'Payment Successful' : 'Payment Failed',
    'heroSubtitle'  => 'Order #' . ($order->order_number ?? ''),
    'emailSubject'  => $success ? 'Payment Confirmed — Order #' . ($order->order_number ?? '') : 'Payment Failed — Please Retry',
    'recipientEmail'=> $order->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $order->user->name ?? 'Customer' }}</strong>,</p>

@if($success)
<p>Your payment of <strong>₨{{ number_format($amount, 0) }}</strong> for order <strong>#{{ $order->order_number }}</strong> has been received successfully.</p>
<div class="alert-box alert-success">✅ Payment confirmed. Your order is now being processed.</div>
@else
<p>Unfortunately, your payment of <strong>₨{{ number_format($amount, 0) }}</strong> for order <strong>#{{ $order->order_number }}</strong> could not be processed.</p>
<div class="alert-box alert-danger">❌ Payment failed. Please try again or use a different payment method.</div>
@endif

<div class="info-box">
    <div class="info-row"><span class="info-label">Order Number</span>  <span class="info-value">#{{ $order->order_number }}</span></div>
    <div class="info-row"><span class="info-label">Amount</span>        <span class="info-value">₨{{ number_format($amount, 0) }}</span></div>
    @if(isset($transactionId))<div class="info-row"><span class="info-label">Transaction ID</span><span class="info-value">{{ $transactionId }}</span></div>@endif
    <div class="info-row"><span class="info-label">Date</span>          <span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
    @if(!$success && isset($failureReason))<div class="info-row"><span class="info-label">Reason</span><span class="info-value" style="color:#c62828">{{ $failureReason }}</span></div>@endif
</div>

@if($success)
<div class="btn-wrap">
    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn">📦 View Order</a>
</div>
@else
<div class="btn-wrap">
    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-danger">🔄 Retry Payment</a>
</div>
<p style="font-size:13px;color:#888;text-align:center">Having trouble? <a href="{{ config('app.url') }}/contact" style="color:#2e7d32">Contact Support</a></p>
@endif
@endsection
