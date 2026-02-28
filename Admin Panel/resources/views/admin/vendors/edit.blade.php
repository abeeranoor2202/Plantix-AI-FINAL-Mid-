@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.vendors') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Vendors</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit {{ $vendor->title }}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                Edit Vendor
            </h1>
        </div>
    </div>

    {{-- Edit Form Card --}}
    <div class="card-agri" style="padding: 32px; margin-bottom: 24px;">
        <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->id) }}">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">
                        Basic Information
                    </h3>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Vendor Name
                        </label>
                        <input type="text" class="form-agri" value="{{ $vendor->title }}" disabled style="background-color: #f5f5f5;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Owner Email
                        </label>
                        <input type="email" class="form-agri" value="{{ $vendor->author->email }}" disabled style="background-color: #f5f5f5;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Category
                        </label>
                        <input type="text" class="form-agri" value="{{ $vendor->category?->name ?? 'N/A' }}" disabled style="background-color: #f5f5f5;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Phone
                        </label>
                        <input type="tel" class="form-agri" value="{{ $vendor->phone }}" disabled style="background-color: #f5f5f5;">
                    </div>
                </div>

                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">
                        Status & Settings
                    </h3>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Approval Status
                        </label>
                        <div style="padding: 12px; border-radius: 8px; background: {{ $vendor->is_approved ? '#d4edda' : '#fff3cd' }}; color: {{ $vendor->is_approved ? '#155724' : '#856404' }}; font-weight: 600;">
                            {{ $vendor->is_approved ? '✓ Approved' : '⚠ Pending Approval' }}
                        </div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Active Status
                        </label>
                        <div style="padding: 12px; border-radius: 8px; background: {{ $vendor->is_active ? '#d4edda' : '#f8d7da' }}; color: {{ $vendor->is_active ? '#155724' : '#721c24' }}; font-weight: 600;">
                            {{ $vendor->is_active ? '✓ Active' : '✗ Inactive' }}
                        </div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Commission Rate (%)
                        </label>
                        <input type="number" step="0.01" class="form-agri" value="{{ $vendor->commission_rate ?? 0 }}" disabled style="background-color: #f5f5f5;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Rating
                        </label>
                        <input type="text" class="form-agri" value="⭐ {{ $vendor->rating ?? 0 }} / 5.0" disabled style="background-color: #f5f5f5;">
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="border-top: 1px solid var(--agri-border); padding-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('admin.vendors') }}" class="btn btn-secondary">
                    Cancel
                </a>
                @if(!$vendor->is_approved)
                    <button type="submit" name="action" value="approve" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Vendor
                    </button>
                @endif
                <button type="submit" name="action" value="toggle_active" class="btn btn-{{ $vendor->is_active ? 'danger' : 'success' }}">
                    <i class="fas fa-{{ $vendor->is_active ? 'lock' : 'unlock' }}"></i>
                    {{ $vendor->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Vendor Statistics --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->products->count() }}</div>
            <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 8px;">Products Listed</div>
        </div>
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->orders->count() }}</div>
            <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 8px;">Total Orders</div>
        </div>
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->coupons->count() }}</div>
            <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 8px;">Active Coupons</div>
        </div>
        <div class="card-agri" style="padding: 24px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--agri-primary);">{{ $vendor->review_count ?? 0 }}</div>
            <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 8px;">Customer Reviews</div>
        </div>
    </div>

</div>
@endsection
