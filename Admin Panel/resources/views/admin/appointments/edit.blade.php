@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.appointments.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Appointments</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Appointment</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Appointment #{{ $appointment->id }}</h1>
    </div>

    @if($errors->any())
        <div class="error_top" style="display: block; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card-agri" style="padding: 40px;">
                <form method="POST" action="{{ route('admin.appointments.update', $appointment->id) }}">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-calendar-alt"></i> Appointment Details
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Assigned Expert <span class="text-danger">*</span></label>
                                <select class="form-agri" name="expert_id" required>
                                    <option value="">Select expert</option>
                                    @foreach($experts as $expert)
                                        <option value="{{ $expert->id }}" @selected(old('expert_id', $appointment->expert_id) == $expert->id)>{{ $expert->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Appointment Type <span class="text-danger">*</span></label>
                                <select class="form-agri" id="type" name="type" required>
                                    <option value="online" @selected(old('type', $appointment->type === 'physical' ? 'offline' : 'online') === 'online')>Online</option>
                                    <option value="offline" @selected(old('type', $appointment->type === 'physical' ? 'offline' : 'online') === 'offline')>Offline</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-agri" name="scheduled_at" value="{{ old('scheduled_at', $appointment->scheduled_at ? $appointment->scheduled_at->format('Y-m-d\\TH:i') : '') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Consultation Fee</label>
                                <input type="number" step="0.01" min="0" class="form-agri" name="fee" value="{{ old('fee', $appointment->fee) }}" placeholder="0.00">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Topic</label>
                                <input type="text" class="form-agri" name="topic" value="{{ old('topic', $appointment->topic) }}" placeholder="e.g. Disease Consultation">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Platform</label>
                                <select class="form-agri online-only" name="platform" id="platform">
                                    <option value="">Select platform</option>
                                    <option value="Google Meet" @selected(old('platform', $appointment->platform) === 'Google Meet')>Google Meet</option>
                                    <option value="Zoom" @selected(old('platform', $appointment->platform) === 'Zoom')>Zoom</option>
                                    <option value="Microsoft Teams" @selected(old('platform', $appointment->platform) === 'Microsoft Teams')>Microsoft Teams</option>
                                    <option value="WhatsApp" @selected(old('platform', $appointment->platform) === 'WhatsApp')>WhatsApp</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="agri-label">Admin Notes</label>
                                <textarea class="form-agri" rows="3" name="admin_notes" placeholder="Internal notes for this appointment...">{{ old('admin_notes', $appointment->admin_notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-exchange-alt"></i> Mode Specific Details
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6 online-only">
                                <label class="agri-label">Meeting Link</label>
                                <input type="url" id="meeting_link" class="form-agri" name="meeting_link" value="{{ old('meeting_link', $appointment->meeting_link) }}" placeholder="https://...">
                            </div>

                            <div class="col-md-6 offline-only">
                                <label class="agri-label">Venue Name</label>
                                <input type="text" id="venue_name" class="form-agri" name="venue_name" value="{{ old('venue_name', $appointment->venue_name) }}" placeholder="e.g. Plantix Office Lahore">
                            </div>

                            <div class="col-md-6 offline-only">
                                <label class="agri-label">Address Line 1</label>
                                <input type="text" id="address_line1" class="form-agri" name="address_line1" value="{{ old('address_line1', $appointment->address_line1) }}" placeholder="House / Street / Area">
                            </div>

                            <div class="col-md-6 offline-only">
                                <label class="agri-label">Address Line 2</label>
                                <input type="text" class="form-agri" name="address_line2" value="{{ old('address_line2', $appointment->address_line2) }}" placeholder="Apartment, floor, suite (optional)">
                            </div>

                            <div class="col-md-6 offline-only">
                                <label class="agri-label">City / Location</label>
                                <input type="text" id="city" class="form-agri" name="city" value="{{ old('city', $appointment->city) }}" placeholder="City">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user"></i> Customer
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Customer Name</label>
                                <input type="text" class="form-agri" value="{{ $appointment->user->name ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">Customer Email</label>
                                <input type="text" class="form-agri" value="{{ $appointment->user->email ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a;">
                        <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 16px;">Status & Settings</h5>
                        @php
                            $currentUiStatus = match($appointment->status) {
                                'confirmed', 'accepted' => 'confirmed',
                                'completed' => 'completed',
                                'cancelled', 'rejected' => 'cancelled',
                                default => 'pending',
                            };
                        @endphp
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="agri-label">Status</label>
                                <select class="form-agri" name="status">
                                    <option value="pending" @selected(old('status', $currentUiStatus) === 'pending')>Pending</option>
                                    <option value="confirmed" @selected(old('status', $currentUiStatus) === 'confirmed')>Confirmed</option>
                                    <option value="completed" @selected(old('status', $currentUiStatus) === 'completed')>Completed</option>
                                    <option value="cancelled" @selected(old('status', $currentUiStatus) === 'cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="agri-label">Payment Status</label>
                                <select class="form-agri" name="payment_status">
                                    <option value="unpaid" @selected(old('payment_status', $appointment->payment_status) === 'unpaid')>Unpaid</option>
                                    <option value="paid" @selected(old('payment_status', $appointment->payment_status) === 'paid')>Paid</option>
                                    <option value="refunded" @selected(old('payment_status', $appointment->payment_status) === 'refunded')>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-4" style="display: flex; align-items: end;">
                                <div class="form-check form-switch" style="padding: 0; margin: 0; display: flex; align-items: center; gap: 12px;">
                                    <input type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);" @checked(old('notifications_enabled', $appointment->notifications_enabled ?? true))>
                                    <label for="notifications_enabled" style="font-size: 13px; font-weight: 700; color: #92400e;">Notifications Enabled</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                            Cancel
                        </a>
                    </div>
                </form>
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
</style>
@endsection

@section('scripts')
<script>
    function applyModeVisibility() {
        const type = document.getElementById('type').value;
        const online = document.querySelectorAll('.online-only');
        const offline = document.querySelectorAll('.offline-only');

        online.forEach(function (el) {
            el.style.display = (type === 'online') ? '' : 'none';
        });
        offline.forEach(function (el) {
            el.style.display = (type === 'offline') ? '' : 'none';
        });

        const meetingLink = document.getElementById('meeting_link');
        const venueName = document.getElementById('venue_name');
        const addressLine1 = document.getElementById('address_line1');
        const city = document.getElementById('city');

        if (meetingLink) meetingLink.required = type === 'online';
        if (venueName) venueName.required = type === 'offline';
        if (addressLine1) addressLine1.required = type === 'offline';
        if (city) city.required = type === 'offline';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('type').addEventListener('change', applyModeVisibility);
        applyModeVisibility();
    });
</script>
@endsection
