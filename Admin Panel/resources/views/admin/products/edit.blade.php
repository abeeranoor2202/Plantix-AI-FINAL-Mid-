@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.products.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Products</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Product</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Product</h1>
    </div>

    @if($errors->any())
        <div class="error_top" style="background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data" id="product-edit-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="track_stock" value="1">

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card-agri" style="padding: 40px;">

                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-box"></i> Product Details
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-agri" value="{{ old('name', $product->name) }}" placeholder="e.g. Premium NPK Fertilizer 20-20-20" required>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">SKU</label>
                                <input type="text" name="sku" class="form-agri" value="{{ old('sku', $product->sku) }}" placeholder="AGRI-XXX-000">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Base Price <span class="text-danger">*</span></label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-text-muted);">RS</span>
                                    <input type="number" step="0.01" min="0" name="price" class="form-agri" value="{{ old('price', $product->price) }}" placeholder="0.00" style="padding-left: 40px;" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Sale Price</label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-text-muted);">RS</span>
                                    <input type="number" step="0.01" min="0" name="sale_price_ui" class="form-agri" value="{{ old('sale_price_ui', $product->discount_price) }}" placeholder="0.00" style="padding-left: 40px;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Short Description</label>
                                <input type="text" name="short_description" class="form-agri" value="{{ old('short_description', $product->short_description) }}" placeholder="Concise short product summary">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Discounted Price</label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-text-muted);">RS</span>
                                    <input type="number" step="0.01" min="0" name="discount_price" id="discount_price" class="form-agri" value="{{ old('discount_price', $product->discount_price) }}" placeholder="0.00" style="padding-left: 40px;">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="agri-label">Description</label>
                                <textarea name="description" class="form-agri" rows="4" placeholder="Product details, usage notes, and key specifications.">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-tags"></i> Product Classification
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-agri" required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-agri" required>
                                    <option value="">Select vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $product->vendor_id) == $vendor->id)>{{ $vendor->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">Unit</label>
                                <input type="text" name="unit" class="form-agri" value="{{ old('unit', $product->unit) }}" placeholder="e.g. Kg, Litre, Packet">
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">Stock Quantity</label>
                                <input type="number" min="0" name="stock_quantity" class="form-agri" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" placeholder="0">
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">Low Stock Warning</label>
                                <input type="number" min="0" name="low_stock_threshold" class="form-agri" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}" placeholder="10">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Product Media</h4>
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 18px;">
                            <div class="product_image_preview" style="width: 80px; height: 80px; border-radius: 12px; background: white; border: 2px dashed var(--agri-border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                @if($product->image)
                                    <img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="{{ asset('storage/'.$product->image) }}" alt="image">
                                @else
                                    <i class="fas fa-image" style="color: var(--agri-border); font-size: 24px;"></i>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <input type="file" name="image" id="main_product_image" class="form-control" style="font-size: 13px;" accept="image/*">
                            </div>
                        </div>

                        @if($product->images && $product->images->where('is_primary', false)->count())
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px;">
                                @foreach($product->images->where('is_primary', false) as $img)
                                    <img src="{{ asset('storage/'.$img->path) }}" alt="Gallery" style="width: 52px; height: 52px; border-radius: 8px; object-fit: cover; border: 1px solid var(--agri-border);">
                                @endforeach
                            </div>
                        @endif

                        <div>
                            <label class="agri-label">Gallery Upload</label>
                            <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple style="font-size: 13px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a;">
                        <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 14px;">Product Settings</h5>

                        <div class="row g-4" style="margin-bottom: 8px;">
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Active</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Featured</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Returnable</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_returnable" value="1" @checked(old('is_returnable', $product->is_returnable))>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="row g-4" style="margin-top: 4px;">
                            <div class="col-md-6">
                                <label class="agri-label">Return Days</label>
                                <input type="number" min="0" name="return_window_days" class="form-agri" value="{{ old('return_window_days', $product->return_window_days ?? 7) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">Tax Rate</label>
                                <input type="number" step="0.01" min="0" name="tax_rate" class="form-agri" value="{{ old('tax_rate', $product->tax_rate ?? 0) }}">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#main_product_image').on('change', function (evt) {
        var f = evt.target.files[0];
        if (!f) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            $('.product_image_preview').html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + e.target.result + '" alt="image">');
        };
        reader.readAsDataURL(f);
    });

    $('#product-edit-form').on('submit', function () {
        var salePrice = $('input[name="sale_price_ui"]').val();
        var discountPrice = $('#discount_price').val();
        if (!discountPrice && salePrice) {
            $('#discount_price').val(salePrice);
        }
    });
});
</script>
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }
    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
</style>
@endsection
