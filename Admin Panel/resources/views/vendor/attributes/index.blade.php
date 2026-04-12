@extends('vendor.layouts.app')
@section('title', 'Attributes')
@section('page-title', 'Product Attributes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Product Attributes</h4>
        <span class="text-muted small fw-medium mt-1 d-block">View available attributes (e.g. Weight, Quantity) defined by administrators for product listings</span>
    </div>
</div>

<div class="card border-0 shadow-sm hover-card pt-2" style="border-radius:16px;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-success fs-5"></i>All Attributes</h6>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 shadow-sm">{{ $attributes->total() }} Attributes</span>
    </div>
    <div class="card-body p-0">
        @if($attributes->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-sliders fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No attributes found</h6>
                <p class="small mb-0">There are currently no product attributes managed by the administration.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small" style="width: 80px;">#</th>
                            <th class="fw-semibold text-muted text-uppercase small">Attribute Name</th>
                            <th class="fw-semibold text-muted text-uppercase small">Type</th>
                            <th class="fw-semibold text-muted text-uppercase small">Options</th>
                            <th class="pe-4 fw-semibold text-muted text-uppercase small">Used on Your Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attributes as $attr)
                        <tr>
                            <td class="ps-4">
                                <span class="font-monospace text-muted small">#{{ $attr->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">
                                        <i class="bi bi-tag text-primary"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $attr->name ?: $attr->title }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ strtoupper($attr->type ?? 'text') }}</span>
                            </td>
                            <td>
                                <span class="text-muted small fw-semibold">{{ $attr->values_count }}</span>
                            </td>
                            <td class="pe-4">
                                @php
                                    $vendorId = optional(auth('vendor')->user()->vendor)->id ?? 0;
                                    $usageCount = \App\Models\ProductAttribute::whereHas('product', fn($q) => $q->where('vendor_id', $vendorId))
                                        ->where('attribute_id', $attr->id)
                                        ->count();
                                @endphp
                                @if($usageCount > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">{{ $usageCount }} product(s)</span>
                                @else
                                    <span class="text-muted small fst-italic">Not yet used</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($attributes->hasPages())
        <div class="card-footer bg-white border-top p-4 d-flex justify-content-center" style="border-radius: 0 0 16px 16px;">
            {{ $attributes->links() }}
        </div>
    @endif
</div>
@endsection
