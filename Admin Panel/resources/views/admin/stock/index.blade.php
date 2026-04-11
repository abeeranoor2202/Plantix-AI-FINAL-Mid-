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
                        <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->name }}</option>
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
                            <td class="px-4 py-3">{{ $stock->low_stock_threshold ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($stock->quantity <= 0)
                                    <span class="badge rounded-pill bg-danger">OUT OF STOCK</span>
                                @elseif($stock->isLow())
                                    <span class="badge rounded-pill bg-warning text-dark">LOW STOCK</span>
                                @else
                                    <span class="badge rounded-pill bg-success">IN STOCK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.stock.edit', $stock->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 999px; border: none;" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $stock->id }}" title="Adjust">
                                        <i class="fas fa-sliders-h"></i>
                                    </button>
                                </div>

                                <div class="modal fade" id="adjustModal{{ $stock->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 18px 40px rgba(0,0,0,0.12);">
                                            <div class="modal-header" style="border: none; padding: 20px 20px 0;">
                                                <h5 class="modal-title" style="font-weight: 700; color: var(--agri-text-heading); font-size: 16px;">Adjust Stock</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.stock.adjust', $stock->id) }}">
                                                @csrf
                                                <div class="modal-body" style="padding: 20px;">
                                                    <div class="mb-3">
                                                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">Quantity Delta</label>
                                                        <input type="number" name="adjustment" class="form-agri" required placeholder="Use negative to reduce">
                                                    </div>
                                                    <div>
                                                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">Note</label>
                                                        <input type="text" name="note" class="form-agri" placeholder="Adjustment note (optional)">
                                                    </div>
                                                </div>
                                                <div class="modal-footer" style="border: none; padding: 0 20px 20px; display: flex; gap: 10px;">
                                                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; height: 40px;">Cancel</button>
                                                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 40px;">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">No stock records found.</td></tr>
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
</div>
@endsection
