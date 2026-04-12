@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Reviews</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Reviews & Ratings</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Moderate and analyze product feedback.</p>
        </div>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Review List</h4>
            <form method="GET" action="{{ route('admin.reviews') }}" style="display: flex; align-items: center; gap: 10px;">
                <select name="rating" class="form-agri" style="height: 42px; min-width: 140px; margin-bottom: 0;">
                    <option value="">All Ratings</option>
                    @foreach([5,4,3,2,1] as $r)
                        <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} Stars</option>
                    @endforeach
                </select>
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search reviews..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Rating</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Comment</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Photos</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $review->user->name ?? 'Deleted User' }}</div>
                                <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-primary);">{{ $review->product->name ?? 'Unknown Product' }}</div>
                                <small class="text-muted">{{ $review->vendor->title ?? 'Marketplace' }}</small>
                            </td>
                            <td class="px-4 py-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="font-size: 12px; color: {{ $i <= $review->rating ? '#fbbf24' : '#e5e7eb' }};"></i>
                                @endfor
                            </td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($review->comment, 80) }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($review->review_images))
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        @foreach($review->review_images as $image)
                                            <a href="{{ asset('storage/' . $image) }}" target="_blank" rel="noopener noreferrer" title="View review image">
                                                <img src="{{ asset('storage/' . $image) }}" alt="Review image" style="width: 42px; height: 42px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb;">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">No photos</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.products.show', $review->product_id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Delete this review?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">No reviews found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $reviews->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
