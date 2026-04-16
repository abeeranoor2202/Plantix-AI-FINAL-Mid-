@extends('vendor.layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Products</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Products</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">View, edit, and manage all products.</p>
        </div>
        <x-button :href="route('vendor.products.create')" variant="primary" icon="fas fa-plus">Add Product</x-button>
    </div>

    <x-card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Product List</h4>
                <form method="GET" action="{{ route('vendor.products.index') }}" style="display: flex; align-items: center; gap: 10px;">
                    <div class="agri-search-wrap" style="width: 320px;">
                        <i class="fas fa-search agri-search-icon"></i>
                        <input type="text" name="search" class="form-agri agri-search-input" placeholder="Search products..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                </form>
            </div>
        </x-slot>

        <x-table>
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Image</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Name</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Category</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Price</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Returnable</th>
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
                            <small class="text-muted">Vendor Product</small>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight: 700; color: var(--agri-primary-dark);">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($product->price, 0) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :variant="$product->is_active ? 'success' : 'secondary'">{{ $product->is_active ? 'Active' : 'Inactive' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :variant="$product->is_returnable ? 'success' : 'secondary'">{{ $product->is_returnable ? 'Yes' : 'No' }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                <form method="POST" action="{{ route('vendor.products.toggle-active', $product->id) }}">
                                    @csrf
                                    <x-toggle :checked="$product->is_active" onchange="this.form.submit()" />
                                </form>
                                <x-button :href="route('vendor.products.show', $product->id)" variant="icon" title="View" style="color: #2563eb; background: var(--agri-bg); width:34px; height:34px;"><i class="fas fa-eye"></i></x-button>
                                <x-button :href="route('vendor.products.edit', $product->id)" variant="icon" title="Edit" style="color: var(--agri-primary); background: var(--agri-bg); width:34px; height:34px;"><i class="fas fa-pen"></i></x-button>
                                <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="icon" title="Delete" style="color:#ef4444; background:#fef2f2; width:34px; height:34px;"><i class="fas fa-trash"></i></x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>

    @if($products->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .agri-search-wrap {
        position: relative;
    }

    .agri-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--agri-text-muted);
        font-size: 14px;
        pointer-events: none;
    }

    .agri-search-input {
        margin-bottom: 0;
        height: 42px;
        padding-left: 36px;
    }
</style>
@endpush
