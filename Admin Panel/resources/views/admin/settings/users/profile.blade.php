@extends('layouts.app')

@section('content')

<div class="row page-titles mb-4">
    <div class="col-md-5 align-self-center">
        <h3 class="text-themecolor">{{trans('lang.user_profile')}}</h3>
    </div>
    <div class="col-md-7 align-self-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
            <li class="breadcrumb-item active">{{trans('lang.user_profile')}}</li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-agri border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="agri-icon-box bg-primary-light">
                            <i class="fa-solid fa-user-gear text-primary" style="font-size: 20px;"></i>
                        </div>
                        <h4 class="m-0 fw-bold">{{trans('lang.user_profile')}}</h4>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if (Session::has('message'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> {{Session::get('message')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{Session::get('success')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <form method="post" action="{{ route('admin.users.profile.update', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row gx-5">
                            <!-- Left Column: Basic Info & Profile Photo -->
                            <div class="col-lg-4 text-center border-end">
                                <div class="profile-photo-wrapper mb-4">
                                    <div class="position-relative d-inline-block">
                                        @php
                                            $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/user.png');
                                        @endphp
                                        <img src="{{ $photo }}" id="profile-preview" class="rounded-circle shadow-sm border" style="width: 120px; height: 120px; object-fit: cover; background: #fdfdfd;">
                                        <label for="profile_photo" class="btn btn-sm btn-agri-primary position-absolute rounded-circle" style="bottom: 0px; right: 0px; width: 32px; height: 32px; padding: 0;">
                                            <i class="fa-solid fa-camera" style="font-size: 12px;"></i>
                                        </label>
                                        <input type="file" id="profile_photo" name="profile_photo" class="d-none" onchange="previewImage(this)">
                                    </div>
                                    <h5 class="mt-3 mb-1 fw-bold">{{ $user->name }}</h5>
                                    <p class="text-muted small">{{ $user->email }}</p>
                                </div>
                                
                                <div class="agri-info-card bg-light p-3 rounded-md text-start small">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fa-solid fa-shield-halved text-success me-2"></i>
                                        <span><strong>Account:</strong> Super Admin</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-clock text-info me-2"></i>
                                        <span><strong>Last Login:</strong> {{ now()->format('d M, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Form Fields -->
                            <div class="col-lg-8">
                                <h5 class="fw-bold mb-4"><i class="fa-solid fa-id-card me-2 text-primary"></i>Personal Details</h5>
                                
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="fw-semibold small mb-1">{{trans('lang.user_name')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0" name="name" value="{{ $user->name }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="fw-semibold small mb-1">{{trans('lang.user_email')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control border-start-0" name="email" value="{{ $user->email }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12 form-group pt-2">
                                        <hr class="my-4">
                                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-lock me-2 text-warning"></i>Security Settings</h5>
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <label class="fw-semibold small mb-1">{{trans('lang.old_password')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                            <input type="password" class="form-control border-start-0" name="old_password" placeholder="Enter current password to make changes">
                                        </div>
                                        <small class="text-muted">{{ trans("lang.old_password_help") }}</small>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="fw-semibold small mb-1">{{trans('lang.new_password')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-lock-open text-muted"></i></span>
                                            <input type="password" class="form-control border-start-0" name="password" placeholder="Leave blank to keep current">
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="fw-semibold small mb-1">{{trans('lang.confirm_password')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                                            <input type="password" class="form-control border-start-0" name="confirm_password" placeholder="Re-enter new password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top-0 pt-4 px-0 pb-0">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{!! route('admin.dashboard') !!}" class="btn-agri btn-agri-outline px-4 rounded-md" style="text-decoration: none; display: inline-flex; align-items: center;">
                                    <i class="fa-solid fa-xmark me-2"></i>{{ trans('lang.cancel')}}
                                </a>
                                <button type="submit" class="btn-agri btn-agri-primary px-5 rounded-md shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> {{ trans('lang.save')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection

