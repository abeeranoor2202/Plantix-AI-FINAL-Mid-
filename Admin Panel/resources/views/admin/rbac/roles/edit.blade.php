@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Authority Configuration</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Configure Role: {{ $role->role_name }}</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Fine-tune access boundaries and functional capabilities for this organizational unit.</p>
            </div>
            <div style="background: {{ $role->is_active ? 'var(--agri-primary-light)' : '#FEF2F2' }}; color: {{ $role->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}; padding: 8px 16px; border-radius: 12px; font-size: 13px; font-weight: 800; border: 1px solid {{ $role->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}40;">
                <i class="fas {{ $role->is_active ? 'fa-shield-check' : 'fa-shield-alt' }}" style="margin-right: 6px;"></i>
                {{ $role->is_active ? 'ACTIVE AUTHORITY' : 'DEACTIVATED' }}
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-11">
            <form method="POST" action="{{ route('admin.role.update', $role->id) }}">
                @csrf
                @method('PUT')

                {{-- Primary Identity Card --}}
                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-primary)40;">
                            <i class="fas fa-id-card-alt"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Identity & Status</h4>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="agri-label">Organizational Label</label>
                                    <input type="text" name="role_name" class="form-agri" value="{{ old('role_name', $role->role_name) }}" placeholder="e.g. Regional Supervisor" required>
                                    @error('role_name')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="agri-label">Operational State</label>
                                    <div style="background: var(--agri-bg); padding: 10px 16px; border-radius: 12px; border: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }} style="cursor: pointer; width: 44px; height: 22px;">
                                        </div>
                                        <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Role is Operational</span>
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
                            <h3 style="font-size: 20px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Capability Matrix</h3>
                            <p style="color: var(--agri-text-muted); font-size: 13px; margin: 4px 0 0 0;">Assign functional nodes to define the exact scope of this role's authority.</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="select-all-perms" class="btn-agri" style="padding: 8px 16px; font-size: 12px; background: white; color: var(--agri-primary); border: 1px solid var(--agri-primary); border-radius: 10px; font-weight: 700;">Grant All Access</button>
                            <button type="button" id="deselect-all-perms" class="btn-agri" style="padding: 8px 16px; font-size: 12px; background: white; color: var(--agri-text-muted); border: 1px solid var(--agri-border); border-radius: 10px; font-weight: 700;">Revoke All Access</button>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach($permissions as $group => $groupPerms)
                        <div class="col-lg-6 col-xl-4">
                            <div class="card-agri" style="padding: 0; background: white; height: 100%; border-radius: 16px; transition: 0.3s; border: 1px solid var(--agri-border);">
                                <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); background: var(--agri-bg); display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; border: 1px solid var(--agri-border);">
                                            <i class="fas {{ $loop->index % 2 == 0 ? 'fa-folder' : 'fa-cogs' }}"></i>
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
                                                <input type="checkbox" class="form-check-input perm-checkbox perm-{{ $group }}" 
                                                       name="permissions[]" value="{{ $perm['id'] }}"
                                                       {{ in_array($perm['id'], old('permissions', $assignedIds)) ? 'checked' : '' }}
                                                       style="width: 18px; height: 18px; margin: 0;">
                                                <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-main);">{{ $perm['display_name'] }}</span>
                                            </div>
                                            @if(str_contains(strtolower($perm['display_name']), 'delete') || str_contains(strtolower($perm['display_name']), 'remove'))
                                                <i class="fas fa-exclamation-triangle" style="font-size: 10px; color: var(--agri-error); opacity: 0.5;" title="Destructive Action"></i>
                                            @endif
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Impact Assessment and Actions --}}
                <div class="card-agri" style="padding: 32px; background: white; border-top: 4px solid var(--agri-primary);">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <div style="display: flex; gap: 20px;">
                                <div style="width: 48px; height: 48px; background: var(--agri-secondary-light); border-radius: 14px; color: var(--agri-secondary-dark); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div>
                                    <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">Impact Assessment</h5>
                                    <p style="color: var(--agri-text-muted); font-size: 13px; margin: 0; line-height: 1.5;">
                                        This role is active. Modifying these capabilities will immediately affect <strong>{{ $usersCount ?? 0 }}</strong> administrative user(s) currently assigned to this unit.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 text-end mt-4 mt-lg-0">
                            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                <a href="{{ route('admin.role.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 24px; text-decoration: none; font-weight: 700; min-width: 120px; display: flex; align-items: center; justify-content: center;">Cancel</a>
                                <button type="submit" class="btn-agri btn-agri-primary" style="padding: 12px 32px; font-weight: 700; font-size: 15px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-sync-alt"></i> Commit Authority Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .hover-bg:hover { background: var(--agri-bg); }
    .perm-checkbox:checked + span { color: var(--agri-primary) !important; font-weight: 700 !important; }
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
        $(this).text(allChecked ? 'Select All' : 'Deselect All');
    });

    $('#select-all-perms').on('click', function(e) {
        e.preventDefault();
        $('.perm-checkbox').prop('checked', true);
    });

    $('#deselect-all-perms').on('click', function(e) {
        e.preventDefault();
        $('.perm-checkbox').prop('checked', false);
    });
});
</script>
@endsection
