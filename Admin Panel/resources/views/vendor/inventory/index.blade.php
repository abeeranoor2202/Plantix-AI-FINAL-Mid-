@extends('vendor.layouts.app')
@section('title', 'Inventory Management')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    {{-- Breadcrumbs --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Inventory</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Inventory Management</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Track and update stock levels for your products in real-time.</p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; flex-direction: row; align-items: center; gap: 20px;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Total Products</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--agri-text-heading); line-height: 1;">{{ $summary['total_products'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; flex-direction: row; align-items: center; gap: 20px;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Low Stock</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--agri-text-heading); line-height: 1;">{{ $summary['low_stock'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; flex-direction: row; align-items: center; gap: 20px;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-circle-xmark"></i>
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Out of Stock</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--agri-text-heading); line-height: 1;">{{ $summary['out_of_stock'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Stock List --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Stock List</h4>
            <form method="GET" action="{{ route('vendor.inventory.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group" style="width: 280px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search product..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <select name="stock_status" class="form-agri" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    <option value="in_stock" @selected(request('stock_status')==='in_stock')>In Stock</option>
                    <option value="low"      @selected(request('stock_status')==='low')>Low Stock</option>
                    <option value="out"      @selected(request('stock_status')==='out')>Out of Stock</option>
                </select>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 20px;">Filter</button>
                @if(request()->hasAny(['search', 'stock_status']))
                    <a href="{{ route('vendor.inventory.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                        <th class="text-center" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Current Qty</th>
                        <th class="text-center" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reserved</th>
                        <th class="text-center" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Low Threshold</th>
                        <th class="text-center" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Update Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('vendor.products.edit', $stock->product_id) }}" class="text-decoration-none d-block" style="font-weight: 700; color: var(--agri-text-heading); margin-bottom: 2px;">
                                    {{ Str::limit($stock->product->name ?? 'Unknown Product', 45) }}
                                </a>
                                <div style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600;">
                                    <i class="fas fa-barcode me-1"></i> SKU: {{ $stock->sku ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div style="font-size: 18px; font-weight: 800; color: {{ $stock->quantity <= 0 ? '#dc2626' : ($stock->isLow() ? '#d97706' : '#059669') }};">
                                    {{ $stock->quantity }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 13px; padding: 6px 12px; border-radius: 8px;">{{ (int) $stock->reserved_quantity }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 13px; padding: 6px 12px; border-radius: 8px;">{{ $stock->low_stock_threshold ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($stock->quantity <= 0)
                                    <span class="badge rounded-pill" style="background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; padding: 6px 12px; font-weight: 700; font-size: 11px; text-transform: uppercase;">Out of Stock</span>
                                @elseif($stock->isLow())
                                    <span class="badge rounded-pill" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; padding: 6px 12px; font-weight: 700; font-size: 11px; text-transform: uppercase;">Low Stock</span>
                                @else
                                    <span class="badge rounded-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; padding: 6px 12px; font-weight: 700; font-size: 11px; text-transform: uppercase;">In Stock</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <form method="POST" action="{{ route('vendor.inventory.update', $stock->product_id) }}" class="d-flex align-items-center gap-1">
                                        @csrf
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <div style="display: flex; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; position: relative; padding-top: 14px;">
                                                <div style="position: absolute; top: 2px; left: 0; right: 40px; display: flex; justify-content: space-around; pointer-events: none;">
                                                    <span style="font-size: 8px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Qty</span>
                                                    <span style="font-size: 8px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Min</span>
                                                </div>
                                                <input type="number" name="quantity" value="{{ $stock->quantity }}"
                                                       class="form-control text-center" style="width: 70px; border: none; background: transparent; font-size: 14px; font-weight: 700; padding: 4px 5px;" min="0" title="Current Quantity">
                                                <div style="width: 1px; background: #e2e8f0;"></div>
                                                <input type="number" name="low_stock_threshold" value="{{ $stock->low_stock_threshold }}"
                                                       class="form-control text-center" style="width: 60px; border: none; background: transparent; font-size: 14px; font-weight: 700; padding: 4px 5px;" min="0" title="Low Stock Threshold">
                                                <button type="submit" class="btn btn-primary" style="border: none; border-radius: 0; padding: 5px 14px; background: var(--agri-primary); display: flex; align-items: center; justify-content: center;" title="Save Changes">
                                                    <i class="fas fa-check" style="font-size: 12px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @if($stock->quantity == 0 && $stock->reserved_quantity == 0)
                                        <form method="POST" action="{{ route('vendor.inventory.destroy', $stock->product_id) }}" onsubmit="return confirm('Delete this stock record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Delete Empty Record">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div style="color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">No inventory records found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($stocks->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $stocks->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Recent Movements --}}
    <div class="card-agri mt-5" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Recent Stock Movements</h4>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Time</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Type</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Qty</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? [] as $movement)
                        <tr>
                            <td class="px-4 py-3" style="font-size: 13px; color: var(--agri-text-muted); font-weight: 600;">
                                {{ $movement->created_at?->format('d M, Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">
                                {{ $movement->product->name ?? 'Deleted Product' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill" style="background: {{ $movement->type == 'in' ? '#ecfdf5' : '#fef2f2' }}; color: {{ $movement->type == 'in' ? '#059669' : '#dc2626' }}; padding: 4px 10px; font-weight: 700; font-size: 10px; text-transform: uppercase;">
                                    {{ $movement->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3" style="font-weight: 800; font-size: 15px; color: var(--agri-text-heading);">
                                {{ $movement->type == 'in' ? '+' : '-' }}{{ (int) abs($movement->quantity) }}
                            </td>
                            <td class="px-4 py-3" style="font-size: 13px; color: var(--agri-text-muted);">
                                {{ $movement->reference ?? 'Manual Update' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--agri-text-muted); font-weight: 600;">No movement records available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
