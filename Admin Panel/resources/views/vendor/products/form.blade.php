@extends('vendor.layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 24px;">
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="margin-bottom: 24px; gap: 12px;">
        <div>
            <div class="small text-muted" style="font-weight: 600; margin-bottom: 6px;">
                <a href="{{ route('vendor.dashboard') }}" class="text-muted" style="text-decoration: none;">Dashboard</a>
                <span class="mx-1">/</span>
                <a href="{{ route('vendor.products.index') }}" class="text-muted" style="text-decoration: none;">Products</a>
                <span class="mx-1">/</span>
                <span>{{ isset($product) ? 'Edit Product' : 'Create Product' }}</span>
            </div>
            <h1 class="mb-0" style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark);">{{ isset($product) ? 'Edit Product' : 'Create Product' }}</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 24px; border-radius: 12px;">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ isset($product) ? route('vendor.products.update', $product->id) : route('vendor.products.store') }}" enctype="multipart/form-data" id="vendor-product-form">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <x-card class="card-agri" bodyClass="p-4 p-md-5">
                    <div class="row">
                        <div class="col-12">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">Product Details</h4>
                        </div>

                        <div class="col-md-6">
                            <x-input label="Product Name" name="name" :required="true" type="text" value="{{ old('name', $product->name ?? '') }}" placeholder="e.g. Premium NPK Fertilizer" />
                        </div>
                        <div class="col-md-6">
                            <x-input label="SKU" name="sku" type="text" value="{{ old('sku', $product->sku ?? '') }}" placeholder="AGRI-XXX-000" />
                        </div>

                        <div class="col-md-6">
                            <x-input label="Base Price" name="price" :required="true" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" />
                        </div>
                        <div class="col-md-6">
                            <x-input label="Discounted Price" name="discount_price" type="number" step="0.01" min="0" value="{{ old('discount_price', $product->discount_price ?? '') }}" />
                        </div>

                        <div class="col-md-6">
                            <label class="agri-label">Category</label>
                            <select name="category_id" id="category_id" class="form-agri">
                                <option value="">Select category</option>
                                @php
                                    $myVendorId = auth('vendor')->user()->vendor->id ?? null;
                                    $globalCats = ($categories ?? collect())->filter(fn($c) => is_null($c->vendor_id));
                                    $myCats     = ($categories ?? collect())->filter(fn($c) => (int)$c->vendor_id === (int)$myVendorId);
                                    $otherCats  = ($categories ?? collect())->filter(fn($c) => !is_null($c->vendor_id) && (int)$c->vendor_id !== (int)$myVendorId);
                                @endphp
                                @if($globalCats->isNotEmpty())
                                    <optgroup label="── Global (Admin)">
                                        @foreach($globalCats as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($myCats->isNotEmpty())
                                    <optgroup label="── My Categories">
                                        @foreach($myCats as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($otherCats->isNotEmpty())
                                    <optgroup label="── Other Vendors">
                                        @foreach($otherCats as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
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

                    <hr style="margin: 24px 0; border-color: var(--agri-border);">

                    <div class="mb-4">
                        <h4 style="font-size: 17px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 14px;">Category Attributes</h4>
                        <p class="text-muted mb-3" style="font-size: 13px;">Attributes are loaded from the selected category and saved directly to this product.</p>
                        <div id="vendor-attribute-fields" class="row">
                            <div class="col-12 text-muted" style="font-size: 13px;">Select a category to load dynamic attributes.</div>
                        </div>
                    </div>

                    <hr style="margin: 24px 0; border-color: var(--agri-border);">

                    <div class="mb-4">
                        <h4 style="font-size: 17px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Product Settings</h4>
                        <div class="row">
                            <div class="col-md-4 mb-3 d-flex align-items-center justify-content-between">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Active</span>
                                <x-toggle name="is_active" :checked="old('is_active', $product->is_active ?? true)" />
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-center justify-content-between">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Returnable</span>
                                <x-toggle name="is_returnable" :checked="old('is_returnable', $product->is_returnable ?? true)" />
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-center justify-content-between">
                                <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Refundable</span>
                                <x-toggle name="is_refundable" :checked="old('is_refundable', $product->is_refundable ?? true)" />
                            </div>
                            <div class="col-md-6">
                                <x-input label="Return Days" name="return_window_days" type="number" min="0" max="365" value="{{ old('return_window_days', $product->return_window_days ?? 7) }}" />
                            </div>
                            <div class="col-md-6">
                                <x-input label="Low Stock Warning" name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}" />
                            </div>
                        </div>
                    </div>

                    <hr style="margin: 24px 0; border-color: var(--agri-border);">

                    <div class="mb-4">
                        <h4 style="font-size: 17px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Product Media</h4>
                        <div class="row">
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

                    <div class="d-flex flex-wrap" style="gap: 12px; border-top: 1px solid var(--agri-border); padding-top: 24px;">
                        <x-button type="submit" variant="primary" icon="fas fa-save">{{ isset($product) ? 'Update Product' : 'Save Product' }}</x-button>
                        <x-button :href="route('vendor.products.index')" variant="outline">Back</x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingValues = @json($attributeValues ?? []);
    const endpointTemplate = @json(route('vendor.products.category-attributes', ['category' => '__CATEGORY__']));
    const attributeWrap = document.getElementById('vendor-attribute-fields');
    const categorySelect = document.getElementById('category_id');

    function endpointForCategory(categoryId) {
        return endpointTemplate.replace('__CATEGORY__', String(categoryId));
    }

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeMulti(raw) {
        if (Array.isArray(raw)) {
            return raw.map(String);
        }

        if (raw === null || raw === undefined || raw === '') {
            return [];
        }

        if (typeof raw === 'string' && raw.trim().startsWith('[')) {
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed.map(String) : [];
            } catch (e) {
                return [];
            }
        }

        return [String(raw)];
    }

    function normalizeBoolean(raw) {
        if (raw === null || raw === undefined || raw === '') {
            return false;
        }

        if (typeof raw === 'boolean') {
            return raw;
        }

        const normalized = String(raw).trim().toLowerCase();
        return ['1', 'true', 'yes', 'on'].includes(normalized);
    }

    function renderAttributes(attrs) {
        if (!Array.isArray(attrs) || attrs.length === 0) {
            attributeWrap.innerHTML = '<div class="col-12 text-muted" style="font-size: 13px;">No attributes assigned to this category.</div>';
            return;
        }

        const html = attrs.map(function (attr) {
            const required = attr.is_required ? 'required' : '';
            const requiredMark = attr.is_required ? ' <span class="text-danger">*</span>' : '';
            const unitHint = attr.unit ? ` <small class="text-muted">(${esc(attr.unit)})</small>` : '';
            const fieldName = `attribute_values[${attr.id}]`;
            const current = existingValues[String(attr.id)] ?? existingValues[attr.id] ?? '';
            const values = (Array.isArray(attr.values) ? attr.values : []).map(function (item) {
                if (item && typeof item === 'object') {
                    return String(item.value ?? '');
                }
                return String(item ?? '');
            }).filter(Boolean);

            if (attr.type === 'multi-select') {
                const selected = normalizeMulti(current);
                const options = values.map(function (v) {
                    const pick = selected.includes(String(v)) ? 'selected' : '';
                    return `<option value="${esc(v)}" ${pick}>${esc(v)}</option>`;
                }).join('');

                return `<div class="col-md-6 mb-3">
                    <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                    <select name="${fieldName}[]" class="form-agri" multiple ${required}>${options}</select>
                </div>`;
            }

            if (attr.type === 'select') {
                const options = values.map(function (v) {
                    const pick = String(current) === String(v) ? 'selected' : '';
                    return `<option value="${esc(v)}" ${pick}>${esc(v)}</option>`;
                }).join('');

                return `<div class="col-md-6 mb-3">
                    <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                    <select name="${fieldName}" class="form-agri" ${required}>
                        <option value="">Select value</option>
                        ${options}
                    </select>
                </div>`;
            }

            if (attr.type === 'boolean') {
                const checked = normalizeBoolean(current) ? 'checked' : '';

                return `<div class="col-md-6 mb-3">
                    <label class="agri-label d-block">${esc(attr.name)}${requiredMark}${unitHint}</label>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <input type="hidden" name="${fieldName}" value="0">
                        <label class="switch mb-0">
                            <input type="checkbox" name="${fieldName}" value="1" ${checked} ${required}>
                            <span class="slider"></span>
                        </label>
                        <small class="text-muted">Toggle On/Off</small>
                    </div>
                </div>`;
            }

            const type = attr.type === 'number' ? 'number" step="any' : 'text';
            return `<div class="col-md-6 mb-3">
                <label class="agri-label">${esc(attr.name)}${requiredMark}${unitHint}</label>
                <input type="${type}" name="${fieldName}" value="${esc(current)}" class="form-agri" ${required}>
            </div>`;
        }).join('');

        attributeWrap.innerHTML = html;
    }

    async function loadAttributes(categoryId) {
        if (!categoryId) {
            attributeWrap.innerHTML = '<div class="col-12 text-muted" style="font-size: 13px;">Select a category to load dynamic attributes.</div>';
            return;
        }

        attributeWrap.innerHTML = '<div class="col-12 text-muted" style="font-size: 13px;">Loading attributes...</div>';

        try {
            const response = await fetch(endpointForCategory(categoryId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load attributes');
            }

            const data = await response.json();
            renderAttributes(data.attributes || []);
        } catch (error) {
            attributeWrap.innerHTML = '<div class="col-12 text-danger" style="font-size: 13px;">Unable to load attributes for this category. Please retry.</div>';
        }
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            loadAttributes(this.value);
        });

        loadAttributes(categorySelect.value);
    }
});
</script>
@endpush
