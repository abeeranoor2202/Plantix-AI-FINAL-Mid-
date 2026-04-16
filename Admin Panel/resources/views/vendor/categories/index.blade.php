@extends('vendor.layouts.app')
@section('title', 'Categories')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Product Categories</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">View available categories managed by administrators.</p>
        </div>
        <x-badge variant="success">{{ $categories->total() }} Categories</x-badge>
    </div>

    <x-card style="padding: 0; overflow: hidden;">
        <x-slot name="header">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">All Categories</h4>
        </x-slot>
        @if($categories->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-tags fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No categories found</h6>
                <p class="small mb-0">There are currently no product categories available.</p>
            </div>
        @else
            <x-table>
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small" style="width: 80px;">ID</th>
                            <th class="fw-semibold text-muted text-uppercase small">Category Name</th>
                            <th class="fw-semibold text-muted text-uppercase small">Description</th>
                            <th class="text-center pe-4 fw-semibold text-muted text-uppercase small">Your Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td class="ps-4">
                                <span class="font-monospace text-muted small">#{{ $cat->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($cat->image)
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3 border shadow-sm flex-shrink-0" style="width:40px;height:40px;">
                                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="rounded w-100 h-100" style="object-fit:cover;">
                                        </div>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3 border shadow-sm flex-shrink-0" style="width:40px;height:40px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="fw-bold text-dark">{{ $cat->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small d-inline-block text-truncate" style="max-width: 300px;" title="{{ $cat->description }}">
                                    {{ Str::limit($cat->description ?? 'No description provided.', 60) }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <x-badge :variant="($cat->products_count ?? 0) > 0 ? 'info' : 'secondary'">{{ $cat->products_count ?? '0' }} Products</x-badge>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </x-table>
        @endif
    </x-card>
    @if($categories->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
