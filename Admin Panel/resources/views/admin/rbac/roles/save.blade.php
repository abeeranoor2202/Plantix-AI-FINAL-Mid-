@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Create Access Unit</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ trans('lang.create_role') }}</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create a role and choose what this admin can do.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-11">
            <form method="POST" action="{{ route('admin.role.store') }}">
                @csrf

                {{-- Primary Identity Card --}}
                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-bg); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-border);">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Identity & Initial State</h4>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="agri-label">Role Designation <span class="text-danger">*</span></label>
                                    <input type="text" name="role_name" class="form-agri" value="{{ old('role_name') }}" placeholder="e.g. Inventory Auditor" required>
                                    @error('role_name')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="agri-label">Status on Creation</label>
                                    <div style="background: var(--agri-bg); padding: 10px 16px; border-radius: 12px; border: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} style="cursor: pointer; width: 44px; height: 22px;">
                                        </div>
                                        <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Initialize as Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Capability Matrix --}}
                <div class="mb-5">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
                        <div>
                            <h3 style="font-size: 20px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Permission Checklist</h3>
                            <p style="color: var(--agri-text-muted); font-size: 13px; margin: 4px 0 0 0;">Select each permission this role should have.</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="select-all-perms" class="btn-agri" style="padding: 8px 16px; font-size: 12px; background: white; color: var(--agri-primary); border: 1px solid var(--agri-primary); border-radius: 10px; font-weight: 700;">Grant All Access</button>
                            <button type="button" id="deselect-all-perms" class="btn-agri" style="padding: 8px 16px; font-size: 12px; background: white; color: var(--agri-text-muted); border: 1px solid var(--agri-border); border-radius: 10px; font-weight: 700;">Revoke All Access</button>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach($permissions as $group => $groupPerms)
                        <div class="col-lg-6 col-xl-4">
                            <div class="card-agri" style="padding: 0; background: white; height: 100%; border-radius: 16px; border: 1px solid var(--agri-border);">
                                <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); background: var(--agri-bg); display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; border: 1px solid var(--agri-border);">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <span style="font-weight: 800; color: var(--agri-text-heading); text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">{{ str_replace('-', ' ', $group) }}</span>
                                    </div>
                                    <a href="#" class="select-all-group" data-group="{{ $group }}" style="text-decoration: none; font-size: 10px; font-weight: 800; color: var(--agri-primary); text-transform: uppercase;">Toggle All</a>
                                </div>
                                <div style="padding: 20px;">
                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                        @foreach($groupPerms as $perm)
                                        <label class="d-flex align-items-center justify-content-between p-2 rounded-3 hover-bg" style="cursor: pointer; margin: 0; transition: 0.2s;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <input type="checkbox" class="form-check-input perm-{{ $group }}" 
                                                       name="permissions[]" value="{{ $perm['id'] }}"
                                                       {{ in_array($perm['id'], old('permissions', [])) ? 'checked' : '' }}
                                                       style="width: 18px; height: 18px; margin: 0;">
                                                <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-main);">{{ \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower((string) ($perm['display_name'] ?? '')), 'can ') ? $perm['display_name'] : ('Can ' . \Illuminate\Support\Str::lower((string) ($perm['display_name'] ?? str_replace('.', ' ', ($perm['name'] ?? 'manage access'))))) }}</span>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="card-agri" style="padding: 32px; background: white; border-top: 4px solid var(--agri-primary);">
                    <div style="display: flex; gap: 16px; justify-content: flex-end;">
                        <a href="{{ route('admin.role.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 32px; text-decoration: none; font-weight: 700; min-width: 140px; display: flex; align-items: center; justify-content: center;">{{ trans('lang.cancel')}}</a>
                        <button type="submit" class="btn-agri btn-agri-primary" style="padding: 12px 40px; font-weight: 700; font-size: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle"></i> Complete Role Creation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .hover-bg:hover { background: var(--agri-bg); }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.select-all-group', function(e) {
        e.preventDefault();
        const group = $(this).data('group');
        const boxes = $('.perm-' + group);
        const allChecked = boxes.filter(':checked').length === boxes.length;
        boxes.prop('checked', !allChecked);
    });

    $('#select-all-perms').on('click', function(e) { e.preventDefault(); $('.form-check-input').prop('checked', true); });
    $('#deselect-all-perms').on('click', function(e) { e.preventDefault(); $('.form-check-input').prop('checked', false); });
});
</script>
@endsection
