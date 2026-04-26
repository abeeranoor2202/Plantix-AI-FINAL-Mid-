@extends('layouts.dashboard')

@section('title', 'My Profile | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
document.getElementById('customerPhotoInput')?.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const url = URL.createObjectURL(this.files[0]);
        const preview = document.getElementById('customerPhotoPreview');
        const placeholder = document.getElementById('customerPhotoPlaceholder');
        if (preview) { preview.src = url; preview.classList.remove('d-none'); }
        if (placeholder) placeholder.classList.add('d-none');
    }
});
</script>
@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Main Content -->
            <div class="col-12">
                <div class="card-agri p-4 mb-4" style="border: none;">
                    <h3 class="fw-bold mb-4 text-dark" style="font-size: 20px;">Profile Details</h3>

                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="row g-4 align-items-start">
                            {{-- Profile Photo --}}
                            <div class="col-12 col-md-auto text-center">
                                <div class="position-relative d-inline-block mb-2">
                                    @if($user->profile_photo)
                                        <img src="{{ Storage::url($user->profile_photo) }}" id="customerPhotoPreview"
                                             class="rounded-circle border shadow-sm" style="width:100px;height:100px;object-fit:cover;" alt="Profile Photo">
                                    @else
                                        <div id="customerPhotoPlaceholder" class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm border" style="width:100px;height:100px;font-size:2.5rem;font-weight:700;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <img id="customerPhotoPreview" class="rounded-circle border shadow-sm d-none" style="width:100px;height:100px;object-fit:cover;" alt="Profile Photo" src="">
                                    @endif
                                    <label for="customerPhotoInput" class="position-absolute bottom-0 end-0 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow border" style="width:30px;height:30px;cursor:pointer;" title="Change photo">
                                        <i class="fas fa-camera" style="font-size:12px;"></i>
                                    </label>
                                </div>
                                <input type="file" id="customerPhotoInput" name="profile_photo" accept="image/*" class="d-none">
                                @error('profile_photo')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                <div class="text-muted small">Max 1MB</div>
                            </div>

                            {{-- Fields --}}
                            <div class="col">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark" style="font-size: 14px;">Full Name <span class="text-danger">*</span></label>
                                        <input name="name" type="text" class="form-agri" value="{{ old('name', $user->name) }}" required>
                                        @error('name')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark" style="font-size: 14px;">Email Address</label>
                                        <input type="email" class="form-agri" value="{{ $user->email }}" disabled title="Email cannot be changed" style="background: var(--agri-bg); cursor: not-allowed;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark" style="font-size: 14px;">Phone Number</label>
                                        <input name="phone" type="tel" class="form-agri" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark" style="font-size: 14px;">Account Role</label>
                                        <input type="text" class="form-agri" value="Customer" disabled style="background: var(--agri-bg); cursor: not-allowed;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $notificationPreferences = $user->notification_preferences ?? [];
                        @endphp
                        <div class="mt-4 pt-4 border-top">
                            <h4 class="fw-bold mb-3 text-dark" style="font-size: 18px;">Notification Preferences</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border h-100" style="border-color: var(--agri-border) !important;">
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">Appointment emails</div>
                                            <div class="text-muted small">Booking and reminder messages</div>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="notification_preferences[appointment_emails]" value="0">
                                            <input class="form-check-input" type="checkbox" name="notification_preferences[appointment_emails]" value="1" {{ data_get($notificationPreferences, 'appointment_emails', true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border h-100" style="border-color: var(--agri-border) !important;">
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">Forum notifications</div>
                                            <div class="text-muted small">Replies, answers, and locks</div>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="notification_preferences[forum_notifications]" value="0">
                                            <input class="form-check-input" type="checkbox" name="notification_preferences[forum_notifications]" value="1" {{ data_get($notificationPreferences, 'forum_notifications', true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border h-100" style="border-color: var(--agri-border) !important;">
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">System alerts</div>
                                            <div class="text-muted small">Orders, payments, and security</div>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="notification_preferences[system_alerts]" value="0">
                                            <input class="form-check-input" type="checkbox" name="notification_preferences[system_alerts]" value="1" {{ data_get($notificationPreferences, 'system_alerts', true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn-agri btn-agri-primary" style="padding: 10px 24px; font-size: 15px;">Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Password Settings -->
                <div class="card-agri p-4" style="border: none;">
                    <h3 class="fw-bold mb-4 text-dark" style="font-size: 20px;">Change Password</h3>
                    <form method="POST" action="{{ route('account.password') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark" style="font-size: 14px;">Current Password <span class="text-danger">*</span></label>
                                <input name="current_password" type="password" class="form-agri" required>
                                @error('current_password')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 14px;">New Password <span class="text-danger">*</span></label>
                                <input name="password" type="password" class="form-agri" required>
                                @error('password')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 14px;">Confirm New Password <span class="text-danger">*</span></label>
                                <input name="password_confirmation" type="password" class="form-agri" required>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn-agri btn-agri-outline" style="padding: 10px 24px; font-size: 15px;">Update Password</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
