@extends('vendor.layouts.app')
@section('title', 'Product Details')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    @if(session('success'))
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 20px; box-shadow: 0 24px 60px rgba(0,0,0,0.15);">
                <div class="modal-body p-4 text-center">
                    <div style="width: 68px; height: 68px; border-radius: 50%; background: #D1FAE5; color: #059669; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h4 style="font-weight: 800; color: var(--agri-text-heading); margin-bottom: 10px;">Success</h4>
                    <p style="margin: 0; color: var(--agri-text-muted); font-weight: 600;">{{ session('success') }}</p>
                </div>
                <div class="modal-footer border-top-0 justify-content-center pb-4 pt-0">
                    <button type="button" class="btn-agri btn-agri-primary px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        });
    </script>
    @endpush
    @endif

    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
        <a href="{{ route('vendor.products.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Products</a>
        <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
        <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $product->name }}</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $product->name }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">View complete product information and current settings.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <x-button :href="route('vendor.products.edit', $product->id)" variant="primary" icon="fas fa-pen">Edit</x-button>
            <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}" onsubmit="return confirm('Delete this product permanently?');" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" icon="fas fa-trash">Delete</x-button>
            </form>
        </div>
    </div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-agri p-4 h-100">
            <div class="text-center">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-3 border" style="width: 100%; max-width: 280px; object-fit: cover;">
                @else
                    <div class="rounded-3 border d-flex align-items-center justify-content-center" style="height: 280px; background: #F9FAFB;">
                        <i class="fas fa-image text-muted fs-1"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-agri p-4 h-100">
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
                <div class="col-12">
                    <label class="text-uppercase small text-muted fw-bold">Specifications</label>
                    @if(($product->attributes ?? collect())->isNotEmpty())
                        <div class="table-responsive border rounded-3">
                            <table class="table table-sm mb-0 align-middle">
                                <tbody>
                                @foreach($product->attributes as $item)
                                    <tr>
                                        <th class="bg-light" style="width:35%;">{{ $item->attribute?->name ?: $item->attribute?->title ?: 'Attribute' }}</th>
                                        <td>{{ $item->display_value ?: '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-0 text-muted">No attribute values saved for this product.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
