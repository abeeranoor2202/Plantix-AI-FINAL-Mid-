@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.stock.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Stock Tracking</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Record</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Stock Configuration</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Modify inventory parameters and thresholds for specific products.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if($errors->any())
                <div style="background: #FEF2F2; color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px;">
                    <ul style="margin: 0; padding-left: 20px; font-weight: 600; font-size: 14px;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="card-agri" style="padding: 32px; background: white;">
                {{-- Product Summary --}}
                <div style="display: flex; gap: 24px; align-items: center; padding: 20px; background: var(--agri-bg); border-radius: 16px; margin-bottom: 32px;">
                    @if($stock->product && $stock->product->photo)
                        <img src="{{ $stock->product->photo }}" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    @else
                        <div style="width: 64px; height: 64px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; color: var(--agri-border); border: 1px solid var(--agri-border);">
                            <i class="fas fa-image fa-lg"></i>
                        </div>
                    @endif
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--agri-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Hardware/Product Entry</div>
                        <h4 style="font-size: 20px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">{{ $stock->product->name ?? 'Deleted Product' }}</h4>
                        <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-top: 2px;">Provider: {{ $stock->vendor->title ?? 'Platform Default' }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.stock.update', $stock->id) }}">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom: 40px;">
                        <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-sliders-h"></i> Inventory Parameters
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="agri-label">CURRENT QUANTITY <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-agri @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity', $stock->quantity) }}" min="0" required 
                                       style="height: 48px; font-size: 16px; font-weight: 700;">
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="agri-label">LOW STOCK ALERT AT</label>
                                <input type="number" name="low_stock_threshold"
                                       class="form-agri @error('low_stock_threshold') is-invalid @enderror"
                                       value="{{ old('low_stock_threshold', $stock->low_stock_threshold) }}" min="0"
                                       style="height: 48px; font-size: 16px; font-weight: 700;">
                                @error('low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="agri-label">SKU IDENTIFIER</label>
                                <input type="text" name="sku" class="form-agri @error('sku') is-invalid @enderror"
                                       value="{{ old('sku', $stock->sku) }}" maxlength="100"
                                       placeholder="Product SKU..."
                                       style="height: 48px; font-size: 14px; font-weight: 600;">
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div style="padding-top: 32px; border-top: 1px solid var(--agri-border); display: flex; gap: 12px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="padding: 12px 32px; height: 48px; font-weight: 700;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save Stock Changes
                        </button>
                        <a href="{{ route('admin.stock.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 32px; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 600;">Cancel</a>
                    </div>
                </form>

                {{-- Separate Quick Adjust Section --}}
                <div style="margin-top: 48px; padding: 32px; border-radius: 20px; background: #fffbeb; border: 1px solid #fde68a;">
                    <h5 style="font-size: 16px; font-weight: 800; color: #92400e; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-bolt"></i> Quick Inventory Adjustment
                    </h5>
                    <p style="font-size: 13px; color: #b45309; font-weight: 600; margin-bottom: 24px;">Use this to quickly add or remove stock without manual recalculation.</p>
                    
                    <form method="POST" action="{{ route('admin.stock.adjust', $stock->id) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="agri-label" style="color: #92400e;">ADJUSTMENT (+/-)</label>
                                <input type="number" name="adjustment" class="form-agri" placeholder="e.g. 50 or -10" required 
                                       style="background: white; border-color: #fde68a; height: 44px; font-weight: 700;">
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label" style="color: #92400e;">REASON FOR ADJUSTMENT</label>
                                <input type="text" name="note" class="form-agri" placeholder="Restock, inventory count, etc..."
                                       style="background: white; border-color: #fde68a; height: 44px;">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn-agri" style="width: 100%; height: 44px; background: #d97706; color: white; border: none; font-weight: 700; border-radius: 12px;">Apply Adjustment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
