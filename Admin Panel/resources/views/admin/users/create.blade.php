@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.users') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Governance Accounts</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.create_admin')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Register Governance Personnel</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Instantiate a new administrative identity with defined ecosystem authority.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card-agri" style="padding: 40px; background: white;">
                
                @if (Session::has('message'))
                    <div class="mb-4" style="background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; font-weight: 700; border: 1px solid var(--agri-error)30; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{Session::get('message')}}
                    </div>
                @endif

                <form method="post" action="{{ route('admin.admin.users.store') }}">
                    @csrf

                    {{-- Identity Section --}}
                    <div style="margin-bottom: 48px; border-bottom: 1px solid var(--agri-border); padding-bottom: 40px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">Personnel Identity</h4>
                                <p style="color: var(--agri-text-muted); font-size: 13px; margin: 2px 0 0 0;">Basic identification and communication credentials.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Full Legal Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-agri" placeholder="e.g. Johnathan S. Doe" required>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 8px; font-weight: 600;">
                                    {{ trans("lang.user_name_help") }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Official Communication Link <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-agri" placeholder="governance@agritech.com" required>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 8px; font-weight: 600;">
                                    {{ trans("lang.user_email_help") }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Security Section --}}
                    <div style="margin-bottom: 48px; border-bottom: 1px solid var(--agri-border); padding-bottom: 40px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FFFBEB; color: var(--agri-secondary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">Access & Security</h4>
                                <p style="color: var(--agri-text-muted); font-size: 13px; margin: 2px 0 0 0;">Define the authority level and secure the entry point.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="agri-label">Administrative Role Assignment <span class="text-danger">*</span></label>
                                <select name="role" class="form-agri" required style="height: 52px; font-weight: 600;">
                                    <option value="" disabled selected>Select from Governance Matrix...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected(old('role') == $role->id)>{{$role->role_name}}</option> 
                                    @endforeach  
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Secure Access Key <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-agri" placeholder="Enter high-entropy password" required>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 8px; font-weight: 600;">
                                    {{ trans("lang.user_password_help") }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Key Verification <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-agri" placeholder="Confirm access key" required>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 16px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 56px; font-size: 16px; font-weight: 800; border-radius: 16px;">
                            <i class="fas fa-check-circle me-2"></i> Instantiate Registry Entry
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
