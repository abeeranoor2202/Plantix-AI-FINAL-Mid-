@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.vendors') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Vendors</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Vendor</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Vendor: {{ $vendor->title }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update the store, owner details, and vendor lifecycle status.</p>
        </div>
        <a href="{{ route('admin.vendors') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700; padding: 12px 24px; height: 44px;">
            <i class="fas fa-arrow-left"></i> Back to Vendors
        </a>
    </div>

    @if(session('success'))
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Vendor Details</h4>
            <span class="badge rounded-pill {{ $vendor->is_approved ? ($vendor->is_active ? 'bg-success' : 'bg-warning text-dark') : 'bg-secondary' }}">
                {{ $vendor->is_approved ? ($vendor->is_active ? 'Approved' : 'Suspended') : 'Pending' }}
            </span>
        </div>

        <div style="padding: 24px;">
            <form method="POST" action="{{ route('admin.vendors.update', $vendor->id) }}">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Store Name</label>
                        <input type="text" name="title" class="form-agri" value="{{ old('title', $vendor->title) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Owner Name</label>
                        <input type="text" name="owner_name" class="form-agri" value="{{ old('owner_name', $vendor->author?->name) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Owner Email</label>
                        <input type="email" name="owner_email" class="form-agri" value="{{ old('owner_email', $vendor->author?->email) }}" required>
                    </div>
                    <div class="col-lg-6">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Owner Phone</label>
                        <input type="text" name="owner_phone" class="form-agri" value="{{ old('owner_phone', $vendor->author?->phone) }}">
                    </div>
                    <div class="col-12">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Description</label>
                        <textarea name="description" class="form-agri" rows="3">{{ old('description', $vendor->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Address</label>
                        <input type="text" name="address" class="form-agri" value="{{ old('address', $vendor->address) }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Phone</label>
                        <input type="text" name="phone" class="form-agri" value="{{ old('phone', $vendor->phone) }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Commission Rate</label>
                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-agri" value="{{ old('commission_rate', $vendor->commission_rate) }}">
                    </div>
                    <div class="col-lg-4">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Delivery Fee</label>
                        <input type="number" step="0.01" min="0" name="delivery_fee" class="form-agri" value="{{ old('delivery_fee', $vendor->delivery_fee) }}">
                    </div>
                    <div class="col-lg-3">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Open Time</label>
                        <input type="time" name="open_time" class="form-agri" value="{{ old('open_time', $vendor->open_time) }}">
                    </div>
                    <div class="col-lg-3">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Close Time</label>
                        <input type="time" name="close_time" class="form-agri" value="{{ old('close_time', $vendor->close_time) }}">
                    </div>
                    <div class="col-lg-6">
                        @php
                            $currentStatus = old('status', $vendor->is_approved ? ($vendor->is_active ? 'approved' : 'suspended') : 'pending');
                        @endphp
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
                        <select name="status" class="form-agri" required>
                            <option value="pending" @selected($currentStatus === 'pending')>Pending</option>
                            <option value="approved" @selected($currentStatus === 'approved')>Approved</option>
                            <option value="suspended" @selected($currentStatus === 'suspended')>Suspended</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--agri-border); gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('admin.vendors') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; height: 44px; display: inline-flex; align-items: center;">Back</a>
                    <button type="submit" class="btn-agri btn-agri-primary" style="height: 44px; border: none; font-weight: 700;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri mb-4" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Vendor Status</h4>
                </div>
                <div style="padding: 24px;">
                    <div style="background: var(--agri-bg); border-radius: 16px; padding: 18px; margin-bottom: 18px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">Current State</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading);">
                            {{ $vendor->is_approved ? ($vendor->is_active ? 'Approved' : 'Suspended') : 'Pending' }}
                        </div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Select a new status from the main form to update the vendor lifecycle.</div>
                    </div>

                    <div style="background: var(--agri-bg); border-radius: 16px; padding: 18px; margin-bottom: 18px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">Owner</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $vendor->author?->name ?? 'N/A' }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted);">{{ $vendor->author?->email ?? 'No email' }}</div>
                    </div>

                    <div style="background: #fef2f2; border-radius: 16px; padding: 18px;">
                        <div style="font-size: 12px; font-weight: 700; color: #b91c1c; text-transform: uppercase; margin-bottom: 8px;">Danger Zone</div>
                        <p style="margin: 0 0 16px 0; color: #7f1d1d; font-size: 13px; line-height: 1.6;">Deleting a vendor will remove the store record unless it has existing orders, in which case it will be archived.</p>
                        <form method="POST" action="{{ route('admin.vendors.delete', $vendor->id) }}" onsubmit="return confirm('Delete this vendor? If the vendor has orders, it will be archived instead.');">
                            @csrf
                            <button type="submit" class="btn-agri" style="background: #fee2e2; color: #dc2626; border: none; height: 44px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; width: 100%; justify-content: center;">
                                <i class="fas fa-trash"></i> Delete Vendor
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
