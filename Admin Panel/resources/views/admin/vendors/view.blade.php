@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">
    @php
        $statusVariant = $vendor->is_approved ? ($vendor->is_active ? 'success' : 'warning') : 'secondary';
        $statusText = $vendor->is_approved ? ($vendor->is_active ? 'Approved' : 'Suspended') : 'Pending Approval';
        $placeholderImage = asset('images/placeholder.png');
        $profileImage = $vendor->image ? asset('storage/' . $vendor->image) : $placeholderImage;
    @endphp

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.vendors') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Vendors</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Vendor Details</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Vendor Profile</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Comprehensive overview of account activities and details.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-edit"></i> Edit Vendor
                </a>
                <a href="{{ route('admin.vendors') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <span style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary);">Basic</span>
        <a href="{{ route('admin.orders.index') }}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600;">Orders</a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card-agri" style="text-align: center; padding: 40px 24px; margin-bottom: 24px;">
                <div style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.1); margin: 0 auto 24px; overflow: hidden; background: var(--agri-bg); display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $profileImage }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null;this.src='{{ $placeholderImage }}';" alt="Vendor Image">
                </div>

                <h3 style="font-size: 22px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 8px;">{{ $vendor->title }}</h3>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-store"></i> Verified Vendor
                </div>

                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border);">
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-user" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $vendor->author?->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-envelope" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $vendor->author?->email ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                        <i class="fas fa-phone-alt" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $vendor->phone ?: 'Not mentioned' }}</span>
                    </div>
                </div>
            </div>

            <div class="card-agri" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px;">Quick Actions</h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="toggle_active">
                        <button type="submit" class="btn-agri {{ $vendor->is_active ? 'btn-agri-danger' : 'btn-agri-success' }}" style="width: 100%; justify-content: center;">
                            <i class="fas fa-{{ $vendor->is_active ? 'lock' : 'unlock' }}"></i>
                            {{ $vendor->is_active ? 'Deactivate' : 'Activate' }} Vendor
                        </button>
                    </form>

                    @if(!$vendor->is_approved)
                        <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->id) }}">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; justify-content: center;">
                                <i class="fas fa-check"></i> Approve Vendor
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; margin-bottom: 24px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Address</h4>
                @if($vendor->address)
                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 16px; padding: 20px;">
                        <h6 style="font-weight:700; color:var(--agri-text-heading); margin-bottom:4px; line-height:1.4;">{{ $vendor->address }}</h6>
                        <p style="font-size:13px; color:var(--agri-text-muted); margin:0;">Vendor business address</p>
                    </div>
                @else
                    <div style="background:var(--agri-bg); padding:40px; border-radius:16px; text-align:center; border: 1px dashed var(--agri-border); color:var(--agri-text-muted);">
                        <i class="fas fa-map-marker-alt" style="font-size:32px; margin-bottom:12px; opacity:0.3;"></i>
                        <p style="margin:0; font-weight:600;">No shipping address found</p>
                    </div>
                @endif
            </div>

            <div class="row g-3" style="margin-bottom: 24px;">
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $vendor->products->count() }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Total Products</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $vendor->orders->count() }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $vendor->coupons->count() }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Active Coupons</div>
                    </div>
                </div>
            </div>

            <div class="card-agri" style="padding: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px;">Vendor Status</h4>
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <x-badge :variant="$statusVariant"><i class="fas fa-shield-alt"></i> {{ $statusText }}</x-badge>
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-star" style="color:#D97706;"></i> {{ number_format((float)($vendor->rating ?? 0), 1) }} / 5 ({{ (int)($vendor->review_count ?? 0) }} reviews)
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-percent"></i> Commission {{ number_format((float)($vendor->commission_rate ?? 0), 2) }}%
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-truck"></i> Delivery Fee {{ number_format((float)($vendor->delivery_fee ?? 0), 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
