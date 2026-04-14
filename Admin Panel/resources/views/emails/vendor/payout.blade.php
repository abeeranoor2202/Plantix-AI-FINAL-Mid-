@extends('emails.layouts.master', [
    'heroIcon' => '💸',
    'heroTitle' => 'Vendor Payout Completed',
    'heroSubtitle' => 'Your Stripe transfer has been processed',
    'emailSubject' => 'Payout Completed — Vendor Earnings Settled',
    'recipientEmail' => $vendorUser->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $vendorUser->name ?? 'Vendor' }}</strong>,</p>

<p>Your payout has been processed successfully. Funds are on the way to your connected Stripe account.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Gross Amount</span><span class="info-value">₨{{ number_format($grossAmount, 2) }}</span></div>
    <div class="info-row"><span class="info-label">Platform Commission</span><span class="info-value">₨{{ number_format($commissionAmount, 2) }}</span></div>
    <div class="info-row"><span class="info-label">Net Payout</span><span class="info-value">₨{{ number_format($netAmount, 2) }}</span></div>
    @if(!empty($metadata['order_number']))
        <div class="info-row"><span class="info-label">Order</span><span class="info-value">#{{ $metadata['order_number'] }}</span></div>
    @endif
    <div class="info-row"><span class="info-label">Processed At</span><span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
</div>

<div class="alert-box alert-success">✅ Payout completed through Stripe Connect.</div>
@endsection