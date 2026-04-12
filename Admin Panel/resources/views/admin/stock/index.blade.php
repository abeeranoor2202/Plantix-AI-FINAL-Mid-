@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Stock</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Stock Tracking</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Monitor inventory levels with consistent actions and status indicators.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary);">
                <i class="fas fa-check-circle"></i>
                <span style="font-weight: 700;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: #b91c1c;">
                <i class="fas fa-exclamation-circle"></i>
                <span style="font-weight: 700;">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Inventory List</h4>
            <form method="GET" action="{{ route('admin.stock.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" placeholder="Search product or SKU..." value="{{ request('search') }}" style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <select name="vendor_id" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->name ?? $v->title ?? $v->owner_name ?? ('Vendor #' . $v->id) }}</option>
                    @endforeach
                </select>
                <select name="stock_status" class="form-agri" style="height: 42px; min-width: 170px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
                    <option value="low" @selected(request('stock_status') === 'low')>Low Stock</option>
                    <option value="out" @selected(request('stock_status') === 'out')>Out of Stock</option>
                </select>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Vendor</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Quantity</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reserved</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Threshold</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $stock->product->name ?? 'Deleted Product' }}</div>
                                <small class="text-muted">SKU: {{ $stock->sku ?? 'N/A' }}</small>
                            </td>
                            <td class="px-4 py-3">{{ $stock->vendor->title ?? 'Platform Default' }}</td>
                            <td class="px-4 py-3"><strong>{{ (int) $stock->quantity }}</strong></td>
                            <td class="px-4 py-3">{{ (int) $stock->reserved_quantity }}</td>
                            <td class="px-4 py-3">{{ $stock->low_stock_threshold ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: nowrap;">
                                    @if(! $stock->is_available)
                                        <span class="badge rounded-pill bg-secondary">UNAVAILABLE</span>
                                    @elseif($stock->quantity <= 0)
                                        <span class="badge rounded-pill bg-danger">OUT OF STOCK</span>
                                    @elseif($stock->isLow())
                                        <span class="badge rounded-pill bg-warning text-dark">LOW STOCK</span>
                                    @else
                                        <span class="badge rounded-pill bg-success">IN STOCK</span>
                                    @endif

                                    <form method="POST" action="{{ route('admin.stock.toggle', $stock->id) }}" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <label class="switch" title="{{ $stock->is_available ? 'Mark unavailable' : 'Mark in stock' }}">
                                            <input type="checkbox" onchange="this.form.submit()" {{ $stock->is_available ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.stock.show', $stock->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.stock.edit', $stock->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.stock.destroy', $stock->id) }}" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this stock?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No stock records found.</td></tr>
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

    <div class="card-agri" style="margin-top: 24px; padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Recent Stock Movements</h4>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Time</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Type</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Quantity</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? [] as $movement)
                        <tr>
                            <td class="px-4 py-3">{{ $movement->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">{{ $movement->product->name ?? 'Deleted Product' }}</td>
                            <td class="px-4 py-3 text-uppercase">{{ $movement->type }}</td>
                            <td class="px-4 py-3">{{ (int) $movement->quantity }}</td>
                            <td class="px-4 py-3">{{ $movement->reference ?? 'manual' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4" style="color: var(--agri-text-muted);">No stock movement logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }
    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
</style>
@endsection
