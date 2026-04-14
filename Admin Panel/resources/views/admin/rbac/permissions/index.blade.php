@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Permission Settings</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Permission Settings</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and manage access rules across your admin system.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn-agri btn-agri-primary" style="height: 44px; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none;">
            <i class="fas fa-plus"></i> Create Access Rule
        </a>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary);">
                <i class="fas fa-check-circle"></i>
                <span style="font-weight: 700;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #FEF2F2; border: 1px solid var(--agri-error); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-error);">
                <i class="fas fa-exclamation-circle"></i>
                <span style="font-weight: 700;">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white;">
            <div style="display: grid; grid-template-columns: 1.3fr 1fr 1fr 1fr; gap: 12px;">
                <input type="text" id="search-permission" class="form-agri" placeholder="Search permission..." style="margin-bottom: 0; height: 40px; font-size: 13px;">

                <select id="filter-module" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>

                <select id="filter-status" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>

                <select id="filter-usage" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Usage Areas</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="permissionsTable" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Permission</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Module</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Usage</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $perm)
                        @php
                            $moduleLabel = $perm->module ?: 'General';
                            $groupLabel = $perm->group ?: 'General';
                            $permissionLabel = $perm->display_name ?: ('Can ' . strtolower(($perm->action ?: 'manage')) . ' ' . strtolower($moduleLabel));
                            $status = $perm->is_active ? 'active' : 'disabled';
                        @endphp
                        <tr
                            data-module="{{ $moduleLabel }}"
                            data-status="{{ $status }}"
                            data-usage="{{ $groupLabel }}"
                            data-search="{{ strtolower($permissionLabel . ' ' . $moduleLabel . ' ' . $groupLabel) }}"
                            style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;"
                        >
                            <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $permissionLabel }}</td>
                            <td style="padding: 16px 24px; color: var(--agri-text-main); font-size: 13px; line-height: 1.5;">{{ $moduleLabel }}</td>
                            <td style="padding: 16px 24px;">
                                <span style="background: #EEF2FF; color: #4338CA; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid #C7D2FE;">{{ $groupLabel }}</span>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="background: {{ $perm->is_active ? 'var(--agri-primary-light)' : '#FEF2F2' }}; color: {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}40; min-width: 84px; text-align: center;">
                                        {{ $perm->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                    <form method="POST" action="{{ route('admin.permissions.toggle-status', $perm->id) }}" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <label class="switch" title="Toggle permission status">
                                            <input type="checkbox" {{ $perm->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                </div>
                            </td>
                            <td style="padding: 16px 24px;" class="text-end">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('admin.permissions.edit', $perm->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px; text-decoration: none;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $perm->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #FEF2F2; color: var(--agri-error); border: none; border-radius: 999px;"
                                                onclick="return confirm('Delete permission {{ $perm->display_name ?: $perm->name }}?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div style="color: var(--agri-border); font-size: 40px; margin-bottom: 12px;"><i class="fas fa-shield-slash"></i></div>
                                <p style="color: var(--agri-text-muted); font-weight: 700;">No permissions currently registered.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }

    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const applyFilters = function() {
        const q = ($('#search-permission').val() || '').toLowerCase().trim();
        const module = $('#filter-module').val();
        const status = $('#filter-status').val();
        const usage = $('#filter-usage').val();

        $('#permissionsTable tbody tr').each(function() {
            const $row = $(this);
            if ($row.find('td').length === 1) {
                return;
            }

            const rowSearch = String($row.data('search') || '');
            const rowModule = String($row.data('module') || '');
            const rowStatus = String($row.data('status') || '');
            const rowUsage = String($row.data('usage') || '');

            const matchesSearch = !q || rowSearch.indexOf(q) > -1;
            const matchesModule = !module || rowModule === module;
            const matchesStatus = !status || rowStatus === status;
            const matchesUsage = !usage || rowUsage === usage;

            $row.toggle(matchesSearch && matchesModule && matchesStatus && matchesUsage);
        });
    };

    $('#search-permission').on('input', applyFilters);
    $('#filter-module, #filter-status, #filter-usage').on('change', applyFilters);
});
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Permission Settings</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Permission Settings</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and manage access rules across your admin system.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn-agri btn-agri-primary" style="height: 44px; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none;">
            <i class="fas fa-plus"></i> Create Access Rule
        </a>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary);">
                <i class="fas fa-check-circle"></i>
                <span style="font-weight: 700;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #FEF2F2; border: 1px solid var(--agri-error); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-error);">
                <i class="fas fa-exclamation-circle"></i>
                <span style="font-weight: 700;">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white;">
            <div style="display: grid; grid-template-columns: 1.3fr 1fr 1fr 1fr; gap: 12px;">
                <input type="text" id="search-permission" class="form-agri" placeholder="Search permission..." style="margin-bottom: 0; height: 40px; font-size: 13px;">

                <select id="filter-module" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>

                <select id="filter-status" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>

                <select id="filter-usage" class="form-agri" style="margin-bottom: 0; height: 40px; font-size: 13px;">
                    <option value="">All Usage Areas</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" id="permissionsTable" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Permission</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Module</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Usage</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $perm)
                        @php
                            $moduleLabel = $perm->module ?: 'General';
                            $groupLabel = $perm->group ?: 'General';
                            $permissionLabel = $perm->display_name ?: ('Can ' . strtolower(($perm->action ?: 'manage')) . ' ' . strtolower($moduleLabel));
                            $status = $perm->is_active ? 'active' : 'disabled';
                        @endphp
                        <tr
                            data-module="{{ $moduleLabel }}"
                            data-status="{{ $status }}"
                            data-usage="{{ $groupLabel }}"
                            data-search="{{ strtolower($permissionLabel . ' ' . $moduleLabel . ' ' . $groupLabel) }}"
                            style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;"
                        >
                            <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $permissionLabel }}</td>
                            <td style="padding: 16px 24px; color: var(--agri-text-main); font-size: 13px; line-height: 1.5;">{{ $moduleLabel }}</td>
                            <td style="padding: 16px 24px;">
                                <span style="background: #EEF2FF; color: #4338CA; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid #C7D2FE;">{{ $groupLabel }}</span>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="background: {{ $perm->is_active ? 'var(--agri-primary-light)' : '#FEF2F2' }}; color: {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}40; min-width: 84px; text-align: center;">
                                        {{ $perm->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                    <form method="POST" action="{{ route('admin.permissions.toggle-status', $perm->id) }}" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <label class="switch" title="Toggle permission status">
                                            <input type="checkbox" {{ $perm->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                </div>
                            </td>
                            <td style="padding: 16px 24px;" class="text-end">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('admin.permissions.edit', $perm->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px; text-decoration: none;">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $perm->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #FEF2F2; color: var(--agri-error); border: none; border-radius: 999px;"
                                                onclick="return confirm('Delete permission {{ $perm->display_name ?: $perm->name }}?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div style="color: var(--agri-border); font-size: 40px; margin-bottom: 12px;"><i class="fas fa-shield-slash"></i></div>
                                <p style="color: var(--agri-text-muted); font-weight: 700;">No permissions currently registered.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }

    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const applyFilters = function() {
        const q = ($('#search-permission').val() || '').toLowerCase().trim();
        const module = $('#filter-module').val();
        const status = $('#filter-status').val();
        const usage = $('#filter-usage').val();

        $('#permissionsTable tbody tr').each(function() {
            const $row = $(this);
            if ($row.find('td').length === 1) {
                return;
            }

            const rowSearch = String($row.data('search') || '');
            const rowModule = String($row.data('module') || '');
            const rowStatus = String($row.data('status') || '');
            const rowUsage = String($row.data('usage') || '');

            const matchesSearch = !q || rowSearch.indexOf(q) > -1;
            const matchesModule = !module || rowModule === module;
            const matchesStatus = !status || rowStatus === status;
            const matchesUsage = !usage || rowUsage === usage;

            $row.toggle(matchesSearch && matchesModule && matchesStatus && matchesUsage);
        });
    };

    $('#search-permission').on('input', applyFilters);
    $('#filter-module, #filter-status, #filter-usage').on('change', applyFilters);
});
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Permission Settings</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Permission Settings</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage what each action allows in the system.</p>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-primary);">
                <i class="fas fa-check-circle"></i>
                <span style="font-weight: 700;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #FEF2F2; border: 1px solid var(--agri-error); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--agri-error);">
                <i class="fas fa-exclamation-circle"></i>
                <span style="font-weight: 700;">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-agri" style="position: sticky; top: 100px; background: white; padding: 32px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 24px;">Create Access Rule</h4>

                <form method="POST" action="{{ route('admin.permissions.store') }}" id="permission-create-form">
                    @csrf
                    <div style="margin-bottom: 18px;">
                        <label class="agri-label">Permission (Human-readable) <span class="text-danger">*</span></label>
                        <input type="text" name="human_name" id="create_human_name" class="form-agri" value="{{ old('human_name') }}" placeholder="e.g. Can create users" required>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label class="agri-label">Module <span class="text-danger">*</span></label>
                        <select name="module" id="create_module" class="form-agri" required>
                            <option value="">Choose a module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected(old('module') === $module)>{{ $module }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label class="agri-label">Select what this permission allows <span class="text-danger">*</span></label>
                        <select name="action" id="create_action" class="form-agri" required>
                            <option value="">Choose an option</option>
                            @foreach($actions as $actionKey => $actionLabel)
                                <option value="{{ $actionKey }}" @selected(old('action') === $actionKey)>{{ $actionLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    @error('human_name')<div style="color: var(--agri-error); font-size: 12px; margin-top: -10px; margin-bottom: 14px; font-weight: 600;">{{ $message }}</div>@enderror
                    @error('module')<div style="color: var(--agri-error); font-size: 12px; margin-top: -10px; margin-bottom: 14px; font-weight: 600;">{{ $message }}</div>@enderror
                    @error('action')<div style="color: var(--agri-error); font-size: 12px; margin-top: -10px; margin-bottom: 14px; font-weight: 600;">{{ $message }}</div>@enderror

                    <div style="margin-bottom: 18px;">
                        <label class="agri-label">Where is this used? <span class="text-danger">*</span></label>
                        <select name="group" class="form-agri" required>
                            <option value="">Choose usage area</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}" @selected(old('group') === $group)>{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label class="agri-label">What does this permission allow? <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-agri" rows="4" placeholder="Allows admins to create new users from the admin panel" required>{{ old('description') }}</textarea>
                    </div>

                    <div style="margin-bottom: 28px;">
                        <label class="agri-label">Status</label>
                        <div style="background: var(--agri-bg); padding: 10px 16px; border-radius: 12px; border: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active" value="1" checked style="cursor: pointer; width: 44px; height: 22px;">
                            </div>
                            <span style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Active</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 28px;">
                        <label class="form-check-label" style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="create_advanced_mode" class="form-check-input" style="margin: 0;"> Advanced Mode
                        </label>
                        <div id="create_advanced_panel" style="display: none; margin-top: 12px; padding: 14px; border: 1px dashed var(--agri-border); border-radius: 12px; background: #f8fafc;">
                            <label class="agri-label" style="margin-bottom: 8px;">Technical (for developers only): System Key <span class="text-danger">*</span></label>
                            <input type="text" name="system_key" id="create_system_key" class="form-agri" value="{{ old('system_key') }}" placeholder="admin.users.create" required readonly>
                            @error('system_key')<div style="color: var(--agri-error); font-size: 12px; margin-top: 8px; font-weight: 600;">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-agri btn-agri-primary w-100" style="height: 48px; font-weight: 700; font-size: 15px;">
                        <i class="fas fa-save me-2"></i> Save Permission
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
                <div style="padding: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--agri-border); background: white; gap: 16px; flex-wrap: wrap;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Permission Checklist</h4>
                        <span style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted);">{{ $permissions->count() }} permissions</span>
                    </div>
                    <div style="min-width: 220px;">
                        <select id="filter-group" class="form-agri" style="height: 40px; font-size: 13px; font-weight: 600;">
                            <option value="">All Groups</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="permsTable" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Permission</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Module</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Usage</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Status</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $perm)
                                @php
                                    $moduleLabel = $perm->module ?: $perm->group;
                                    $groupLabel = $perm->group ? ucfirst(str_replace('-', ' ', $perm->group)) : 'General';
                                    $permissionLabel = $perm->display_name ?: ('Can ' . strtolower(($perm->action ?: 'manage')) . ' ' . strtolower($moduleLabel));
                                @endphp
                                <tr data-group="{{ $perm->group }}" style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                                    <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $permissionLabel }}</td>
                                    <td style="padding: 16px 24px; color: var(--agri-text-main); font-size: 13px; line-height: 1.5;">{{ $moduleLabel }}</td>
                                    <td style="padding: 16px 24px;">
                                        <span style="background: #EEF2FF; color: #4338CA; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid #C7D2FE;">{{ $groupLabel }}</span>
                                    </td>
                                    <td style="padding: 16px 24px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="background: {{ $perm->is_active ? 'var(--agri-primary-light)' : '#FEF2F2' }}; color: {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid {{ $perm->is_active ? 'var(--agri-primary)' : 'var(--agri-error)' }}40; min-width: 84px; text-align: center;">
                                                {{ $perm->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                            <form method="POST" action="{{ route('admin.permissions.toggle-status', $perm->id) }}" style="margin: 0;">
                                                @csrf
                                                @method('PATCH')
                                                <label class="switch" title="Toggle permission status">
                                                    <input type="checkbox" {{ $perm->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <span class="slider"></span>
                                                </label>
                                            </form>
                                        </div>
                                    </td>
                                    <td style="padding: 16px 24px;" class="text-end">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="{{ route('admin.permissions.edit', $perm->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px; text-decoration: none;">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.permissions.destroy', $perm->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-agri" style="padding: 8px; background: #FEF2F2; color: var(--agri-error); border: none; border-radius: 999px;"
                                                        onclick="return confirm('Delete permission {{ $perm->name }}?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div style="color: var(--agri-border); font-size: 40px; margin-bottom: 12px;"><i class="fas fa-shield-slash"></i></div>
                                        <p style="color: var(--agri-text-muted); font-weight: 700;">No permissions currently registered.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }

    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
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

    const syncCreatePreview = function() {
        $('#create_human_name').val(moduleToHuman($('#create_module').val(), $('#create_action').val()));
        $('#create_system_key').val(moduleToKey($('#create_module').val(), $('#create_action').val()));
    };

    $('#create_module, #create_action').on('change', syncCreatePreview);
    $('#create_advanced_mode').on('change', function() {
        $('#create_advanced_panel').toggle($(this).is(':checked'));
        $('#create_system_key').prop('readonly', !$(this).is(':checked'));
        syncCreatePreview();
    });
    syncCreatePreview();

    $('#filter-group').on('change', function() {
        const g = $(this).val();
        if (!g) {
            $('#permsTable tbody tr').show();
            return;
        }

        $('#permsTable tbody tr').each(function() {
            $(this).toggle($(this).data('group') === g);
        });
    });
});
</script>
@endsection
