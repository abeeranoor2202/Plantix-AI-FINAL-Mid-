@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.vendors') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Vendors</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Add Vendor</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Add Vendor</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card-agri" style="padding: 40px;">

                @if($errors->any())
                    <div class="error_top" style="background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
                        Please correct the highlighted fields and submit again.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.vendors.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user-circle"></i> User Details
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Owner Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-agri @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                                @error('name')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Store Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" class="form-agri @error('store_name') is-invalid @enderror" value="{{ old('store_name') }}" placeholder="e.g. FarmTech Solutions" required>
                                @error('store_name')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-agri @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="owner@store.com" required>
                                @error('email')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-agri @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="03XXXXXXXXX" required>
                                @error('phone')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-agri @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                @error('password')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-agri" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-map-marker-alt"></i> Customer Address
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="agri-label">Address Line 1</label>
                                <input type="text" name="address_line1" class="form-agri" value="{{ old('address_line1') }}" placeholder="House / Street / Area">
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">Address Line 2</label>
                                <input type="text" name="address_line2" class="form-agri" value="{{ old('address_line2') }}" placeholder="Apartment / Suite">
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">City</label>
                                <input type="text" name="city" class="form-agri" value="{{ old('city') }}" placeholder="City">
                            </div>

                            <div class="col-md-4">
                                <label class="agri-label">State / Province</label>
                                <input type="text" name="state" class="form-agri" value="{{ old('state') }}" placeholder="State or province">
                            </div>

                            <div class="col-md-2">
                                <label class="agri-label">ZIP / Postal Code</label>
                                <input type="text" name="zip" class="form-agri" value="{{ old('zip') }}" placeholder="ZIP">
                            </div>

                            <div class="col-md-2">
                                <label class="agri-label">Country</label>
                                <input type="text" name="country" class="form-agri" value="{{ old('country') }}" placeholder="Country">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Image</h4>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div class="vendor_image" style="width: 80px; height: 80px; border-radius: 12px; background: white; border: 2px dashed var(--agri-border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <i class="fas fa-image" style="color: var(--agri-border); font-size: 24px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <input type="file" name="image" onChange="handleFileSelect(event)" class="form-control" style="font-size: 13px;" accept="image/*">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 8px;">JPG, PNG, WEBP up to 2MB.</div>
                                @error('image')<div style="font-size: 11px; color: var(--agri-error); margin-top: 4px;">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 4px;">Account Activation</h5>
                            <p style="font-size: 13px; color: #b45309; margin: 0;">Enable this vendor account immediately.</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" name="is_active" value="1" class="user_active" id="user_active" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);" @checked(old('is_active', true))>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save
                        </button>
                        <a href="{{ route('admin.vendors') }}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                            Back
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
    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        if (!f) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            $('.vendor_image').html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + e.target.result + '" alt="image">');
        };
        reader.readAsDataURL(f);
    }
</script>
@endsection