@extends('emails.layouts.master', [
    'heroIcon'      => '📉',
    'heroTitle'     => 'Low Stock Alert',
    'heroSubtitle'  => $product->name,
    'emailSubject'  => 'Low Stock Alert: ' . $product->name . ' — Action Required',
    'recipientEmail'=> $vendor->author->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $vendor->author->name ?? 'Vendor' }}</strong>,</p>
<p>One of your products on <strong>{{ $vendor->title }}</strong> is running low on stock. Please restock to avoid missing orders.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Product</span>         <span class="info-value">{{ $product->name }}</span></div>
    <div class="info-row"><span class="info-label">SKU</span>             <span class="info-value">{{ $product->sku ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Current Stock</span>   <span class="info-value" style="color:#c62828;font-weight:700">{{ $currentStock }} units remaining</span></div>
    <div class="info-row"><span class="info-label">Alert Threshold</span> <span class="info-value">{{ $threshold ?? 5 }} units</span></div>
    <div class="info-row"><span class="info-label">Category</span>        <span class="info-value">{{ $product->category->name ?? '—' }}</span></div>
</div>

<div class="alert-box alert-warning">⚠️ Products with zero stock are automatically hidden from customers. Restock now to keep your listing active.</div>

<div class="btn-wrap">
    <a href="{{ route('vendor.products.edit', $product->id) }}" class="btn btn-warning">📦 Restock Product</a>
</div>
@endsection
