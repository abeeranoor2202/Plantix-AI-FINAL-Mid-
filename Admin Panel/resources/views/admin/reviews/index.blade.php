@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Social Proof</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Reviews & Ratings</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Moderate and analyze user sentiment across all product listings.</p>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary-hover);">
                    <i class="fas fa-check-circle"></i>
                    <span style="font-weight: 600;">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="background:none; border:none; opacity: 0.5;"></button>
            </div>
        </div>
    @endif

    {{-- Advanced Filter Bar --}}
    <div class="card-agri mb-4" style="padding: 24px; background: white;">
        <form method="GET" action="{{ route('admin.reviews') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="agri-label">Search Context</label>
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px;"></i>
                    <input type="text" name="search" class="form-agri" style="padding-left: 40px; height: 44px;"
                           placeholder="Search by Product name or Customer..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="agri-label">Rating Filter</label>
                <select name="rating" class="form-agri" style="height: 44px;">
                    <option value="">All Star Ratings</option>
                    @foreach([5,4,3,2,1] as $r)
                        <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} Stars</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 44px; font-weight: 700;">Filter Feedback</button>
                <a href="{{ route('admin.reviews') }}" class="btn-agri btn-agri-outline" style="min-width: 100px; height: 44px; text-decoration: none; display: flex; align-items: center; justify-content: center;">Reset</a>
            </div>
        </form>
    </div>

    {{-- Review Ledger Tables --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Sentiment Registry</h4>
            <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">
                Showing {{ $reviews->count() }} of {{ $reviews->total() }} recorded feedbacks
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Customer Feed</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Associated Product</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Sentiment (Rating)</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Commentary</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                        <td style="padding: 20px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                    {{ substr($review->user->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $review->user->name ?? 'Deleted User' }}</div>
                                    <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ $review->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 20px 24px;">
                            <a href="{{ route('admin.products.show', $review->product_id) }}" style="text-decoration: none;">
                                <div style="font-weight: 600; color: var(--agri-primary); font-size: 14px;">{{ $review->product->name ?? 'Unknown Product' }}</div>
                                <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Provider: {{ $review->vendor->name ?? 'Marketplace' }}</div>
                            </a>
                        </td>
                        <td style="padding: 20px 24px;">
                            <div style="display: flex; gap: 2px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="font-size: 12px; color: {{ $i <= $review->rating ? '#FBBF24' : '#E5E7EB' }};"></i>
                                @endfor
                            </div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-top: 4px;">{{ $review->rating }}.0 Quality Score</div>
                        </td>
                        <td style="padding: 20px 24px;">
                            <div style="font-size: 13px; color: var(--agri-text-main); line-height: 1.5; max-width: 300px;">
                                "{{ $review->comment }}"
                            </div>
                        </td>
                        <td style="padding: 20px 24px;" class="text-end">
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Moderator action: Permanently delete this review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-agri" style="padding: 10px; color: var(--agri-error); background: #FEF2F2; border: none; border-radius: 10px;" title="Moderation: Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div style="color: var(--agri-border); font-size: 48px; margin-bottom: 20px;"><i class="fas fa-comment-slash"></i></div>
                            <div style="font-weight: 700; color: var(--agri-text-muted);">No product reviews found in the registry.</div>
                        </td>
                    </tr>
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

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
