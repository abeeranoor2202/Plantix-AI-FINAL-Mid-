@extends('emails.layouts.master', [
    'heroIcon'      => '⭐',
    'heroTitle'     => 'New Review on Your Product',
    'heroSubtitle'  => $product->name,
    'emailSubject'  => 'New ' . $review->rating . '-star review on "' . $product->name . '"',
    'recipientEmail'=> $vendor->author->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $vendor->author->name ?? 'Vendor' }}</strong>,</p>
<p>A customer has left a review on your product <strong>{{ $product->name }}</strong>.</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Product</span>    <span class="info-value">{{ $product->name }}</span></div>
    <div class="info-row"><span class="info-label">Rating</span>     <span class="info-value">{{ str_repeat('⭐', (int)$review->rating) }} ({{ $review->rating }}/5)</span></div>
    <div class="info-row"><span class="info-label">Reviewer</span>   <span class="info-value">{{ $review->user->name ?? 'Anonymous' }}</span></div>
    <div class="info-row"><span class="info-label">Posted On</span>  <span class="info-value">{{ $review->created_at->format('d M Y') }}</span></div>
</div>

@if($review->comment)
<p><strong>Customer Comment:</strong></p>
<blockquote style="border-left:4px solid #c8e6c9; padding:12px 16px; background:#f9fbe7; border-radius:0 6px 6px 0; color:#444; font-style:italic; margin:0 0 20px;">
    {{ $review->comment }}
</blockquote>
@endif

@if($review->rating >= 4)
<div class="alert-box alert-success">🌟 Great rating! This helps build customer trust in your store.</div>
@elseif($review->rating <= 2)
<div class="alert-box alert-warning">⚠️ Low rating received. Consider responding to improve customer satisfaction.</div>
@endif

<div class="btn-wrap">
    <a href="{{ route('vendor.products.show', $product->id) }}" class="btn">👁️ View Product Reviews</a>
</div>
@endsection
