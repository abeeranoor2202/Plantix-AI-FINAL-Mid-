@extends('vendor.layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('vendor.products.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Products</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ isset($product) ? 'Edit Product' : 'Add Product' }}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ isset($product) ? 'Edit Product' : 'Create Product' }}</h1>
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

    <form method="POST" action="{{ isset($product) ? route('vendor.products.update', $product->id) : route('vendor.products.store') }}" enctype="multipart/form-data" id="vendor-product-form">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card-agri" style="padding: 40px;">
                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-box"></i> Product Details
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <x-input label="Product Name" name="name" :required="true" type="text" value="{{ old('name', $product->name ?? '') }}" placeholder="e.g. Premium NPK Fertilizer 20-20-20" />
                            </div>
                            <div class="col-md-6">
                                <x-input label="SKU" name="sku" type="text" value="{{ old('sku', $product->sku ?? '') }}" placeholder="AGRI-XXX-000" />
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Base Price <span class="text-danger">*</span></label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-text-muted);">{{ config('plantix.currency_symbol', 'PKR') }}</span>
                                    <input type="number" step="0.01" min="0" name="price" class="form-agri" value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" style="padding-left: 52px;" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Discounted Price</label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-text-muted);">{{ config('plantix.currency_symbol', 'PKR') }}</span>
                                    <input type="number" step="0.01" min="0" name="discount_price" id="discount_price" class="form-agri" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="0.00" style="padding-left: 52px;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Category</label>
                                <select name="category_id" class="form-agri">
                                    <option value="">Select category</option>
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <x-input label="Stock Quantity" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" />
                            </div>

                            <div class="col-12">
                                <label class="agri-label">Description</label>
                                <textarea name="description" class="form-agri" rows="4" placeholder="Product details, usage notes, and key specifications.">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-sliders-h"></i> Category Attributes
                        </h4>
                        <div id="vendor-attribute-fields" class="row g-4">
                            <div class="col-12 text-muted" style="font-size:13px;">Select a category to load dynamic attributes.</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a;">
                        <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 14px;">Product Settings</h5>
                        <div class="row g-4" style="margin-bottom: 8px;">
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Active</span>
                                <x-toggle name="is_active" :checked="old('is_active', $product->is_active ?? true)" />
                            </div>
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Returnable</span>
                                <x-toggle name="is_returnable" :checked="old('is_returnable', $product->is_returnable ?? true)" />
                            </div>
                            <div class="col-md-4" style="display:flex; align-items:center; justify-content:space-between;">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Refundable</span>
                                <x-toggle name="is_refundable" :checked="old('is_refundable', $product->is_refundable ?? true)" />
                            </div>
                        </div>

                        <div class="row g-4" style="margin-top: 4px;">
                            <div class="col-md-6">
                                <x-input label="Return Days" name="return_window_days" type="number" min="0" max="365" value="{{ old('return_window_days', $product->return_window_days ?? 7) }}" />
                            </div>
                            <div class="col-md-6">
                                <x-input label="Low Stock Warning" name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}" />
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Product Media</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Main Product Image</label>
                                <input type="file" name="image" id="main_product_image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">Gallery Upload</label>
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <x-button type="submit" variant="primary" icon="fas fa-save" style="flex:2; height:50px; font-size:16px;">{{ isset($product) ? 'Update Product' : 'Save Product' }}</x-button>
                        <x-button :href="route('vendor.products.index')" variant="outline" style="flex:1; height:50px; display:flex; align-items:center; justify-content:center;">Back</x-button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attributeMap = @json($attributeMap ?? []);
    const existingValues = @json(isset($product) ? ($product->attributes ?? collect())->mapWithKeys(function($attr){
        return [$attr->attribute_id => $attr->value];
    }) : []);

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeMulti(raw) {
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch (_) {
            return [];
        }
    }

    function renderAttributes(categoryId) {
        const wrap = document.getElementById('vendor-attribute-fields');
        const attrs = attributeMap[categoryId] || [];

        if (!attrs.length) {
            wrap.innerHTML = '<div class="col-12 text-muted" style="font-size:13px;">No attributes assigned to this category.</div>';
            return;
        }

        const html = attrs.map(attr => {
            const required = attr.is_required ? 'required' : '';
            const requiredMark = attr.is_required ? ' <span class="text-danger">*</span>' : '';
            const unitHint = attr.unit ? ` <small class="text-muted">(${esc(attr.unit)})</small>` : '';
            const fieldName = `attribute_values[${attr.id}]`;
            const current = existingValues[attr.id] || '';

            if (attr.type === 'multi-select') {
                const selected = normalizeMulti(current);
                const options = (attr.values || []).map(v => {
                    const pick = selected.includes(String(v)) ? 'selected' : '';
                    return `<option value="${esc(v)}" ${pick}>${esc(v)}</option>`;
                }).join('');

                return `<div class="col-md-6">
                    <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                    <select name="${fieldName}[]" class="form-agri" multiple ${required}>${options}</select>
                </div>`;
            }

            if (attr.type === 'select') {
                const options = (attr.values || []).map(v => {
                    const pick = String(current) === String(v) ? 'selected' : '';
                    return `<option value="${esc(v)}" ${pick}>${esc(v)}</option>`;
                }).join('');

                return `<div class="col-md-6">
                    <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                    <select name="${fieldName}" class="form-agri" ${required}>
                        <option value="">Select value</option>
                        ${options}
                    </select>
                </div>`;
            }

            const type = attr.type === 'number' ? 'number" step="any' : 'text';
            return `<div class="col-md-6">
                <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                <input type="${type}" name="${fieldName}" value="${esc(current)}" class="form-agri" ${required}>
            </div>`;
        }).join('');

        wrap.innerHTML = html;
    }

    const categorySelect = document.querySelector('select[name="category_id"]');
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            renderAttributes(this.value);
        });
        renderAttributes(categorySelect.value);
    }
});
</script>
@endpush
