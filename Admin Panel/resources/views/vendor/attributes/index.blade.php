@extends('vendor.layouts.app')
@section('title', 'Attributes')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Product Attributes</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">View available attributes defined by administrators for product listings.</p>
        </div>
        <x-badge variant="success">{{ $attributes->total() }} Attributes</x-badge>
    </div>

    <x-card style="padding: 0; overflow: hidden;">
        <x-slot name="header">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">All Attributes</h4>
        </x-slot>
        @if($attributes->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-sliders fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No attributes found</h6>
                <p class="small mb-0">There are currently no product attributes managed by the administration.</p>
            </div>
        @else
            <x-table>
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
                                    <x-badge variant="success">{{ $usageCount }} product(s)</x-badge>
                                @else
                                    <span class="text-muted small fst-italic">Not yet used</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </x-table>
        @endif
    </x-card>
    @if($attributes->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
            {{ $attributes->links() }}
        </div>
    @endif
</div>
@endsection
