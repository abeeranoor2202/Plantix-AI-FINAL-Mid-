@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('admin.role.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Platform Roles</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Permission Registry</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Access Node Registry</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage the granular functional permissions that drive the RBAC ecosystem.</p>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: var(--agri-primary-light); border: 1px solid var(--agri-primary); border-radius: 12px; padding: 12px 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; color: var(--agri-primary);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-check-circle"></i>
                    <span style="font-weight: 700;">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Side Registry Form --}}
        <div class="col-lg-4">
            <div class="card-agri" style="position: sticky; top: 100px; background: white; padding: 32px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 24px;">Register New Capability</h4>
                
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label class="agri-label">Registry Slug <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-agri" value="{{ old('name') }}" placeholder="e.g. audit-stock-levels" required>
                        <p style="font-size: 11px; color: var(--agri-text-muted); margin: 6px 0 0 0; font-style: italic;">Unique technical identifier (lowercase, hyphenated)</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label class="agri-label">Functional Module Group <span class="text-danger">*</span></label>
                        <input type="text" name="group" class="form-agri" list="group-options" value="{{ old('group') }}" placeholder="e.g. inventory" required>
                        <datalist id="group-options">
                            @foreach($groups as $g)
                                <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div style="margin-bottom: 32px;">
                        <label class="agri-label">Administrative UI Label <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" class="form-agri" value="{{ old('display_name') }}" placeholder="e.g. Perform Stock Audits" required>
                    </div>

                    <button type="submit" class="btn-agri btn-agri-primary w-100" style="height: 48px; font-weight: 700; font-size: 15px;">
                        <i class="fas fa-plus-circle me-2"></i> Commit to Registry
                    </button>
                </form>
            </div>
        </div>

        {{-- Main Registry Ledger --}}
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
                <div style="padding: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--agri-border); background: white;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Capability Ledger</h4>
                        <span style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted);">Total Defined Nodes: {{ $permissions->count() }}</span>
                    </div>
                    <div style="min-width: 220px;">
                        <select id="filter-group" class="form-agri" style="height: 40px; font-size: 13px; font-weight: 600;">
                            <option value="">All Module Contexts</option>
                            @foreach($groups as $g)
                                <option value="{{ $g }}">{{ ucfirst(str_replace('-', ' ', $g)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="permsTable" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Hierarchy</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Internal Slug</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;">Human Label</th>
                                <th style="padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 800; letter-spacing: 0.5px; border: none;" class="text-end">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $perm)
                            <tr data-group="{{ $perm->group }}" style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                                <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-muted); font-size: 13px;">{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="padding: 16px 24px;">
                                    <code style="background: var(--agri-bg); color: var(--agri-error); padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 700; border: 1px solid var(--agri-border);">{{ $perm->name }}</code>
                                    <div style="font-size: 10px; font-weight: 800; color: var(--agri-primary); text-transform: uppercase; margin-top: 6px;">
                                        <i class="fas fa-tag me-1"></i> {{ $perm->group }}
                                    </div>
                                </td>
                                <td style="padding: 16px 24px;">
                                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $perm->display_name }}</div>
                                </td>
                                <td style="padding: 16px 24px;" class="text-end">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button type="button" class="btn-agri edit-perm-btn" style="padding: 8px 12px; background: var(--agri-bg); color: var(--agri-text-heading); border-radius: 10px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;"
                                                data-id="{{ $perm->id }}" data-name="{{ $perm->name }}" data-group="{{ $perm->group }}" data-display="{{ $perm->display_name }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $perm->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-agri" style="padding: 8px 12px; background: #FEF2F2; color: var(--agri-error); border: none; border-radius: 10px; font-size: 12px; font-weight: 700;"
                                                    onclick="return confirm('CRITICAL: Delete permission node \'{{ $perm->name }}\'? This will cascade to all roles.')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div style="color: var(--agri-border); font-size: 40px; mb-3;"><i class="fas fa-shield-slash"></i></div>
                                    <p style="color: var(--agri-text-muted); font-weight: 700;">No capability nodes currently registered.</p>
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

{{-- Edit Permission Modal --}}
<div class="modal fade" id="editPermModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; padding: 10px;">
            <div class="modal-header border-0 pb-0">
                <h5 style="font-weight: 800; color: var(--agri-primary-dark);">Edit Capability Registry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 10px;"></button>
            </div>
            <form method="POST" id="editPermForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div style="margin-bottom: 20px;">
                        <label class="agri-label">Internal Slug</label>
                        <input type="text" name="name" id="edit_name" class="form-agri" required>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="agri-label">Functional Group</label>
                        <input type="text" name="group" id="edit_group" class="form-agri" list="group-options" required>
                    </div>
                    <div style="margin-bottom: 0;">
                        <label class="agri-label">Human Label</label>
                        <input type="text" name="display_name" id="edit_display_name" class="form-agri" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0" style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" class="btn-agri btn-agri-primary w-100" style="height: 48px; font-weight: 800;">Commit Node Updates</button>
                    <button type="button" class="btn-agri btn-agri-outline w-100" data-bs-dismiss="modal" style="height: 40px; font-weight: 700;">Cancel Modification</button>
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
    $('#filter-group').on('change', function() {
        const g = $(this).val();
        if (!g) { $('#permsTable tbody tr').show(); return; }
        $('#permsTable tbody tr').each(function() {
            $(this).toggle($(this).data('group') === g);
        });
    });

    $(document).on('click', '.edit-perm-btn', function() {
        const id      = $(this).data('id');
        const name    = $(this).data('name');
        const group   = $(this).data('group');
        const display = $(this).data('display');
        const url     = '{{ route("admin.permissions.update", ":id") }}'.replace(':id', id);

        $('#edit_name').val(name);
        $('#edit_group').val(group);
        $('#edit_display_name').val(display);
        $('#editPermForm').attr('action', url);
        $('#editPermModal').modal('show');
    });
});
</script>
@endsection
