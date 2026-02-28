@extends('layouts.app')

@section('title', 'Reconfigure SKU')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{!! route('admin.products.index') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Inventory Ledger</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">SKU Reconfiguration</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Reconfigure Asset: {{ $product->sku }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Modify the commercial parameters and operational state of this platform SKU.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700; padding: 12px 24px;">
            <i class="fas fa-arrow-left"></i> Return to Ledger
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4" style="border-radius: 16px; border: none; background: #FEF2F2; color: var(--agri-error); font-weight: 700; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>VALIDATION PROTOCOL FAILURE</span>
            </div>
            <ul class="mb-0" style="font-size: 13px; font-weight: 600; opacity: 0.9;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
        
        <div style="margin-top: 40px; padding: 32px 40px; background: white; border-top: 1px solid var(--agri-border); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); display: flex; justify-content: flex-end; align-items: center; gap: 24px;">
            <div style="flex: 1; display: flex; align-items: center; gap: 12px; color: var(--agri-text-muted);">
                <i class="fas fa-history"></i>
                <span style="font-size: 12px; font-weight: 600;">Last Modified: {{ $product->updated_at->diffForHumans() }}</span>
            </div>
            
            <a href="{{ route('admin.products.index') }}" class="btn-agri btn-agri-outline" style="padding: 14px 40px; text-decoration: none; font-weight: 700; min-width: 140px; display: flex; align-items: center; justify-content: center;">{{trans('lang.cancel')}}</a>
            
            <button type="submit" class="btn-agri btn-agri-primary" style="padding: 14px 60px; font-weight: 800; font-size: 16px; border-radius: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 8px 20px rgba(var(--agri-primary-rgb), 0.2);">
                <i class="fas fa-sync-alt"></i> SYNC PARAMETERS
            </button>
        </div>
    </form>

</div>
@endsection
