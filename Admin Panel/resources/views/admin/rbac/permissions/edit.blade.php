@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.permissions.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Permission Settings</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Permission</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Access Rule</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Define what this action allows in the system.</p>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary);">
                <i class="fas fa-check-circle"></i>
                <span style="font-weight: 700;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-11">
            <form method="POST" action="{{ route('admin.permissions.update', $permission->id) }}" id="permission-edit-form">
                @csrf
                @method('PUT')

                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-primary)40;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Basic Info</h4>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="agri-label">Permission (Human-readable) <span class="text-danger">*</span></label>
                                    <input type="text" name="human_name" id="edit_human_name" class="form-agri" value="{{ old('human_name', $permission->display_name) }}" placeholder="e.g. Can create users" required>
                                    @error('human_name')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="agri-label">Where is this used? <span class="text-danger">*</span></label>
                                    <select name="group" class="form-agri" required>
                                        @foreach($groups as $group)
                                            <option value="{{ $group }}" @selected(old('group', $permission->group) === $group)>{{ $group }}</option>
                                        @endforeach
                                    </select>
                                    @error('group')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="agri-label">Module <span class="text-danger">*</span></label>
                                    <select name="module" id="edit_module" class="form-agri" required>
                                        <option value="">Choose a module</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module }}" @selected(old('module', $permission->module) === $module)>{{ $module }}</option>
                                        @endforeach
                                    </select>
                                    @error('module')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="agri-label">Select what this permission allows <span class="text-danger">*</span></label>
                                    <select name="action" id="edit_action" class="form-agri" required>
                                        <option value="">Choose an option</option>
                                        @foreach($actions as $actionKey => $actionLabel)
                                            <option value="{{ $actionKey }}" @selected(old('action', strtolower((string) $permission->action)) === $actionKey)>{{ $actionLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error('action')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-secondary-light); color: var(--agri-secondary-dark); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-secondary)40;">
                            <i class="fas fa-code"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Technical Details</h4>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                                        <label class="form-check-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted);">
                                            <input type="checkbox" id="edit_advanced_mode" class="form-check-input" style="margin-right: 6px;"> Advanced Mode
                                        </label>
                                    </div>
                                    <div id="edit_advanced_panel" style="display: none; margin-top: 12px; padding: 14px; border: 1px dashed var(--agri-border); border-radius: 12px; background: #f8fafc;">
                                        <label class="agri-label" style="margin-bottom: 8px;">Technical (for developers only): System Key <span class="text-danger">*</span></label>
                                        <input type="text" name="system_key" id="edit_system_key" class="form-agri" value="{{ old('system_key', $permission->name) }}" placeholder="admin.users.create" required readonly>
                                        @error('system_key')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="agri-label">Status</label>
                                    <div style="background: var(--agri-bg); padding: 10px 16px; border-radius: 12px; border: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px; height: 100%;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" {{ old('is_active', $permission->is_active) ? 'checked' : '' }} style="cursor: pointer; width: 44px; height: 22px;">
                                        </div>
                                        <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-agri mb-5" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-primary)40;">
                            <i class="fas fa-align-left"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Description</h4>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="agri-label">What does this permission allow? <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-agri" rows="4" placeholder="Allows admin to create new users from admin panel" required>{{ old('description', $permission->description ?: $permission->display_name) }}</textarea>
                                    @error('description')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-agri" style="padding: 32px; background: white; border-top: 4px solid var(--agri-primary);">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <div style="display: flex; gap: 20px;">
                                <div style="width: 48px; height: 48px; background: var(--agri-secondary-light); border-radius: 14px; color: var(--agri-secondary-dark); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">Internal Mapping</h5>
                                    <p style="color: var(--agri-text-muted); font-size: 13px; margin: 0; line-height: 1.5;">
                                        This permission currently resolves to <strong>{{ $permission->name }}</strong> and affects <strong>{{ $permission->roles_count ?? $permission->roles()->count() }}</strong> role(s).
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 text-end mt-4 mt-lg-0">
                            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                <a href="{{ route('admin.permissions.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 24px; text-decoration: none; font-weight: 700; min-width: 120px; display: flex; align-items: center; justify-content: center;">Back</a>
                                <button type="submit" class="btn-agri btn-agri-primary" style="padding: 12px 32px; font-weight: 700; font-size: 15px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-save"></i> Save Changes
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
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const moduleToKey = function(module, action) {
        if (!module || !action) return '';
        const moduleKey = String(module).toLowerCase().replace(/\s+/g, '.').replace(/_/g, '.').replace(/\.+/g, '.');
        const actionKey = String(action).toLowerCase().replace(/\s+/g, '.').replace(/_/g, '.').replace(/\.+/g, '.');
        return ['admin', moduleKey, actionKey].filter(Boolean).join('.').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
    };

    const moduleToHuman = function(module, action) {
        if (!module || !action) return '';
        const resource = String(module).toLowerCase().replace(/[_.]+/g, ' ').trim();
        const verbMap = { view: 'view', create: 'create', edit: 'edit', delete: 'delete', manage: 'manage' };
        const verb = verbMap[String(action).toLowerCase()] || 'manage';
        return 'Can ' + verb + ' ' + resource;
    };

    const syncEditKey = function() {
        $('#edit_human_name').val(moduleToHuman($('#edit_module').val(), $('#edit_action').val()));
        $('#edit_system_key').val(moduleToKey($('#edit_module').val(), $('#edit_action').val()));
    };

    $('#edit_module, #edit_action').on('change', syncEditKey);
    $('#edit_advanced_mode').on('change', function() {
        $('#edit_advanced_panel').toggle($(this).is(':checked'));
        $('#edit_system_key').prop('readonly', !$(this).is(':checked'));
        syncEditKey();
    });
    syncEditKey();
});
</script>
@endsection
