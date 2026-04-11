@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Products</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Products</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">View, edit, and manage all products.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Product List</h4>
            <form method="GET" action="{{ route('admin.products.index') }}" style="display: flex; align-items: center; gap: 10px;">
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search products..." value="{{ $filters['search'] ?? '' }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Image</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Name</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Category</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Price</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="width: 42px; height: 42px; border-radius: 10px; overflow: hidden; border: 1px solid var(--agri-border); background: #f8fafc;">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--agri-text-muted);"><i class="fas fa-seedling"></i></div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->sku }}</small>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-size: 14px; color: var(--agri-text-main);">{{ $product->category->name ?? 'Unmapped' }}</div>
                                <small class="text-muted">{{ $product->vendor->name ?? 'Direct' }}</small>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-primary-dark);">PKR {{ number_format($product->price, 0) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="d-inline" onsubmit="return confirm('Delete this product?');">
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
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $products->appends($filters)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
