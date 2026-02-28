@extends('layouts.app')

@section('title', 'Global Inventory Ledger')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">E-Commerce Hub</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Inventory Intelligence</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Global Inventory Ledger</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Unified management of agriculture supplies, equipment, and digital assets.</p>
        </div>
        <div style="display: flex; gap: 16px;">
            <div style="background: white; padding: 10px 20px; border-radius: 14px; border: 1px solid var(--agri-border); font-size: 13px; font-weight: 800; color: var(--agri-primary); display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <i class="fas fa-warehouse"></i>
                TOTAL SKU: {{ $products->total() }}
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                <i class="fas fa-plus"></i> Register SKU
            </a>
        </div>
    </div>

    {{-- Strategy Filters --}}
    <div class="card-agri mb-4" style="padding: 24px 32px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: white;">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div class="row g-4">
                <div class="col-lg-3">
                    <label class="agri-filter-label">SKU Discovery</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-primary); opacity: 0.6;"></i>
                        <input type="text" name="search" class="form-agri" style="padding-left: 44px; font-size: 14px; font-weight: 600;"
                               placeholder="Scan Name or SKU..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="agri-filter-label">Taxonomy Area</label>
                    <select name="category_id" class="form-agri" style="font-size: 14px; font-weight: 600;">
                        <option value="">All Branches</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="agri-filter-label">Partner Node</label>
                    <select name="vendor_id" class="form-agri" style="font-size: 14px; font-weight: 600;">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(($filters['vendor_id'] ?? '') == $vendor->id)>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="agri-filter-label">Ledger Status</label>
                    <select name="is_active" class="form-agri" style="font-size: 14px; font-weight: 600;">
                        <option value="">All States</option>
                        <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active Portfolio</option>
                        <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Archived/Inactive</option>
                    </select>
                </div>
                <div class="col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; font-weight: 800; letter-spacing: 0.5px;">
                        APPLY FILTERS
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn-agri btn-agri-outline" style="min-width: 100px; text-decoration: none; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                        RESET
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Inventory Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); background: white;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">SKU Item Details</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Ecosystem Segments</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Valuation</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Inventory Density</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Operational State</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="product-row" style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                            <td style="padding: 24px 32px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="position: relative;">
                                        <div style="width: 56px; height: 56px; border-radius: 14px; border: 2px solid var(--agri-bg); overflow: hidden; background: white; flex-shrink: 0;">
                                            @if($product->image)
                                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--agri-text-muted); background: var(--agri-bg);">
                                                    <i class="fas fa-seedling" style="font-size: 20px; opacity: 0.5;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        @if($product->is_featured)
                                            <div style="position: absolute; top: -6px; right: -6px; background: var(--agri-secondary); color: var(--agri-primary-dark); width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" title="Featured Product">
                                                <i class="fas fa-star"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ $product->name }}</div>
                                        <div style="font-size: 11px; font-weight: 800; color: var(--agri-primary); text-transform: uppercase; margin-top: 4px;">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 24px 32px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-folder" style="font-size: 11px; color: var(--agri-primary); opacity: 0.7;"></i>
                                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $product->category->name ?? 'Unmapped' }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-store" style="font-size: 11px; color: var(--agri-secondary); opacity: 0.7;"></i>
                                        <span style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted);">{{ $product->vendor->name ?? 'Direct' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 24px 32px;">
                                <div style="font-weight: 900; color: var(--agri-primary-dark); font-size: 16px; letter-spacing: -0.5px;">
                                    <span style="font-size: 12px; font-weight: 700; opacity: 0.6; margin-right: 2px;">PKR</span>{{ number_format($product->price, 0) }}
                                </div>
                                <div style="font-size: 10px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Per Unit</div>
                            </td>
                            <td style="padding: 24px 32px;" class="text-center">
                                @php $stock = $product->stock_quantity ?? 0; @endphp
                                @if($stock <= 10)
                                    <div style="background: #FEF2F2; color: #991B1B; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; border: 1px solid #fee2e2; display: inline-flex; align-items: center; gap: 8px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                        {{ $stock }} <span style="font-size: 10px; opacity: 0.7;">CRITICAL</span>
                                    </div>
                                @else
                                    <div style="background: #F0FDF4; color: #166534; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; border: 1px solid #dcfce7; display: inline-flex; align-items: center; gap: 8px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                                        {{ $stock }} <span style="font-size: 10px; opacity: 0.7;">OPTIMAL</span>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 24px 32px;" class="text-center">
                                @if($product->is_active)
                                    <div style="display: inline-flex; align-items: center; gap: 8px; color: var(--agri-primary); background: var(--agri-primary-light); padding: 4px 14px; border-radius: 100px; font-size: 11px; font-weight: 900; border: 1px solid var(--agri-primary)30;">
                                        <i class="fas fa-check-circle" style="font-size: 10px;"></i> LIVE
                                    </div>
                                @else
                                    <div style="display: inline-flex; align-items: center; gap: 8px; color: #6B7280; background: #F3F4F6; padding: 4px 14px; border-radius: 100px; font-size: 11px; font-weight: 900; border: 1px solid #E5E7EB;">
                                        <i class="fas fa-archive" style="font-size: 10px;"></i> ARCHIVED
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 24px 32px;" class="text-end">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-agri" style="padding: 10px 14px; background: var(--agri-bg); color: var(--agri-text-heading); border-radius: 12px; text-decoration: none; font-size: 13px; font-weight: 700; border: none;" title="Reconfigure SKU">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="d-inline" onsubmit="return confirm('CRITICAL: Decommission this SKU from the global ledger?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 10px 14px; background: #FEF2F2; color: var(--agri-error); border-radius: 12px; border: none;" title="Decommission SKU">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 100px 32px; text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                                    <div style="width: 80px; height: 80px; background: var(--agri-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--agri-text-muted); font-size: 32px;">
                                        <i class="fas fa-box-open" style="opacity: 0.4;"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-weight: 800; color: var(--agri-text-heading);">REGISTRY IS VOID</h4>
                                        <p style="margin: 8px 0 0 0; font-size: 14px; color: var(--agri-text-muted); max-width: 400px;">Adjust your intelligence filters or initiate a new SKU registration to populate the ledger.</p>
                                    </div>
                                    <a href="{{ route('admin.products.create') }}" class="btn-agri btn-agri-primary" style="padding: 12px 32px; text-decoration: none; font-weight: 700;">
                                        Inaugurate First SKU
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Section --}}
    @if($products->hasPages())
        <div style="margin-top: 32px; display: flex; justify-content: center;">
            {{ $products->appends($filters)->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

<style>
    .agri-filter-label { font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: block; }
    .product-row:hover { background: var(--agri-bg); }
    .pagination { gap: 8px; border: none; }
    .page-link { border-radius: 12px !important; border: 1px solid var(--agri-border) !important; color: var(--agri-text-heading) !important; font-weight: 700 !important; padding: 10px 18px !important; }
    .page-item.active .page-link { background: var(--agri-primary) !important; border-color: var(--agri-primary) !important; color: white !important; }
</style>
@endsection
