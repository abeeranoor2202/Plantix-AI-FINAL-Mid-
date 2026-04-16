@extends('vendor.layouts.app')
@section('title', 'Products Inventory')
@section('page-title', 'My Products')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--panel-title); margin: 0;">Products</h1>
            <p style="color: var(--panel-muted); margin: 4px 0 0 0;">View, edit, and manage your products.</p>
        </div>
        <x-ui.button :href="route('vendor.products.create')" variant="primary" size="md" icon="fas fa-plus">Add Product</x-ui.button>
    </div>

    <x-ui.table>
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Product List</h4>
            <form method="GET" action="{{ route('vendor.products.index') }}" class="panel-filter-wrap">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--panel-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search products..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0;">
                </div>
                <select name="status" class="form-agri" style="min-width: 150px;">
                    <option value="">All Statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <x-ui.button variant="primary" size="md" type="submit">Apply Filters</x-ui.button>
                <x-ui.button :href="route('vendor.products.index')" variant="outline" size="md">Clear</x-ui.button>
            </form>
        </div>

        <table class="table mb-0" style="vertical-align: middle;">
            <thead style="background: var(--panel-bg);">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Listed On</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            <div style="width: 42px; height: 42px; border-radius: 10px; overflow: hidden; border: 1px solid var(--panel-border); background: #f8fafc;">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--panel-muted);"><i class="fas fa-seedling"></i></div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight: 700; color: var(--panel-text);">{{ $product->name }}</div>
                            <small class="text-muted">{{ $product->sku ?: 'No SKU' }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight: 700; color: var(--panel-primary-dark);">{{ config('plantix.currency_symbol') }} {{ number_format($product->price, 2) }}</div>
                            @if($product->discount_price)
                                <small class="text-muted"><s>{{ config('plantix.currency_symbol') }} {{ number_format($product->discount_price, 2) }}</s></small>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($product->track_stock)
                                <x-ui.badge :variant="$product->stock_quantity <= 0 ? 'danger' : ($product->stock_quantity <= 10 ? 'warning' : 'success')">
                                    {{ $product->stock_quantity }} units
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="info">Unlimited</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :variant="$product->is_active ? 'active' : 'inactive'">{{ $product->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <div class="panel-action-group">
                                <form method="POST" action="{{ route('vendor.products.toggle-active', $product->id) }}" class="m-0">
                                    @csrf
                                    <x-ui.button variant="success-soft" size="sm" :circle="true" :icon="$product->is_active ? 'fas fa-toggle-on' : 'fas fa-toggle-off'" type="submit" :title="$product->is_active ? 'Deactivate Product' : 'Activate Product'" />
                                </form>
                                <x-ui.button :href="route('vendor.products.show', $product->id)" variant="info-soft" size="sm" :circle="true" icon="fas fa-eye" title="View Product" />
                                <x-ui.button :href="route('vendor.products.edit', $product->id)" variant="success-soft" size="sm" :circle="true" icon="fas fa-pen" title="Edit Product" />
                                <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}" class="m-0" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="danger-soft" size="sm" :circle="true" icon="fas fa-trash" type="submit" title="Delete Product" />
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--panel-muted);">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($products->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--panel-border); display: flex; justify-content: center;">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </x-ui.table>
</div>
@endsection
