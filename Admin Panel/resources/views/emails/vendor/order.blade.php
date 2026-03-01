@php
    $isNew = $type === 'new';
    $isCancelled = $order->status === 'cancelled';
    $isRefunded  = $order->status === 'refunded';
@endphp

@extends('emails.layouts.master', [
    'heroIcon'      => $isNew ? '🛒' : ($isCancelled ? '❌' : '💰'),
    'heroTitle'     => $isNew ? 'New Order Received!' : ($isCancelled ? 'Order Cancelled' : 'Order Update'),
    'heroSubtitle'  => 'Order #' . $order->order_number,
    'emailSubject'  => ($isNew ? 'New Order #' : 'Order Update #') . $order->order_number . ' — Plantix AI',
    'recipientEmail'=> $vendor->author->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $vendor->author->name ?? 'Vendor' }}</strong>,</p>

@if($isNew)
<p>You have received a new order on your store <strong>{{ $vendor->title }}</strong>. Please prepare it promptly.</p>
@elseif($isCancelled)
<p>Order <strong>#{{ $order->order_number }}</strong> has been <strong>cancelled</strong> by the customer.</p>
@elseif($isRefunded)
<p>A refund of <strong>₨{{ number_format($order->total, 0) }}</strong> has been issued for order <strong>#{{ $order->order_number }}</strong>.</p>
@else
<p>Order <strong>#{{ $order->order_number }}</strong> has been updated to status: <strong>{{ ucfirst(str_replace('_',' ',$order->status)) }}</strong>.</p>
@endif

<div class="info-box">
    <div class="info-row"><span class="info-label">Order Number</span>   <span class="info-value">#{{ $order->order_number }}</span></div>
    <div class="info-row"><span class="info-label">Customer</span>       <span class="info-value">{{ $order->user->name ?? 'Customer' }}</span></div>
    <div class="info-row"><span class="info-label">Order Total</span>    <span class="info-value">₨{{ number_format($order->total, 0) }}</span></div>
    <div class="info-row"><span class="info-label">Payment</span>        <span class="info-value">{{ ucwords(str_replace('_',' ', $order->payment_method ?? 'N/A')) }}</span></div>
    <div class="info-row"><span class="info-label">Placed At</span>      <span class="info-value">{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
    @if($order->delivery_address)<div class="info-row"><span class="info-label">Delivery To</span><span class="info-value" style="text-align:right;max-width:200px">{{ $order->delivery_address }}</span></div>@endif
</div>

{{-- Items --}}
@if($order->items && $order->items->count())
<table class="data-table">
    <thead><tr><th>Product</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th></tr></thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name ?? 'Product' }}<br><small style="color:#888">SKU: {{ $item->product->sku ?? '—' }}</small></td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:right">₨{{ number_format($item->price * $item->quantity, 0) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2" style="text-align:right">Total</td>
            <td style="text-align:right">₨{{ number_format($order->total, 0) }}</td>
        </tr>
    </tbody>
</table>
@endif

@if($isNew)
<div class="alert-box alert-warning">⏱️ Please process and ship this order promptly to maintain your seller rating.</div>
@endif

<div class="btn-wrap">
    <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn">📦 View Order in Panel</a>
</div>
@endsection
