@php
    $statusLabels = [
        'pending'         => ['label' => 'Order Received',    'icon' => '📦', 'badge' => 'badge-warning'],
        'confirmed'       => ['label' => 'Order Confirmed',   'icon' => '✅', 'badge' => 'badge-success'],
        'processing'      => ['label' => 'Being Prepared',    'icon' => '⚙️',  'badge' => 'badge-info'],
        'shipped'         => ['label' => 'Shipped',           'icon' => '🚚', 'badge' => 'badge-info'],
        'delivered'       => ['label' => 'Delivered',         'icon' => '🎉', 'badge' => 'badge-success'],
        'completed'       => ['label' => 'Completed',         'icon' => '⭐', 'badge' => 'badge-success'],
        'cancelled'       => ['label' => 'Cancelled',         'icon' => '❌', 'badge' => 'badge-danger'],
        'return_requested'=> ['label' => 'Return Requested',  'icon' => '↩️',  'badge' => 'badge-warning'],
        'returned'        => ['label' => 'Returned',          'icon' => '↩️',  'badge' => 'badge-secondary'],
        'refunded'        => ['label' => 'Refunded',          'icon' => '💰', 'badge' => 'badge-success'],
        'payment_failed'  => ['label' => 'Payment Failed',    'icon' => '⚠️',  'badge' => 'badge-danger'],
        'rejected'        => ['label' => 'Rejected',          'icon' => '🚫', 'badge' => 'badge-danger'],
    ];
    $meta   = $statusLabels[$order->status] ?? ['label' => ucfirst($order->status), 'icon' => '📋', 'badge' => 'badge-secondary'];
    $isNew  = $order->status === 'pending';
@endphp

@extends('emails.layouts.master', [
    'heroIcon'     => $meta['icon'],
    'heroTitle'    => $isNew ? 'Order Placed Successfully!' : 'Order Update — ' . $meta['label'],
    'heroSubtitle' => 'Order #' . $order->order_number,
    'emailSubject' => ($isNew ? 'Order Confirmed' : 'Order Update: ' . $meta['label']) . ' — #' . $order->order_number,
    'recipientEmail' => $order->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $order->user->name ?? 'Customer' }}</strong>,</p>

@if($isNew)
<p>Great news! We've received your order and it's being processed. Here's your order summary:</p>
@else
<p>Your order <strong>#{{ $order->order_number }}</strong> status has been updated to
    <span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span>.
</p>
@endif

{{-- Order details --}}
<div class="info-box">
    <div class="info-row"><span class="info-label">Order Number</span>    <span class="info-value">#{{ $order->order_number }}</span></div>
    <div class="info-row"><span class="info-label">Status</span>          <span class="info-value"><span class="badge {{ $meta['badge'] }}">{{ $meta['label'] }}</span></span></div>
    <div class="info-row"><span class="info-label">Order Date</span>      <span class="info-value">{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Payment Method</span>  <span class="info-value">{{ ucwords(str_replace('_',' ', $order->payment_method ?? 'N/A')) }}</span></div>
    @if($order->estimated_delivery)
    <div class="info-row"><span class="info-label">Est. Delivery</span>   <span class="info-value">{{ $order->estimated_delivery->format('d M Y') }}</span></div>
    @endif
</div>

{{-- Order items --}}
@if($order->items && $order->items->count())
<table class="data-table">
    <thead><tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th></tr></thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name ?? $item->product_name ?? 'Product' }}<br><small style="color:#888">SKU: {{ $item->product->sku ?? '—' }}</small></td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:right">₨{{ number_format($item->price * $item->quantity, 0) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2" style="text-align:right">Subtotal</td>
            <td style="text-align:right">₨{{ number_format($order->subtotal, 0) }}</td>
        </tr>
        @if($order->discount_amount > 0)
        <tr><td colspan="2" style="text-align:right; color:#2e7d32">Discount</td>
            <td style="text-align:right; color:#2e7d32">−₨{{ number_format($order->discount_amount, 0) }}</td></tr>
        @endif
        <tr><td colspan="2" style="text-align:right">Delivery Fee</td>
            <td style="text-align:right">₨{{ number_format($order->delivery_fee, 0) }}</td></tr>
        <tr class="total-row">
            <td colspan="2" style="text-align:right; font-size:16px">Total</td>
            <td style="text-align:right; font-size:16px">₨{{ number_format($order->total, 0) }}</td>
        </tr>
    </tbody>
</table>
@endif

{{-- Contextual messages --}}
@if($order->status === 'shipped')
<div class="alert-box alert-info">🚚 Your order is on the way! You'll receive another update when it is delivered.</div>
@elseif($order->status === 'delivered')
<div class="alert-box alert-success">🎉 Your order has been delivered. Enjoying your purchase? Leave a review!</div>
@elseif($order->status === 'cancelled')
<div class="alert-box alert-danger">Your order has been cancelled. If payment was made, a refund will be processed within 5–7 business days.</div>
@elseif($order->status === 'refunded')
<div class="alert-box alert-success">💰 Your refund of <strong>₨{{ number_format($order->total, 0) }}</strong> has been processed.</div>
@endif

@if(isset($notes) && $notes)
<div class="alert-box alert-info"><strong>Note:</strong> {{ $notes }}</div>
@endif

<div class="btn-wrap">
    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn">📦 View Order Details</a>
</div>

<hr class="divider">
<p style="font-size:13px;color:#888;">Questions about your order? <a href="{{ config('app.url') }}/contact" style="color:#2e7d32">Contact Support</a></p>
@endsection
