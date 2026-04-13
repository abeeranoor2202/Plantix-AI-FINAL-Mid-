@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.appointments.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Appointments</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Appointment Details</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Appointment #{{ $appointment->id }}</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Read-only overview for online and offline consultation details.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn-agri btn-agri-primary" style="text-decoration: none;">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i>Edit
                </a>
                <a href="{{ route('admin.appointments.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card-agri" style="text-align: center; padding: 40px 24px;">
                @php
                    $displayStatus = match($appointment->status) {
                        'confirmed', 'accepted' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled', 'rejected' => 'Cancelled',
                        default => 'Pending',
                    };
                @endphp
                <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 6px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-calendar-check"></i>
                    {{ $appointment->type === 'physical' ? 'Offline' : 'Online' }} Appointment
                </div>
                <h3 style="font-size: 22px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 8px;">{{ $displayStatus }}</h3>
                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border);">
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-user" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $appointment->user->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-user-tie" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ optional($appointment->expert)->user->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                        <i class="fas fa-money-bill-wave" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($appointment->fee ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; margin-bottom: 20px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Appointment Details</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="agri-label">Date & Time</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('M d, Y h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Topic</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->topic ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Payment Status</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ ucfirst($appointment->payment_status ?? 'unpaid') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="agri-label">Notifications</label>
                        <div class="address-card" style="margin-bottom: 0;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ ($appointment->notifications_enabled ?? true) ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="agri-label">Admin Notes</label>
                        <div class="address-card" style="margin-bottom: 0; min-height: 72px;">
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->admin_notes ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-agri" style="padding: 32px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Mode Specific Information</h4>
                <div class="row g-4">
                    @if($appointment->type === 'online')
                        <div class="col-md-6">
                            <label class="agri-label">Platform</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->platform ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Meeting Link</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main); word-break: break-all;">{{ $appointment->meeting_link ?: 'N/A' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="agri-label">Venue Name</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->venue_name ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">City / Location</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->city ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Address Line 1</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->address_line1 ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Address Line 2</label>
                            <div class="address-card" style="margin-bottom: 0;">
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-main);">{{ $appointment->address_line2 ?: 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
    .address-card {
        background: var(--agri-bg);
        border: 1px solid var(--agri-border);
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }
    .address-card:hover {
        border-color: var(--agri-primary);
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>
@endsection
