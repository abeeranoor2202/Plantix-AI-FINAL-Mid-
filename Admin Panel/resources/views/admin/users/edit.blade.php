@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.users') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Governance Accounts</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Account Moderation</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Moderate Account: {{ $user->name }}</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update administrative profiles and security permissions for this personnel entry.</p>
            </div>
            <div style="background: var(--agri-primary-light); color: var(--agri-primary); padding: 8px 20px; border-radius: 100px; font-size: 12px; font-weight: 900; letter-spacing: 0.5px; border: 1px solid var(--agri-primary)40;">
                <i class="fas fa-shield-alt me-2"></i> SYSTEM GUARDED
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card-agri" style="padding: 40px; background: white;">
                
                @if (Session::has('message'))
                    <div class="mb-4" style="background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; font-weight: 600; border: 1px solid var(--agri-error)30; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{Session::get('message')}}
                    </div>
                @endif

                <form method="post" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf

                    {{-- Profile Section --}}
                    <div style="margin-bottom: 48px; border-bottom: 1px solid var(--agri-border); padding-bottom: 40px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">Profile Context</h4>
                                <p style="color: var(--agri-text-muted); font-size: 13px; margin: 2px 0 0 0;">Personnel identity and administrative alignment.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.user_name')}} <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ $user->name }}" class="form-agri" placeholder="Full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.user_email')}} <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ $user->email }}" class="form-agri" placeholder="Email address" required>
                            </div>

                            @if($user->id != 1)
                            <div class="col-12">
                                <label class="agri-label">Assigned Capability Group <span class="text-danger">*</span></label>
                                <select name="role" class="form-agri" required style="font-weight: 600; height: 52px;">
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}" @selected($user->role_id == $role->id)>{{$role->role_name}}</option>
                                    @endforeach
                                </select>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 8px; font-weight: 600;">
                                    <i class="fas fa-info-circle me-1"></i> Changing the role will immediately adjust this user's functional boundaries.
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Security Overrides --}}
                    <div style="margin-bottom: 48px; background: var(--agri-bg); border-radius: 20px; padding: 32px; border: 1px dashed var(--agri-border);">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: white; color: var(--agri-secondary); display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid var(--agri-border);">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 17px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">Security Key Management</h4>
                                <p style="color: var(--agri-text-muted); font-size: 12px; margin: 2px 0 0 0;">Modify the administrative access credentials.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="agri-label">Current Key Verification</label>
                                <input type="password" name="old_password" class="form-agri" style="background: white;" placeholder="Required to authorize sensitive modifications">
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">New Secure Access Key</label>
                                <input type="password" name="password" class="form-agri" style="background: white;" placeholder="Minimum 8 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="agri-label">Confirm New Key</label>
                                <input type="password" name="confirm_password" class="form-agri" style="background: white;" placeholder="Matches new access key">
                            </div>
                        </div>
                    </div>

                    {{-- Action Bar --}}
                    <div style="display: flex; gap: 16px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 56px; font-size: 16px; font-weight: 800; border-radius: 16px;">
                            <i class="fas fa-sync-alt me-2"></i> Commit Account Updates
                        </button>
                        <a href="{!! route('admin.users') !!}" class="btn-agri btn-agri-outline" style="flex: 1; height: 56px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; font-weight: 700; border-radius: 16px;">
                            {{ trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
