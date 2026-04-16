@extends('vendor.layouts.app')
@section('title', 'Review Detail')
@section('page-title', 'Review Detail')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('vendor.reviews.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;" title="Back to Reviews">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-chat-quote-fill me-2 text-primary"></i>Customer Review Details</h4>
                <span class="text-muted small fw-medium mt-1 d-block">View feedback left by a customer for your product</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm hover-card mb-4" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-info fs-5"></i>Review Information</h6>
            </div>
            <div class="card-body p-4 p-md-5">
                
                <div class="text-center mb-5 border-bottom pb-4">
                    <div class="d-inline-block bg-warning bg-opacity-10 rounded-pill px-4 py-2 border border-warning border-opacity-25 shadow-sm mb-3">
                        <div class="fs-4 text-warning">
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ ($i <= $review->rating) ? '-fill' : ' text-muted opacity-25' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="fw-bold text-dark fs-5">{{ $review->rating }}.0 out of 5 Stars</div>
                    <div class="text-muted small fw-medium mt-1">Submitted on <i class="bi bi-calendar3 mx-1"></i>{{ $review->created_at->format('d M Y, h:i A') }}</div>
                </div>

                <dl class="row mb-0 g-4">
                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Product</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('vendor.products.edit', $review->product_id) }}" class="text-decoration-none fw-bold text-primary d-flex align-items-center">
                            <i class="bi bi-box-seam me-2"></i>{{ $review->product->name ?? '—' }}
                        </a>
                    </dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Customer</dt>
                    <dd class="col-sm-8 fw-medium text-dark d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ substr($review->user->name ?? '?', 0, 1) }}
                        </div>
                        {{ $review->user->name ?? '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Order Reference</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('vendor.orders.show', $review->order_id ?? 0) }}" class="text-decoration-none fw-bold text-dark btn btn-sm btn-light border shadow-sm rounded-pill px-3">
                            <i class="bi bi-receipt me-1"></i>{{ $review->order->order_number ?? '—' }}
                        </a>
                    </dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Customer Comment</dt>
                    <dd class="col-sm-8">
                        <div class="bg-light p-4 rounded-4 border-start border-4 border-primary position-relative">
                            <i class="bi bi-quote position-absolute top-0 start-0 text-primary opacity-25" style="font-size: 3rem; transform: translate(-10px, -15px);"></i>
                            <span class="fst-italic text-dark position-relative z-1" style="font-size: 1.05rem; line-height: 1.6;">
                                {{ $review->comment ?: 'No written comment provided by the customer.' }}
                            </span>
                        </div>
                    </dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Review Photos</dt>
                    <dd class="col-sm-8">
                        @if(!empty($review->review_images))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($review->review_images as $image)
                                    <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ asset('storage/' . $image) }}" alt="Review image" style="width: 72px; height: 72px; object-fit: cover; border-radius: 12px; border: 1px solid #e5e7eb;">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">No photos attached to this review.</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold">Your Response</dt>
                    <dd class="col-sm-8">
                        @if($review->vendor_response)
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3 border border-primary border-opacity-25">
                                <div class="fw-medium text-dark">{{ $review->vendor_response }}</div>
                                @if($review->vendor_responded_at)
                                    <div class="small text-muted mt-2">Updated {{ $review->vendor_responded_at->format('d M Y, h:i A') }}</div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">No response posted yet.</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm hover-card" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-reply-fill me-2 text-primary fs-5"></i>Respond to Customer</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('vendor.reviews.respond', $review->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-muted fw-bold">Response</label>
                        <textarea name="vendor_response" rows="4" class="form-control bg-light border-0 @error('vendor_response') is-invalid @enderror" maxlength="2000" placeholder="Write a professional response to this review...">{{ old('vendor_response', $review->vendor_response) }}</textarea>
                        @error('vendor_response')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Save Response
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
