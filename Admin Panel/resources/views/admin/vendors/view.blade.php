@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.vendors') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Vendors</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $vendor->title }}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                Vendor Details
            </h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Vendor
            </a>
        </div>
    </div>

    {{-- Vendor Card --}}
    <div class="card-agri" style="padding: 32px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">
                    Basic Information
                </h3>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Vendor Name</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">{{ $vendor->title }}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Owner</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">
                        {{ $vendor->author->name }} ({{ $vendor->author->email }})
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Category</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">
                        {{ $vendor->category?->name ?? 'N/A' }}
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Phone</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">{{ $vendor->phone }}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Address</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">{{ $vendor->address }}</p>
                </div>
            </div>
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">
                    Status & Metrics
                </h3>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Approval Status</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px;">
                        <span style="display: inline-block; padding: 6px 12px; border-radius: 8px; background: {{ $vendor->is_approved ? '#d4edda' : '#fff3cd' }}; color: {{ $vendor->is_approved ? '#155724' : '#856404' }}; font-weight: 600;">
                            {{ $vendor->is_approved ? 'Approved' : 'Pending Approval' }}
                        </span>
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Active Status</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px;">
                        <span style="display: inline-block; padding: 6px 12px; border-radius: 8px; background: {{ $vendor->is_active ? '#d4edda' : '#f8d7da' }}; color: {{ $vendor->is_active ? '#155724' : '#721c24' }}; font-weight: 600;">
                            {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Rating</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">
                        ⭐ {{ $vendor->rating ?? 0 }} / 5.0 ({{ $vendor->review_count ?? 0 }} reviews)
                    </p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Commission Rate</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">{{ $vendor->commission_rate ?? 0 }}%</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Delivery Fee</label>
                    <p style="margin: 4px 0 0 0; font-size: 16px; color: var(--agri-primary-dark);">{{ $vendor->delivery_fee ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card-agri" style="padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px;">
            Quick Actions
        </h3>
        <div style="display: flex; gap: 12px;">
            <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->id) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="action" value="toggle_active">
                <button type="submit" class="btn btn-{{ $vendor->is_active ? 'danger' : 'success' }}">
                    <i class="fas fa-{{ $vendor->is_active ? 'lock' : 'unlock' }}"></i>
                    {{ $vendor->is_active ? 'Deactivate' : 'Activate' }} Vendor
                </button>
            </form>
            @if(!$vendor->is_approved)
                <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->id) }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Vendor
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Statistics --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 24px;">
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 32px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->products->count() }}</div>
            <div style="font-size: 14px; color: var(--agri-text-muted); margin-top: 8px;">Total Products</div>
        </div>
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 32px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->orders->count() }}</div>
            <div style="font-size: 14px; color: var(--agri-text-muted); margin-top: 8px;">Total Orders</div>
        </div>
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 32px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->coupons->count() }}</div>
            <div style="font-size: 14px; color: var(--agri-text-muted); margin-top: 8px;">Active Coupons</div>
        </div>
    </div>

    {{-- Back Link --}}
    <div>
        <a href="{{ route('admin.vendors') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Vendors
        </a>
    </div>

</div>
@endsection
