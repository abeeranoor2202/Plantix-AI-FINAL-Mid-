@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.stock.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Stock Tracking</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">View Record</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Stock Details</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Read-only view of stock information.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; background: white;">
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

                <div style="margin-bottom: 40px;">
                    <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-info-circle"></i> Stock Information
                    </h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">PRODUCT NAME</label>
                            <input type="text" class="form-agri" value="{{ $stock->product->name ?? 'Deleted Product' }}" disabled style="height: 48px; font-size: 14px; font-weight: 700;">
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">VENDOR</label>
                            <input type="text" class="form-agri" value="{{ $stock->vendor->title ?? 'Platform Default' }}" disabled style="height: 48px; font-size: 14px; font-weight: 700;">
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">QUANTITY</label>
                            <input type="text" class="form-agri" value="{{ (int) $stock->quantity }}" disabled style="height: 48px; font-size: 16px; font-weight: 800;">
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">RESERVED</label>
                            <input type="text" class="form-agri" value="{{ (int) $stock->reserved_quantity }}" disabled style="height: 48px; font-size: 16px; font-weight: 800;">
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">THRESHOLD</label>
                            <input type="text" class="form-agri" value="{{ (int) $stock->low_stock_threshold }}" disabled style="height: 48px; font-size: 16px; font-weight: 800;">
                        </div>
                        <div class="col-md-4">
                            <label class="agri-label">STATUS</label>
                            <input type="text" class="form-agri" value="{{ strtoupper($stock->display_status) }}" disabled style="height: 48px; font-size: 14px; font-weight: 800;">
                        </div>
                    </div>
                </div>

                <div style="padding-top: 32px; border-top: 1px solid var(--agri-border); display: flex; gap: 12px;">
                    <a href="{{ route('admin.stock.edit', $stock->id) }}" class="btn-agri btn-agri-primary" style="padding: 12px 32px; height: 48px; display: flex; align-items: center; text-decoration: none; font-weight: 700;">
                        <i class="fas fa-pen" style="margin-right: 8px;"></i> Edit Stock
                    </a>
                    <a href="{{ route('admin.stock.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 32px; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 600;">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
