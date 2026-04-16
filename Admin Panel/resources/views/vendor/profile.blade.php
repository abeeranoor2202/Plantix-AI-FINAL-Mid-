@extends('vendor.layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="row page-titles mb-4">
    <div class="col-md-5 align-self-center">
        <h3 class="text-themecolor">Profile Settings</h3>
    </div>
    <div class="col-md-7 align-self-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Profile Settings</li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-10">
            <div class="card card-agri border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="m-0 fw-bold"><i class="fa-solid fa-user-gear me-2 text-primary"></i>Personal Details</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-3 text-center">
                                @if ($user->profile_photo)
                                    <img src="{{ Storage::url($user->profile_photo) }}" class="rounded-circle shadow-sm border mb-3" width="120" height="120" style="object-fit: cover;" alt="Profile Photo">
                                @else
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center shadow-sm border mb-3" style="width:120px;height:120px;font-size:3rem;font-weight:700;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <input type="file" name="profile_photo" accept="image/*" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold small mb-1">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold small mb-1">Email Address</label>
                                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold small mb-1">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold small mb-1">Account Role</label>
                                        <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn-agri btn-agri-primary px-5"><i class="fa-solid fa-floppy-disk me-2"></i>Update Personal Details</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($vendor)
                <div class="card card-agri border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="m-0 fw-bold"><i class="fa-solid fa-store me-2 text-success"></i>Store Information</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('vendor.profile.store.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $vendor->title) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Store Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="fw-semibold small mb-1">Store Description</label>
                                    <textarea name="description" rows="4" class="form-control">{{ old('description', $vendor->description) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="fw-semibold small mb-1">Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Opening Time</label>
                                    <input type="time" name="open_time" class="form-control" value="{{ old('open_time', $vendor->open_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Closing Time</label>
                                    <input type="time" name="close_time" class="form-control" value="{{ old('close_time', $vendor->close_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Delivery Fee</label>
                                    <input type="number" step="0.01" name="delivery_fee" class="form-control" value="{{ old('delivery_fee', $vendor->delivery_fee) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-semibold small mb-1">Minimum Order Amount</label>
                                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $vendor->min_order_amount) }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn-agri btn-agri-primary px-5"><i class="fa-solid fa-floppy-disk me-2"></i>Update Store Setup</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card card-agri border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="m-0 fw-bold"><i class="fa-solid fa-lock me-2 text-danger"></i>Password Settings</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('vendor.profile.password') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-semibold small mb-1">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-semibold small mb-1">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-semibold small mb-1">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-danger rounded-pill px-5"><i class="fa-solid fa-shield-halved me-2"></i>Apply New Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
