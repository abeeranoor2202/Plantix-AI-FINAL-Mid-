@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.permissions.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Permission Settings</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Create Access Rule</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Create Access Rule</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Define what actions users are allowed to perform</p>
    </div>

    @if($errors->any())
        <div class="card-agri mb-4" style="background: #FEF2F2; border: 1px solid var(--agri-error); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-error);">
                <i class="fas fa-exclamation-circle"></i>
                <span style="font-weight: 700;">Please check the form fields and try again.</span>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-11">
            <form method="POST" action="{{ route('admin.permissions.store') }}" id="permission-create-form">
                @csrf

                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div style="display: flex; gap: 24px; align-items: flex-start;">
                        <div style="width: 60px; height: 60px; border-radius: 16px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid var(--agri-primary)40;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Basic Info</h4>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="agri-label">Permission <span class="text-danger">*</span></label>
                                    <input type="text" name="human_name" id="create_human_name" class="form-agri" value="{{ old('human_name') }}" placeholder="e.g. Can create users" required>
                                    @error('human_name')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="agri-label">Module <span class="text-danger">*</span></label>
                                    <select name="module" id="create_module" class="form-agri" required>
                                        <option value="">Choose module</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module }}" @selected(old('module') === $module)>{{ $module }}</option>
                                        @endforeach
                                    </select>
                                    @error('module')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="agri-label">Action <span class="text-danger">*</span></label>
                                    <select name="action" id="create_action" class="form-agri" required>
                                        <option value="">Choose action</option>
                                        @foreach($actions as $actionKey => $actionLabel)
                                            <option value="{{ $actionKey }}" @selected(old('action') === $actionKey)>{{ $actionLabel }}</option>
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
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">System Config</h4>
                            <label class="form-check-label" style="font-size: 13px; font-weight: 700; color: var(--agri-text-muted); display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <input type="checkbox" id="create_advanced_mode" name="advanced_mode" value="1" class="form-check-input" style="margin: 0;" {{ old('advanced_mode') ? 'checked' : '' }}> Advanced (Technical)
                            </label>

                            <div id="create_advanced_panel" style="display: {{ old('advanced_mode') ? 'block' : 'none' }}; margin-top: 4px; padding: 14px; border: 1px dashed var(--agri-border); border-radius: 12px; background: #f8fafc;">
                                <label class="agri-label" style="margin-bottom: 8px;">System Key <span class="text-danger">*</span></label>
                                <input type="text" name="system_key" id="create_system_key" class="form-agri" value="{{ old('system_key') }}" placeholder="admin.users.create" required readonly>
                                @error('system_key')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-agri mb-4" style="padding: 32px; background: white;">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Where is this used? <span class="text-danger">*</span></label>
                            <select name="group" class="form-agri" required>
                                <option value="">Choose usage area</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}" @selected(old('group') === $group)>{{ $group }}</option>
                                @endforeach
                            </select>
                            @error('group')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="agri-label">Status</label>
                            <div style="background: var(--agri-bg); padding: 10px 16px; border-radius: 12px; border: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px; height: 100%;">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} style="cursor: pointer; width: 44px; height: 22px;">
                                </div>
                                <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Active</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="agri-label">What does this permission allow? <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-agri" rows="4" placeholder="Allows admin to create new users from dashboard" required>{{ old('description') }}</textarea>
                            @error('description')<div style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card-agri" style="padding: 32px; background: white; border-top: 4px solid var(--agri-primary);">
                    <div style="display: flex; gap: 16px; justify-content: flex-end;">
                        <a href="{{ route('admin.permissions.index') }}" class="btn-agri btn-agri-outline" style="padding: 12px 32px; text-decoration: none; font-weight: 700; min-width: 140px; display: flex; align-items: center; justify-content: center;">Back</a>
                        <button type="submit" class="btn-agri btn-agri-primary" style="padding: 12px 40px; font-weight: 700; font-size: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-save"></i> Save Access Rule
                        </button>
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

    const syncGeneratedFields = function() {
        $('#create_human_name').val(moduleToHuman($('#create_module').val(), $('#create_action').val()));
        $('#create_system_key').val(moduleToKey($('#create_module').val(), $('#create_action').val()));
    };

    $('#create_module, #create_action').on('change', syncGeneratedFields);

    $('#create_advanced_mode').on('change', function() {
        const enabled = $(this).is(':checked');
        $('#create_advanced_panel').toggle(enabled);
        $('#create_system_key').prop('readonly', !enabled);
        syncGeneratedFields();
    });

    syncGeneratedFields();
});
</script>
@endsection
