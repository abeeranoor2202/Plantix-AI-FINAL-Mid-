@extends('emails.layouts.master', [
    'heroIcon' => '💸',
    'heroTitle' => 'Expert Payout Completed',
    'heroSubtitle' => 'Your Stripe transfer has been processed',
    'emailSubject' => 'Payout Completed — Expert Earnings Settled',
    'recipientEmail' => $expertUser->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $expertUser->name ?? 'Expert' }}</strong>,</p>

<p>Your appointment earnings payout has been processed successfully to your connected Stripe account.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Gross Amount</span><span class="info-value">₨{{ number_format($grossAmount, 2) }}</span></div>
    <div class="info-row"><span class="info-label">Platform Commission</span><span class="info-value">₨{{ number_format($commissionAmount, 2) }}</span></div>
    <div class="info-row"><span class="info-label">Net Payout</span><span class="info-value">₨{{ number_format($netAmount, 2) }}</span></div>
    @if(!empty($metadata['appointment_id']))
        <div class="info-row"><span class="info-label">Appointment</span><span class="info-value">#{{ $metadata['appointment_id'] }}</span></div>
    @endif
    <div class="info-row"><span class="info-label">Processed At</span><span class="info-value">{{ now()->format('d M Y, h:i A') }}</span></div>
</div>

<div class="alert-box alert-success">✅ Payout completed through Stripe Connect.</div>
@endsection