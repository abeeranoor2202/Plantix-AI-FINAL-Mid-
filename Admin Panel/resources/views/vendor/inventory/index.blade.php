@extends('vendor.layouts.app')
@section('title', 'Inventory')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Inventory Management</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Track and update stock levels for your products.</p>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4 hover-card h-100" style="border-radius:16px;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-boxes fs-3"></i>
                </div>
                <div class="display-5 fw-bold text-dark mb-1">{{ $summary['total_products'] }}</div>
                <div class="text-muted small text-uppercase fw-bold mt-1">Total Products</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4 hover-card h-100" style="border-radius:16px;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                </div>
                <div class="display-5 fw-bold text-dark mb-1">{{ $summary['low_stock'] }}</div>
                <div class="text-muted small text-uppercase fw-bold mt-1">Low Stock</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4 hover-card h-100" style="border-radius:16px;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-x-octagon-fill fs-3"></i>
                </div>
                <div class="display-5 fw-bold text-dark mb-1">{{ $summary['out_of_stock'] }}</div>
                <div class="text-muted small text-uppercase fw-bold mt-1">Out of Stock</div>
            </div>
        </div>
    </div>
</div>

<x-card style="padding: 0; overflow: hidden;">
    <x-slot name="header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-columns-reverse me-2 text-primary fs-5"></i>Stock List</h6>
        
        {{-- Filters --}}
        <form method="GET" class="d-flex flex-wrap align-items-center gap-2 m-0">
            <div class="input-group input-group-sm rounded-pill shadow-sm" style="width: 200px;">
                <span class="input-group-text bg-light border-0 rounded-start-pill text-muted px-3"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-0 bg-light rounded-end-pill py-2" placeholder="Search product..." value="{{ request('search') }}">
            </div>
            
            <select name="stock_status" class="form-select form-select-sm border-0 bg-light rounded-pill px-3 py-2 fw-medium shadow-sm w-auto">
                <option value="">All Statuses</option>
                <option value="in_stock" @selected(request('stock_status')==='in_stock')>In Stock</option>
                <option value="low"      @selected(request('stock_status')==='low')>Low Stock</option>
                <option value="out"      @selected(request('stock_status')==='out')>Out of Stock</option>
            </select>
            
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center">
                <i class="bi bi-funnel-fill d-sm-none"></i><span class="d-none d-sm-inline">Filter</span>
            </button>
            
            @if(request()->hasAny(['search','stock_status']))
                <a href="{{ route('vendor.inventory.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm" title="Clear Filters">
                    <i class="bi bi-x-circle d-sm-none"></i><span class="d-none d-sm-inline">Clear</span>
                </a>
            @endif
        </form>
    </div>
    </x-slot>
        @if($stocks->isEmpty())
            <div class="text-center text-muted py-5 my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                    <i class="bi bi-inboxes fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark">No inventory records found</h6>
                <p class="small mb-0">Try adjusting your filters or search term.</p>
            </div>
        @else
            <x-table>
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 fw-semibold text-muted text-uppercase small">Product</th>
                            <th class="text-center fw-semibold text-muted text-uppercase small" style="width: 120px;">Current Qty</th>
                            <th class="text-center fw-semibold text-muted text-uppercase small" style="width: 120px;">Reserved <i class="bi bi-question-circle text-muted" title="Units allocated to pending or processing orders"></i></th>
                            <th class="text-center fw-semibold text-muted text-uppercase small" style="width: 140px;">Low Threshold</th>
                            <th class="text-center fw-semibold text-muted text-uppercase small">Status</th>
                            <th class="text-center pe-4 fw-semibold text-muted text-uppercase small" style="width: 250px;">Update Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('vendor.products.edit', $stock->product_id) }}" class="text-decoration-none fw-bold text-dark small mb-1 d-block hover-text-primary">
                                    {{ Str::limit($stock->product->name ?? 'Unknown Product', 40) }}
                                </a>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded"><i class="bi bi-upc-scan me-1"></i>SKU: {{ $stock->sku ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fs-5 fw-bold {{ $stock->quantity <= 0 ? 'text-danger' : ($stock->isLow() ? 'text-warning' : 'text-success') }}">
                                    {{ $stock->quantity }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light border text-dark fs-6">{{ (int) $stock->reserved_quantity }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light border text-dark fs-6">{{ $stock->low_stock_threshold ?? '—' }}</span>
                            </td>
                            <td class="text-center">
                                @if($stock->quantity <= 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-x-octagon-fill me-1"></i>Out of Stock</span>
                                @elseif($stock->isLow())
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i>Low Stock</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>In Stock</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <form method="POST" action="{{ route('vendor.inventory.update', $stock->product_id) }}" class="d-flex align-items-center justify-content-center gap-2">
                                    @csrf
                                    <div class="input-group input-group-sm shadow-sm" style="width: 180px;">
                                        <input type="number" name="quantity" value="{{ $stock->quantity }}"
                                               class="form-control text-center border-secondary border-opacity-25" min="0" title="Quantity" placeholder="Qty">
                                        <input type="number" name="low_stock_threshold" value="{{ $stock->low_stock_threshold }}"
                                               class="form-control text-center border-secondary border-opacity-25" min="0" title="Low Threshold" placeholder="Thr.">
                                        <button type="submit" class="btn btn-success" title="Save Stock">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('vendor.inventory.destroy', $stock->product_id) }}" class="mt-2"
                                      onsubmit="return confirm('Delete this stock record? Quantity and reserved quantity must both be zero.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Delete empty stock record">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </x-table>
        @endif
    @if($stocks->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
            {{ $stocks->links() }}
        </div>
    @endif
</x-card>

<x-card class="mt-4" style="padding: 0; overflow: hidden;">
    <x-slot name="header">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary fs-5"></i>Recent Stock Movements</h6>
    </x-slot>
        <x-table>
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 fw-semibold text-muted text-uppercase small">Time</th>
                        <th class="fw-semibold text-muted text-uppercase small">Product</th>
                        <th class="fw-semibold text-muted text-uppercase small">Type</th>
                        <th class="fw-semibold text-muted text-uppercase small">Qty</th>
                        <th class="pe-4 fw-semibold text-muted text-uppercase small">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? [] as $movement)
                        <tr>
                            <td class="ps-4 small text-muted">{{ $movement->created_at?->format('d M, Y H:i') ?? '—' }}</td>
                            <td class="small fw-medium text-dark">{{ $movement->product->name ?? 'Deleted Product' }}</td>
                            <td class="small text-uppercase">{{ $movement->type }}</td>
                            <td class="small fw-bold text-dark">{{ (int) $movement->quantity }}</td>
                            <td class="pe-4 small text-muted">{{ $movement->reference ?? 'manual' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No movement records available.</td></tr>
                    @endforelse
                </tbody>
        </x-table>
</x-card>
</div>
@endsection
