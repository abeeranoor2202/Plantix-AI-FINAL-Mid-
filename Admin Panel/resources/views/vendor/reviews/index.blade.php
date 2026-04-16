@extends('vendor.layouts.app')
@section('title', 'Product Reviews')
@section('page-title', 'Product Reviews & Ratings')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-star-half me-2 text-warning"></i>Product Reviews</h4>
        <span class="text-muted small fw-medium mt-1 d-block">Monitor customer feedback and ratings for your products</span>
    </div>
</div>

{{-- Stats row --}}
<div class="row g-3 mb-4">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4 hover-card h-100" style="border-radius:16px;">
            <div class="d-flex flex-column justify-content-center h-100">
                <h5 class="text-muted small text-uppercase fw-bold mb-3">Average Rating</h5>
                <div class="display-3 fw-bold text-dark mb-2 lh-1">{{ number_format($avgRating ?? 0, 1) }}</div>
                <div class="mb-2 fs-5">
                    @for($i=1;$i<=5;$i++)
                        <i class="bi bi-star{{ ($i <= round($avgRating ?? 0)) ? '-fill text-warning' : ' text-muted opacity-25' }}"></i>
                    @endfor
                </div>
                <div class="text-muted small fw-medium">Based on {{ $reviews->total() }} reviews</div>
            </div>
        </div>
    </div>
    <div class="col-md-7 col-lg-8">
        <div class="card border-0 shadow-sm p-4 hover-card h-100" style="border-radius:16px;">
            <h6 class="mb-4 fw-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Rating Breakdown</h6>
            <div class="d-flex flex-column gap-2 justify-content-center h-100">
                @for($r=5;$r>=1;$r--)
                @php $count = $ratingCounts[$r] ?? 0; $total = $reviews->total() ?: 1; @endphp
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-1 text-muted fw-bold small" style="min-width:30px">
                        {{ $r }} <i class="bi bi-star-fill text-warning" style="font-size:0.75rem;"></i>
                    </div>
                    <div class="progress flex-grow-1 rounded-pill bg-light border" style="height:10px;">
                        <div class="progress-bar bg-warning rounded-pill" style="width:{{ round($count/$total*100) }}%"></div>
                    </div>
                    <span class="small text-muted fw-medium text-end" style="min-width:30px">{{ $count }}</span>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm hover-card mb-4" style="border-radius:16px;">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary fs-5"></i>Recent Reviews</h6>
        {{-- Filters --}}
        <form method="GET" class="d-flex align-items-center gap-2 m-0">
            <label class="text-muted small fw-bold text-uppercase text-nowrap mb-0 d-none d-sm-block">Filter:</label>
            <select name="rating" class="form-select border-0 bg-light rounded-pill px-3 py-2 fw-medium shadow-sm w-auto">
                <option value="">All Ratings</option>
                @foreach([5,4,3,2,1] as $r)
                    <option value="{{ $r }}" @selected(request('rating') == $r)>{{ $r }} Stars</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center">
                <i class="bi bi-funnel-fill d-sm-none"></i><span class="d-none d-sm-inline">Apply</span>
            </button>
            @if(request()->hasAny(['rating','product_id']))
                <a href="{{ route('vendor.reviews.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-bold shadow-sm" title="Clear Filters">
                    <i class="bi bi-x-circle d-sm-none"></i><span class="d-none d-sm-inline">Clear</span>
                </a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        @if($reviews->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-star-half fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No reviews found</h6>
                <p class="small mb-0">There are currently no reviews matching your criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small">Product</th>
                            <th class="fw-semibold text-muted text-uppercase small">Customer</th>
                            <th class="text-center fw-semibold text-muted text-uppercase small">Rating</th>
                            <th class="fw-semibold text-muted text-uppercase small">Comment</th>
                            <th class="fw-semibold text-muted text-uppercase small">Photos</th>
                            <th class="fw-semibold text-muted text-uppercase small">Date</th>
                            <th class="text-end pe-4 fw-semibold text-muted text-uppercase small">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('vendor.products.edit', $review->product_id) }}" class="text-decoration-none fw-bold text-dark small hover-text-primary">
                                    {{ Str::limit($review->product->name ?? 'Unknown Product', 30) }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                        {{ substr($review->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="fw-medium text-dark small">{{ $review->user->name ?? 'Anonymous' }}</span>
                                </div>
                            </td>
                            <td class="text-center text-nowrap">
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill px-3 py-1 fw-bold shadow-sm">
                                    {{ $review->rating }} <i class="bi bi-star-fill text-warning ms-1" style="font-size:0.75rem;"></i>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small d-inline-block text-truncate" style="max-width: 250px;" title="{{ $review->comment }}">
                                    @if($review->comment)
                                        <i class="bi bi-quote text-secondary me-1"></i>{{ Str::limit($review->comment, 60) }}
                                    @else
                                        <i class="text-muted opacity-50">No comment provided</i>
                                    @endif
                                </span>
                                @if($review->vendor_response)
                                    <div class="small mt-1 text-success fw-medium">
                                        <i class="bi bi-reply-fill me-1"></i>Responded
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($review->review_images))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($review->review_images as $image)
                                            <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener noreferrer" title="View review image">
                                                <img src="{{ asset('storage/' . $image) }}" alt="Review image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb;">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">No photos</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-muted small fw-medium">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $review->created_at->format('d M, Y') }}
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('vendor.reviews.show', $review->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                                    <i class="bi bi-eye-fill me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($reviews->hasPages())
        <div class="card-footer bg-white border-top p-4 d-flex justify-content-center" style="border-radius: 0 0 16px 16px;">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection
