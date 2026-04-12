@extends('vendor.layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <div class="mb-4 d-flex align-items-center gap-3">
            <a href="{{ route('vendor.products.index') }}" class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                <i class="fas fa-arrow-left text-muted"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="{{ isset($product) ? 'fas fa-edit' : 'fas fa-plus-circle' }} me-2 text-primary fs-5"></i>
                    {{ isset($product) ? 'Edit Product Details' : 'Add New Product' }}
                </h4>
                <p class="text-muted small m-0">{{ isset($product) ? 'Update the details for your existing product.' : 'Fill out the form below to list a new product in the marketplace.' }}</p>
            </div>
        </div>

        <div class="card-agri p-4 p-md-5 border-0 shadow-sm">
            <form method="POST"
                  action="{{ isset($product) ? route('vendor.products.update', $product->id) : route('vendor.products.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($product)) @method('PUT') @endif

                <div class="row g-4">
                    {{-- Name --}}
                    <div class="col-12">
                        <label class="form-label text-dark fw-bold small mb-2">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-agri @error('name') border-danger @enderror"
                               value="{{ old('name', $product->name ?? '') }}" placeholder="Enter a descriptive product name" required>
                        @error('name')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label text-dark fw-bold small mb-2">Description</label>
                        <textarea name="description" rows="5"
                                  class="form-agri @error('description') border-danger @enderror" placeholder="Describe the product features, benefits, usage instructions, and key details...">{{ old('description', $product->description ?? '') }}</textarea>
                        @error('description')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Price --}}
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-bold small mb-2">Selling Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-success bg-opacity-10 text-success border-0 px-3 fw-bold">{{ config('plantix.currency_symbol', 'PKR') }}</span>
                            <input type="number" name="price" step="0.01" min="0"
                                   class="form-agri border-start-0 ps-3 @error('price') border-danger @enderror" style="border-radius: 0 0.5rem 0.5rem 0; outline: none;"
                                   value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" required>
                        </div>
                        @error('price')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Discount --}}
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-bold small mb-2">Discounted Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-warning bg-opacity-10 text-warning border-0 px-3 fw-bold">{{ config('plantix.currency_symbol', 'PKR') }}</span>
                            <input type="number" name="discount_price" step="0.01" min="0"
                                   class="form-agri border-start-0 ps-3 @error('discount_price') border-danger @enderror" style="border-radius: 0 0.5rem 0.5rem 0; outline: none;"
                                   value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="0.00 (Optional)">
                        </div>
                        @error('discount_price')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Category --}}
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-bold small mb-2">Category</label>
                        <select name="category_id" class="form-agri @error('category_id') border-danger @enderror">
                            <option value="">— Select Category —</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- SKU --}}
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-bold small mb-2">SKU (Stock Keeping Unit)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0 px-3"><i class="fas fa-barcode"></i></span>
                            <input type="text" name="sku"
                                   class="form-agri border-start-0 ps-0 @error('sku') border-danger @enderror" style="border-radius: 0 0.5rem 0.5rem 0;"
                                   value="{{ old('sku', $product->sku ?? '') }}" placeholder="e.g. PRD-001">
                        </div>
                        @error('sku')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Configuration section separator --}}
                    <div class="col-12 mt-5 mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="fw-bold text-dark m-0"><i class="fas fa-cogs text-muted me-2"></i>Product Configuration</h5>
                            <div class="flex-grow-1 border-top"></div>
                        </div>
                    </div>

                    {{-- Stock Tracking --}}
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded-3 h-100 border border-dashed text-dark">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold m-0 text-dark">Inventory Tracking</h6>
                                    <p class="text-muted small m-0">Monitor product stock levels</p>
                                </div>
                                <div class="form-check form-switch m-0" style="font-size: 1.25rem;">
                                    <input type="checkbox" name="track_stock" id="track_stock"
                                           class="form-check-input shadow-none cursor-pointer" style="margin-top: 0;" value="1"
                                           {{ old('track_stock', $product->track_stock ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            
                            <div id="stock_qty_wrap" class="mt-4 pt-3 border-top border-muted text-dark">
                                <label class="form-label text-dark fw-bold small mb-2">Available Stock Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted border-end-0 px-3"><i class="fas fa-box"></i></span>
                                    <input type="number" name="stock_quantity" min="0" id="stock_quantity_input"
                                           class="form-agri border-start-0 ps-0 text-dark" style="border-radius: 0 0.5rem 0.5rem 0;"
                                           value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                                </div>
                                @error('stock_quantity')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Status Visibility --}}
                    <div class="col-md-6">
                        <div class="bg-light p-4 rounded-3 h-100 border border-dashed">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold m-0 text-dark">Visibility Status</h6>
                                    <p class="text-muted small m-0">Show or hide on storefront</p>
                                </div>
                                <div class="form-check form-switch m-0" style="font-size: 1.25rem;">
                                    <input type="checkbox" name="is_active" id="is_active"
                                           class="form-check-input shadow-none cursor-pointer" style="margin-top: 0;" value="1"
                                           {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top border-muted">
                                <div class="d-flex gap-3 align-items-start">
                                    <i class="fas fa-info-circle text-primary mt-1"></i>
                                    <p class="text-muted small mb-0" style="line-height: 1.6;">Turn off to hide this product from customers while preserving its data. It will not appear in search results or category pages.</p>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top border-muted">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="fw-bold m-0 text-dark">Returnable</h6>
                                        <p class="text-muted small m-0">Allow customer return requests</p>
                                    </div>
                                    <div class="form-check form-switch m-0" style="font-size: 1.25rem;">
                                        <input type="checkbox" name="is_returnable" id="is_returnable"
                                               class="form-check-input shadow-none cursor-pointer" style="margin-top: 0;" value="1"
                                               {{ old('is_returnable', $product->is_returnable ?? true) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <label class="form-label text-dark fw-bold small mb-2">Return Window (Days)</label>
                                <input type="number" min="0" max="365" name="return_window_days"
                                       class="form-agri @error('return_window_days') border-danger @enderror"
                                       value="{{ old('return_window_days', $product->return_window_days ?? 7) }}"
                                       placeholder="7">
                                @error('return_window_days')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="col-12 mt-4">
                        <div class="p-4 rounded-3 border">
                            <label class="form-label text-dark fw-bold small mb-3">Main Product Image <span class="text-danger">*</span></label>
                            
                            <div class="row align-items-center gap-4">
                                @if(isset($product) && $product->image)
                                    <div class="col-auto">
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ asset('storage/'.$product->image) }}"
                                                 class="rounded-3 shadow-sm border" width="120" height="120" style="object-fit:cover;" alt="Current Image">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success shadow-sm">
                                                <i class="fas fa-check"></i>
                                                <span class="visually-hidden">current image</span>
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                <div class="col">
                                    <input type="file" name="image" class="form-agri py-3 bg-white @error('image') border-danger @enderror"
                                           accept="image/*">
                                    <div class="d-flex gap-2 mt-2 text-muted small fw-medium">
                                        <i class="fas fa-image text-primary"></i>
                                        <span>Square images recommended. Maximum file size: 2MB. Supported formats: JPG, PNG, WEBP.</span>
                                    </div>
                                    @error('image')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Options --}}
                    <div class="col-12 mt-5 pt-4 border-top d-flex align-items-center justify-content-between">
                        <a href="{{ route('vendor.products.index') }}" class="btn-agri btn-agri-outline px-4 text-dark shadow-sm">Cancel</a>
                        <button type="submit" class="btn-agri btn-agri-primary px-5 py-2 fs-6 shadow-sm">
                            <i class="fas {{ isset($product) ? 'fa-save' : 'fa-paper-plane' }} me-2"></i>{{ isset($product) ? 'Save Changes' : 'Publish Product' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted small"><i class="fas fa-shield-alt text-success me-1"></i> Listings are monitored for quality. Please ensure all product details are accurate.</p>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trackCheck = document.getElementById('track_stock');
        const qtyWrap    = document.getElementById('stock_qty_wrap');
        const qtyInput   = document.getElementById('stock_quantity_input');
        
        function toggleQty() { 
            if(trackCheck.checked) {
                qtyWrap.style.display = 'block';
                qtyInput.setAttribute('required', 'required');
            } else {
                qtyWrap.style.display = 'none';
                qtyInput.removeAttribute('required');
            }
        }
        
        trackCheck.addEventListener('change', toggleQty);
        toggleQty(); // Initial call
        
        // Add color transition to switch labels based on state
        const isAct = document.getElementById('is_active');
        isAct.addEventListener('change', function() {
            this.closest('.card').style.borderLeft = this.checked ? '4px solid var(--agri-primary)' : '4px solid #9CA3AF';
        });
        
        // Initialize border based on initial state
        document.getElementById('is_active').closest('.bg-light').style.borderLeft = document.getElementById('is_active').checked ? '4px solid var(--agri-primary)' : '4px solid transparent';
    });
</script>
@endpush
