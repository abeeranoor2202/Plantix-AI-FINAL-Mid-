@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Access Control</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Platform Roles</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage role hierarchy and permission coverage in one consistent table.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.permissions.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: inline-flex; align-items: center; font-weight: 700;">Permission Registry</a>
            @can('admin.roles')
                <a href="{{ route('admin.role.save') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: inline-flex; align-items: center; font-weight: 700;">Create Role</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 12px 20px; color: #166534; font-weight: 700;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Role Authority Matrix</h4>
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-search" style="color: var(--agri-text-muted);"></i></span>
                <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search roles..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
            </div>
        </div>

        <div class="table-responsive">
            <table id="roleTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Hierarchy</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Role</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Permissions</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="px-4 py-3">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $role->role_name }}</div>
                                @if($role->guard === 'admin')
                                    <small style="color: #92400e; font-weight: 700;">SYSTEM GUARDED</small>
                                @endif
                            </td>
                            <td class="px-4 py-3"><span class="badge rounded-pill bg-success">{{ $role->permissions_count ?? 0 }} Nodes</span></td>
                            <td class="px-4 py-3">
                                @if($role->is_active)
                                    <span class="badge rounded-pill bg-success">OPERATIONAL</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary">DEACTIVATED</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    @can('admin.perm', 'role.edit')
                                        <a href="{{ route('admin.role.edit', $role->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endcan
                                    @can('admin.perm', 'role.delete')
                                        <form method="POST" action="{{ route('admin.role.delete', $role->id) }}" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5" style="color: var(--agri-text-muted);">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#roleTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
});
</script>
@endsection
