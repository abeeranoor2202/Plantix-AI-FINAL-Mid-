@extends('layouts.frontend')

@section('title', 'My Profile | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Sidebar Menu -->
            <div class="col-lg-3 mb-4">
                <div class="card-agri p-0 overflow-hidden" style="border: none;">
                    <div class="bg-white p-4 text-center border-bottom">
                        <div style="width: 80px; height: 80px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 32px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ auth('web')->user()->name ?? 'Customer' }}</h5>
                        <p class="text-muted small mb-0">{{ auth('web')->user()->email ?? '' }}</p>
                    </div>
                    <div class="list-group border-0" style="border-radius: 0;">
                        <a class="list-group-item border-0 py-3 px-4 d-flex align-items-center gap-3 active" href="{{ route('account.profile') }}" style="background: var(--agri-primary-light); color: var(--agri-primary); border-left: 4px solid var(--agri-primary) !important;">
                            <i class="fas fa-user-circle fs-5"></i> Profile Settings
                        </a>
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('orders') }}">
                            <i class="fas fa-shopping-bag fs-5"></i> My Orders
                        </a>
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('appointments') }}">
                            <i class="fas fa-calendar-check fs-5"></i> Appointments
                        </a>
                        <a class="list-group-item border-0 text-danger py-3 px-4 d-flex align-items-center gap-3 mt-3 border-top" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fs-5"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Main Content -->
            <div class="col-lg-9">
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
                                <label class="form-label fw-bold text-dark" style="font-size: 14px;">Profile Photo</label>
                                <input name="profile_photo" type="file" class="form-control" style="border-radius: var(--agri-radius-sm); padding: 0.5rem 1rem;" accept="image/*">
                                @error('profile_photo')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
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
