@extends('vendor.layouts.app')
@section('title', 'Product Details')
@section('page-title', 'Product Details')

@section('content')
<div class="mb-4 d-flex align-items-center gap-3">
    <a href="{{ route('vendor.products.index') }}" class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
        <i class="fas fa-arrow-left text-muted"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">
            <i class="fas fa-box-open me-2 text-primary fs-5"></i>{{ $product->name }}
        </h4>
        <p class="text-muted small m-0">View complete details for this listing.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-agri p-4 border-0 shadow-sm h-100">
            <div class="text-center">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 100%; max-width: 280px; object-fit: cover;">
                @else
                    <div class="rounded-3 border d-flex align-items-center justify-content-center" style="height: 280px; background: #F9FAFB;">
                        <i class="fas fa-image text-muted fs-1"></i>
                    </div>
                @endif
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <a href="{{ route('vendor.products.edit', $product->id) }}" class="btn-agri btn-agri-primary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}" onsubmit="return confirm('Delete this product permanently?');" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-agri btn-agri-outline text-danger">
                        <i class="fas fa-trash-alt me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-agri p-4 border-0 shadow-sm h-100">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">SKU</label>
                    <p class="mb-0 fw-semibold">{{ $product->sku ?: 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Category</label>
                    <p class="mb-0 fw-semibold">{{ optional($product->category)->name ?: 'Uncategorized' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Price</label>
                    <p class="mb-0 fw-semibold">{{ config('plantix.currency_symbol') }}{{ number_format((float) $product->price, 2) }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Discount Price</label>
                    <p class="mb-0 fw-semibold">
                        {{ $product->discount_price ? config('plantix.currency_symbol') . number_format((float) $product->discount_price, 2) : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Stock Tracking</label>
                    <p class="mb-0 fw-semibold">{{ $product->track_stock ? 'Enabled' : 'Disabled' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Stock Quantity</label>
                    <p class="mb-0 fw-semibold">{{ $product->track_stock ? (int) ($product->stock_quantity ?? 0) : 'Unlimited' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Status</label>
                    <p class="mb-0 fw-semibold">{{ $product->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase small text-muted fw-bold">Created</label>
                    <p class="mb-0 fw-semibold">{{ optional($product->created_at)->format('d M Y, h:i A') }}</p>
                </div>
                <div class="col-12">
                    <label class="text-uppercase small text-muted fw-bold">Description</label>
                    <p class="mb-0">{{ $product->description ?: 'No description provided.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
