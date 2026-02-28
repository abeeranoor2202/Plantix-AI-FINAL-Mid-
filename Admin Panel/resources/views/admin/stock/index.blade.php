@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Inventory Management</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Stock Tracking</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Monitor and adjust real-time stock levels across your marketplace.</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background: var(--agri-success-light); color: var(--agri-success); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="background: none; border: none; color: var(--agri-success); cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--agri-text-heading); line-height: 1;">{{ $summary['total_products'] }}</div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px;">Tracked Products</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: #d97706; line-height: 1;">{{ $summary['low_stock'] }}</div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px;">Low Stock Alerts</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-agri" style="padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #FEF2F2; color: var(--agri-error); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--agri-error); line-height: 1;">{{ $summary['out_of_stock'] }}</div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px;">Out of Stock</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Controls --}}
    <div class="card-agri" style="padding: 24px; margin-bottom: 24px; background: white;">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px;"></i>
                    <input type="text" name="search" class="form-agri" placeholder="Search by product name or SKU..." value="{{ request('search') }}" style="padding-left: 40px; height: 44px;">
                </div>
            </div>
            <div class="col-md-3">
                <select name="vendor_id" class="form-agri" style="height: 44px;">
                    <option value="">All Partners/Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-agri" style="height: 44px;">
                    <option value="">Availability Status</option>
                    <option value="in_stock" @selected(request('stock_status')==='in_stock')>In Stock</option>
                    <option value="low"      @selected(request('stock_status')==='low')>Low Stock</option>
                    <option value="out"      @selected(request('stock_status')==='out')>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 44px;">Apply</button>
                @if(request()->hasAny(['search','vendor_id','stock_status']))
                    <a href="{{ route('admin.stock.index') }}" class="btn-agri btn-agri-outline" style="padding: 10px; height: 44px; display: flex; align-items: center; text-decoration: none;">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Inventory Ledger</h4>
            <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); background: var(--agri-bg); padding: 4px 12px; border-radius: 100px;">{{ $stocks->total() }} Records Found</span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Product Details</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Provider</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Current Qty</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Min. Threshold</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                @if($stock->product && $stock->product->photo)
                                    <img src="{{ $stock->product->photo }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid var(--agri-border);">
                                @else
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--agri-bg); display: flex; align-items: center; justify-content: center; color: var(--agri-border); border: 1px solid var(--agri-border);">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $stock->product->name ?? 'Deleted Product' }}</div>
                                    <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">SKU: {{ $stock->sku ?? 'N/A' }} • {{ $stock->product->category->title ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-main);">{{ $stock->vendor->title ?? 'Platform Default' }}</div>
                        </td>
                        <td style="padding: 16px 24px;" class="text-center">
                            @php
                                $color = 'var(--agri-success)';
                                if($stock->quantity <= 0) $color = 'var(--agri-error)';
                                elseif($stock->isLow()) $color = '#d97706';
                            @endphp
                            <div style="font-size: 16px; font-weight: 800; color: {{ $color }};">{{ $stock->quantity }}</div>
                        </td>
                        <td style="padding: 16px 24px;" class="text-center">
                            <div style="font-size: 13px; font-weight: 700; color: var(--agri-text-muted); background: var(--agri-bg); padding: 2px 10px; border-radius: 6px; display: inline-block;">
                                {{ $stock->low_stock_threshold ?? '—' }}
                            </div>
                        </td>
                        <td style="padding: 16px 24px;" class="text-center">
                            @if($stock->quantity <= 0)
                                <span class="badge-agri badge-agri-error">Out of Stock</span>
                            @elseif($stock->isLow())
                                <span class="badge-agri" style="background:#fffbeb; color:#d97706;">Low Stock</span>
                            @else
                                <span class="badge-agri badge-agri-success">Stable</span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px;" class="text-end">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('admin.stock.edit', $stock->id) }}" class="btn-agri" style="padding: 8px; color: var(--agri-text-muted); background: var(--agri-bg); border-radius: 10px; text-decoration: none;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn-agri" style="padding: 8px; color: var(--agri-primary); background: var(--agri-primary-light); border-radius: 10px; border: none;" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $stock->id }}">
                                    <i class="fas fa-plus-minus"></i> Adjust
                                </button>
                            </div>

                            {{-- Adjust Modal Redesign --}}
                            <div class="modal fade" id="adjustModal{{ $stock->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15);">
                                        <div class="modal-header" style="border: none; padding: 24px 24px 0;">
                                            <h5 class="modal-title" style="font-weight: 800; color: var(--agri-text-heading); font-size: 16px;">Quick Adjustment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 12px;"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.stock.adjust', $stock->id) }}">
                                            @csrf
                                            <div class="modal-body" style="padding: 24px;">
                                                <div style="background: var(--agri-bg); padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center;">
                                                    <div style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Current Level</div>
                                                    <div style="font-size: 20px; font-weight: 800; color: var(--agri-text-heading);">{{ $stock->quantity }} Units</div>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">ADJUSTMENT QUANTITY</label>
                                                    <input type="number" name="adjustment" class="form-agri" placeholder="+/- Qty" required style="text-align: center; font-size: 18px; font-weight: 800; height: 50px;">
                                                    <p style="font-size: 10px; color: var(--agri-primary); font-weight: 700; margin-top: 8px; text-align: center;"><i class="fas fa-info-circle"></i> Use negative numbers to decrease</p>
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">ADJUSTMENT NOTE</label>
                                                    <input type="text" name="note" class="form-agri" placeholder="Reason (Optional)" style="font-size: 13px;">
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border: none; padding: 0 24px 24px;">
                                                <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; height: 44px;">Cancel</button>
                                                <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 44px; font-weight: 700;">Update Stock</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div style="color: var(--agri-border); font-size: 48px; margin-bottom: 20px;"><i class="fas fa-box-open"></i></div>
                            <div style="font-weight: 700; color: var(--agri-text-muted);">No stock records match your criteria.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($stocks->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border);">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .badge-agri { padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .badge-agri-success { background: #DCFCE7; color: #166534; }
    .badge-agri-error { background: #fee2e2; color: #991b1b; }
</style>
@endsection
