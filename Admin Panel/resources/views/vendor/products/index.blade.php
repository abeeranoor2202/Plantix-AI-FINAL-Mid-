@extends('vendor.layouts.app')
@section('title', 'Products Inventory')
@section('page-title', 'My Products')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-boxes me-2 text-primary"></i>Product Catalog</h4>
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
    @endif

        <span class="text-muted small fw-medium">{{ $products->total() }} product(s) available</span>

    @if(session('success'))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        });
    </script>
    @endpush
    @endif
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn-agri btn-agri-primary shadow-sm px-4">
        <i class="fas fa-plus-circle me-2"></i> List New Product
    </a>
</div>

{{-- Search / Filter --}}
<form method="GET" class="card-agri p-4 mb-4 border-0">
    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label small text-uppercase fw-bold text-muted mb-2">Search Products</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-agri border-start-0 ps-0" style="border-radius: 0 0.5rem 0.5rem 0;"
                       placeholder="Search by name, SKU..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-uppercase fw-bold text-muted mb-2">Status Filter</label>
            <select name="status" class="form-agri">
                <option value="">All Statuses</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active (Visible)</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive (Hidden)</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn-agri btn-agri-primary px-4 shadow-sm flex-grow-1">
                Apply Filters
            </button>
            <a href="{{ route('vendor.products.index') }}" class="btn-agri btn-agri-outline px-4">Clear</a>
        </div>
    </div>
</form>

<div class="card-agri p-0 overflow-hidden border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th class="py-3 px-4 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Product Image</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Name & SKU</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Pricing</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Inventory</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Status</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Listed On</th>
                    <th class="text-end py-3 px-4 border-0 text-muted text-uppercase fw-bold" style="font-size: 13px;">Manage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr style="background: white; border-bottom: 1px solid var(--sidebar-border); transition: background 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                    <td class="px-4 py-3">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 class="rounded-3 shadow-sm border" width="60" height="60" style="object-fit:cover"
                                 alt="{{ $product->name }}">
                        @else
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center shadow-sm border"
                                 style="width:60px;height:60px">
                                <i class="fas fa-image text-muted fs-4"></i>
                            </div>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-dark fs-6">{{ $product->name }}</div>
                        @if($product->sku)
                            <div class="text-muted small fw-medium mt-1"><i class="fas fa-barcode me-1"></i>SKU: {{ $product->sku }}</div>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-success fs-6">
                            {{ config('plantix.currency_symbol') }}{{ number_format($product->price, 2) }}
                        </div>
                        @if($product->discount_price)
                            <div class="text-danger small fw-medium">
                                <s>{{ config('plantix.currency_symbol') }}{{ number_format($product->discount_price, 2) }}</s>
                            </div>
                        @endif
                    </td>
                    <td class="py-3">
                        @if($product->track_stock)
                            <span class="badge-agri border 
                                {{ $product->stock_quantity <= 0 ? 'badge-danger-agri border-danger border-opacity-25' : ($product->stock_quantity <= 10 ? 'badge-warning-agri border-warning border-opacity-25' : 'badge-success-agri border-success border-opacity-25') }}">
                                <i class="fas fa-box me-1"></i>{{ $product->stock_quantity }} units
                            </span>
                        @else
                            <span class="badge-agri bg-light text-muted border"><i class="fas fa-infinity me-1"></i>Unlimited</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <span class="badge-agri {{ $product->is_active ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25' }}">
                            {{ $product->is_active ? 'Active' : 'Unlisted' }}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="text-dark fw-medium small"><i class="far fa-calendar-alt me-1 text-muted"></i>{{ $product->created_at->format('d M Y') }}</div>
                    </td>
                    <td class="text-end px-4 py-3">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('vendor.products.edit', $product->id) }}"
                               class="btn btn-sm btn-light border shadow-sm text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Edit Product">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}"
                                  onsubmit="return confirm('Delete this product permanently?');" class="m-0">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border shadow-sm text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Delete Product">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border border-dashed" style="width:100px; height:100px;">
                            <i class="fas fa-box-open fs-1 text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No Products Found</h5>
                        <p class="text-muted">You haven't listed any products yet. Click "List New Product" to get started.</p>
                        <a href="{{ route('vendor.products.create') }}" class="btn-agri btn-agri-primary mt-2 px-4 shadow-sm">
                            <i class="fas fa-plus me-1"></i> List Your First Product
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($products->hasPages())
    <div class="p-4 border-top bg-light text-center">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
