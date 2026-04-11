@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.products.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Products</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Product Details</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Product Profile</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Comprehensive overview of product details and settings.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-agri btn-agri-primary" style="text-decoration: none;">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i> Edit
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <a href="{{ route('admin.products.show', $product->id) }}" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary);">
            Basic
        </a>
        <a href="{{ route('admin.products.edit', $product->id) }}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600;">
            Edit
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card-agri" style="text-align: center; padding: 40px 24px;">
                <div class="profile_image" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.1); margin: 0 auto 24px; overflow: hidden; background: var(--agri-bg); display: flex; align-items: center; justify-content: center;">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <i class="fas fa-image" style="font-size: 36px; color: var(--agri-border);"></i>
                    @endif
                </div>
                <h3 class="user_name" style="font-size: 22px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 8px;">{{ $product->name }}</h3>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-seedling"></i>
                    {{ $product->is_active ? 'Active Product' : 'Inactive Product' }}
                </div>

                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border);">
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                         <i class="fas fa-tags" style="color: var(--agri-text-muted); width: 16px;"></i>
                         <span>{{ $product->category->name ?? 'Not assigned' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                         <i class="fas fa-store" style="color: var(--agri-text-muted); width: 16px;"></i>
                         <span>{{ $product->vendor->title ?? 'Not assigned' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                         <i class="fas fa-boxes" style="color: var(--agri-text-muted); width: 16px;"></i>
                         <span>{{ $product->stock_quantity ?? 0 }} in stock</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px;">

                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Product Details</h4>
                <div class="address-card">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Product Name</label>
                            <div class="field-static">{{ $product->name ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">SKU</label>
                            <div class="field-static">{{ $product->sku ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Base Price</label>
                            <div class="field-static">Rs {{ number_format((float) $product->price, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Sale Price</label>
                            <div class="field-static">{{ $product->discount_price !== null ? 'Rs '.number_format((float) $product->discount_price, 2) : 'Not set' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Effective Price</label>
                            <div class="field-static">Rs {{ number_format((float) $product->effective_price, 2) }}</div>
                        </div>
                        <div class="col-12">
                            <label class="agri-label">Short Description</label>
                            <div class="field-static">{{ $product->short_description ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="agri-label">Description</label>
                            <div class="field-static" style="white-space: pre-line;">{{ $product->description ?? 'Not provided' }}</div>
                        </div>
                    </div>
                </div>

                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; margin-top: 24px;">Classification & Stock</h4>
                <div class="address-card">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Category</label>
                            <div class="field-static">{{ $product->category->name ?? 'Not assigned' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Vendor</label>
                            <div class="field-static">{{ $product->vendor->title ?? 'Not assigned' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Unit</label>
                            <div class="field-static">{{ $product->unit ?? 'Not set' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Stock</label>
                            <div class="field-static">{{ $product->stock_quantity ?? 0 }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">Low Stock Warning</label>
                            <div class="field-static">{{ $product->low_stock_threshold ?? 10 }}</div>
                        </div>
                    </div>
                </div>

                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; margin-top: 24px;">Media</h4>
                <div class="address-card">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="agri-label">Primary Image</label>
                            <div style="width: 100%; height: 120px; border-radius: 12px; background: white; border: 1px solid var(--agri-border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <i class="fas fa-image" style="font-size: 28px; color: var(--agri-border);"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="agri-label">Gallery</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; min-height: 120px; align-items: flex-start;">
                                @forelse($product->images->where('is_primary', false) as $img)
                                    <img src="{{ asset('storage/'.$img->path) }}" alt="Gallery" style="width: 72px; height: 72px; object-fit: cover; border-radius: 8px; border: 1px solid var(--agri-border);">
                                @empty
                                    <div class="field-static" style="width: 100%;">No gallery images</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; margin-top: 24px;">Settings</h4>
                <div class="address-card" style="margin-bottom: 0;">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Active</label>
                            <div class="form-check form-switch" style="padding-left: 0; margin-bottom: 0;">
                                <input type="checkbox" class="form-check-input" style="width: 44px; height: 22px; margin-left: 0;" {{ $product->is_active ? 'checked' : '' }} disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Featured</label>
                            <div class="form-check form-switch" style="padding-left: 0; margin-bottom: 0;">
                                <input type="checkbox" class="form-check-input" style="width: 44px; height: 22px; margin-left: 0;" {{ $product->is_featured ? 'checked' : '' }} disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Returnable</label>
                            <div class="field-static">{{ $product->is_returnable ? 'Yes' : 'No' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Tax Rate</label>
                            <div class="field-static">{{ $product->tax_rate ?? 0 }}%</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
    .address-card {
        background: var(--agri-bg);
        border: 1px solid var(--agri-border);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }
    .address-card:hover {
        border-color: var(--agri-primary);
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .field-static {
        background: white;
        border: 1px solid var(--agri-border);
        border-radius: 12px;
        min-height: 46px;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 600;
        color: var(--agri-text-heading);
        display: flex;
        align-items: center;
    }
</style>
@endsection
