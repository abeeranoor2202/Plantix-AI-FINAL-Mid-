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