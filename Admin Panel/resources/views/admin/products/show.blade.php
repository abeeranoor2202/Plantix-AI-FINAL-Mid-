@extends('layouts.app')

@section('title', 'Product: '.$product->name)

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="h4 mb-0">{{ $product->name }}</h2>
        </div>
        <div>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}"
                  class="d-inline" onsubmit="return confirm('Delete this product?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Main Info --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="img-fluid rounded shadow-sm"
                                     style="max-height:200px;object-fit:cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="height:200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $product->name }}</h4>
                            <p class="text-muted">{{ $product->short_description }}</p>

                            <table class="table table-sm table-borderless">
                                <tr><th class="text-muted" style="width:130px">SKU</th><td>{{ $product->sku ?? '—' }}</td></tr>
                                <tr><th class="text-muted">Category</th><td>{{ $product->category->name ?? '—' }}</td></tr>
                                <tr><th class="text-muted">Vendor</th><td>{{ $product->vendor->name ?? '—' }}</td></tr>
                                <tr><th class="text-muted">Unit</th><td>{{ $product->unit ?? '—' }}</td></tr>
                                <tr>
                                    <th class="text-muted">Price</th>
                                    <td>
                                        Rs {{ number_format($product->price, 2) }}
                                        @if($product->sale_price)
                                            <span class="badge bg-danger ms-1">
                                                Sale: Rs {{ number_format($product->sale_price, 2) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($product->description)
                        <hr>
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $product->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Gallery --}}
            @if($product->images && $product->images->where('is_primary', false)->count())
                <div class="card mb-4">
                    <div class="card-header fw-semibold">Gallery</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->images->where('is_primary', false) as $img)
                                <img src="{{ asset('storage/'.$img->path) }}"
                                     class="rounded border"
                                     style="width:80px;height:80px;object-fit:cover;">
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar: stock & flags --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header fw-semibold">Stock</div>
                <div class="card-body">
                    <div class="display-6 fw-bold text-center
                        {{ ($product->stock_quantity ?? 0) <= ($product->low_stock_threshold ?? 10) ? 'text-danger' : 'text-success' }}">
                        {{ $product->stock_quantity ?? 0 }}
                    </div>
                    <p class="text-center text-muted mb-0">units in stock</p>
                    <hr>
                    <small class="text-muted">Low-stock alert at: {{ $product->low_stock_threshold ?? config('plantix.low_stock_threshold') }} units</small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-semibold">Status</div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted small">Active</dt>
                        <dd>
                            @if($product->is_active)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </dd>
                        <dt class="text-muted small">Featured</dt>
                        <dd>
                            @if($product->is_featured)
                                <span class="badge bg-warning text-dark">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </dd>
                        <dt class="text-muted small">Returnable</dt>
                        <dd>
                            @if($product->is_returnable)
                                <span class="badge bg-info">Yes ({{ $product->return_window_days ?? 7 }} days)</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </dd>
                        <dt class="text-muted small">Tax Rate</dt>
                        <dd>{{ $product->tax_rate ?? 0 }}%</dd>
                    </dl>

                    <hr>
                    <form method="POST" action="{{ route('admin.products.toggle-featured', $product->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-star me-1"></i>
                            {{ $product->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Timestamps</div>
                <div class="card-body">
                    <dl class="mb-0 small">
                        <dt class="text-muted">Created</dt>
                        <dd>{{ $product->created_at->format('d M Y, h:i A') }}</dd>
                        <dt class="text-muted">Updated</dt>
                        <dd>{{ $product->updated_at->format('d M Y, h:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
