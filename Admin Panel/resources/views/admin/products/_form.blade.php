{{-- Redesigned Premium AgriTech Product Form Partial --}}

<div class="row g-4">

    {{-- ── Left column: core details ── --}}
    <div class="col-lg-8">

        {{-- Core Specifications --}}
        <div class="card-agri mb-4" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-barcode"></i>
                </div>
                <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Core Specifications</h5>
            </div>

            <div class="mb-4">
                <label class="agri-label">Product Designation <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-agri @error('name') is-invalid @enderror"
                       value="{{ old('name', $product->name ?? '') }}" placeholder="e.g. Premium NPK Fertilizer 20-20-20" required style="height: 52px; font-size: 16px; font-weight: 700;">
                @error('name') <div class="invalid-feedback @error('name') d-block @enderror" style="font-weight: 700; font-size: 12px; margin-top: 8px;">{{ $message }}</div> @enderror
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="agri-label">SKU ID</label>
                    <input type="text" name="sku" class="form-agri @error('sku') is-invalid @enderror"
                           value="{{ old('sku', $product->sku ?? '') }}" placeholder="AGRI-XXX-000">
                    @error('sku') <div class="invalid-feedback d-block" style="font-weight: 700;">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="agri-label">Base Valuation (PKR) <span class="text-danger">*</span></label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 800; color: var(--agri-text-muted); font-size: 12px;">RS</span>
                        <input type="number" step="0.01" min="0" name="price"
                               class="form-agri @error('price') is-invalid @enderror" style="padding-left: 40px;"
                               value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" required>
                    </div>
                    @error('price') <div class="invalid-feedback d-block" style="font-weight: 700;">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="agri-label">Active Listing Price</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 800; color: var(--agri-primary); font-size: 12px;">RS</span>
                        <input type="number" step="0.01" min="0" name="sale_price"
                               class="form-agri @error('sale_price') is-invalid @enderror" style="padding-left: 40px; border-color: var(--agri-primary)40; background: var(--agri-primary-light)20;"
                               value="{{ old('sale_price', $product->sale_price ?? '') }}" placeholder="Discounted Price">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="agri-label">Technical Abstract (Short)</label>
                <input type="text" name="short_description"
                       class="form-agri @error('short_description') is-invalid @enderror"
                       value="{{ old('short_description', $product->short_description ?? '') }}"
                       placeholder="Concise 1-line summary for mobile cards..." maxlength="255">
            </div>

            <div>
                <label class="agri-label">Full Technical Manifest (Description)</label>
                <textarea name="description" rows="6" placeholder="Document full product properties, usage instructions, and safety protocols..."
                          class="form-agri @error('description') is-invalid @enderror" style="padding: 20px;">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
        </div>

        {{-- Ecosystem Alignment --}}
        <div class="card-agri" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                <div style="width: 36px; height: 36px; background: var(--agri-secondary-light); color: var(--agri-primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Ecosystem Alignment</h5>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="agri-label">Taxonomy Branch <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-agri @error('category_id') is-invalid @enderror" required style="font-weight: 700;">
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="agri-label">Fulfillment Node (Vendor) <span class="text-danger">*</span></label>
                    <select name="vendor_id" class="form-agri @error('vendor_id') is-invalid @enderror" required style="font-weight: 700; border-color: var(--agri-secondary);">
                        <option value="">— Select Partner —</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('vendor_id', $product->vendor_id ?? '') == $vendor->id)>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="agri-label">Quantum Unit</label>
                    <input type="text" name="unit" class="form-agri"
                           value="{{ old('unit', $product->unit ?? '') }}"
                           placeholder="e.g. 50kg, 1Litre, Pkt">
                </div>
                <div class="col-md-4">
                    <label class="agri-label">Inventory Balance</label>
                    <input type="number" min="0" name="stock_quantity" class="form-agri" style="font-weight: 800;"
                           value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="agri-label">Critical Threshold</label>
                    <input type="number" min="0" name="low_stock_threshold" class="form-agri" style="border-color: #FEF2F2; color: var(--agri-error); font-weight: 800;"
                           value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right column: assets & governance ── --}}
    <div class="col-lg-4">

        {{-- Primary Asset --}}
        <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <label class="agri-label mb-3">Primary Node Visual</label>
            <div style="background: var(--agri-bg); border: 2px dashed var(--agri-border); border-radius: 20px; padding: 24px; text-align: center; position: relative;">
                @isset($product)
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" class="rounded shadow-sm mb-3" style="width:100%; height:180px; object-fit:cover; border:2px solid white;">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-primary);">REPLACE CURRENT ASSET</p>
                    @endif
                @endisset
                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                        <i class="fas fa-camera"></i>
                    </div>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*" style="font-size: 12px;">
                    <p style="font-size: 10px; color: var(--agri-text-muted); margin: 4px 0 0 0;">Supported: JPG, PNG, WEBP (Max 2MB)</p>
                </div>
            </div>
        </div>

        {{-- Asset Mosaic --}}
        <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <label class="agri-label mb-3">Gallery Mosaic</label>
            @isset($product)
                @if($product->images && $product->images->count())
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($product->images->where('is_primary', false) as $img)
                            <img src="{{ asset('storage/'.$img->path) }}" class="rounded shadow-sm" style="width:52px; height:52px; object-fit:cover; border:1px solid var(--agri-border);">
                        @endforeach
                    </div>
                @endif
            @endisset
            <input type="file" name="gallery[]" class="form-control form-control-sm" accept="image/*" multiple style="font-size: 12px;">
            <p style="font-size: 10px; color: var(--agri-text-muted); margin: 8px 0 0 0;">Upload multiple angles for enhanced catalog discovery.</p>
        </div>

        {{-- Governance Controls --}}
        <div class="card-agri" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <label class="agri-label mb-3">Operational Governance</label>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: var(--agri-bg); padding: 14px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-globe text-primary" style="font-size: 13px;"></i>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">Live Status</span>
                    </div>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} style="width: 40px; height: 20px;"></div>
                </div>

                <div style="background: var(--agri-bg); padding: 14px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-star text-warning" style="font-size: 13px;"></i>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">Featured SKU</span>
                    </div>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} style="width: 40px; height: 20px;"></div>
                </div>

                <div style="background: var(--agri-bg); padding: 14px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-undo text-danger" style="font-size: 13px;"></i>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">Returnable</span>
                    </div>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" name="is_returnable" value="1" {{ old('is_returnable', $product->is_returnable ?? true) ? 'checked' : '' }} style="width: 40px; height: 20px;"></div>
                </div>
            </div>

            <hr style="margin: 24px 0; opacity: 0.1;">

            <div class="row g-3">
                <div class="col-6">
                    <label class="agri-label">Return Window</label>
                    <div style="position: relative;">
                        <input type="number" name="return_window_days" class="form-agri" style="padding-right: 44px; font-weight: 700;"
                               value="{{ old('return_window_days', $product->return_window_days ?? 7) }}">
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: 10px; font-weight: 800; color: var(--agri-text-muted);">DAYS</span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="agri-label">Tax Protocol</label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" name="tax_rate" class="form-agri" style="padding-right: 32px; font-weight: 700;"
                               value="{{ old('tax_rate', $product->tax_rate ?? 5) }}">
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 800; color: var(--agri-text-muted);">%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .agri-label { font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
    .form-agri:focus { border-color: var(--agri-primary) !important; background: white !important; }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
