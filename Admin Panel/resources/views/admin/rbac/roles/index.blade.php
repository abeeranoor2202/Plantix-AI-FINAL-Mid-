@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Access Control</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Platform Roles</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Configure administrative role hierarchies and high-level ecosystem permissions.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.permissions.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                <i class="fas fa-shield-alt"></i> Permission Registry
            </a>
            @can('admin', auth('admin')->user())
            <a href="{{ route('admin.role.save') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 700;">
                <i class="fas fa-plus-circle"></i> Create New Role
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div style="background: var(--agri-primary-light); color: var(--agri-primary); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--agri-primary);">
            <span><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="background:none; border:none; color:inherit; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- Role Matrix --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
        <div style="padding: 24px; border-bottom: 1px solid var(--agri-border); background: white;">
            <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Role Authority Matrix</h5>
        </div>

        <div class="table-responsive">
            <table id="roleTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Hierarchy</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Role Identity</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Scope (Permissions)</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Integrity Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-muted); font-size: 13px;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--agri-bg); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; border: 1px solid var(--agri-border);">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ $role->role_name }}</div>
                                    @if($role->guard === 'admin')
                                        <div style="display: inline-flex; align-items:center; gap:4px; font-size: 9px; font-weight: 900; color: #7c2d12; background: #ffedd5; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; margin-top: 2px;">
                                            <i class="fas fa-lock" style="font-size: 8px;"></i> System Guarded
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 4px 14px; border-radius: 100px; font-size: 11px; font-weight: 800;">
                                <i class="fas fa-bolt" style="font-size: 10px;"></i>
                                {{ $role->permissions_count ?? 0 }} Functional Nodes
                            </div>
                        </td>
                        <td style="padding: 16px 24px;" class="text-center">
                            @if($role->is_active)
                                <span style="background: var(--agri-success-light); color: var(--agri-success); padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
                                    <span style="width: 6px; height: 6px; background: var(--agri-success); border-radius: 50%;"></span>
                                    Operational
                                </span>
                            @else
                                <span style="background: #FEF2F2; color: var(--agri-error); padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
                                    <span style="width: 6px; height: 6px; background: var(--agri-error); border-radius: 50%;"></span>
                                    Deactivated
                                </span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px;" class="text-end">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('admin.role.edit', $role->id) }}" class="btn-agri" style="padding: 8px 12px; background: var(--agri-bg); color: var(--agri-text-heading); border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-edit"></i> Configure
                                </a>
                                <a href="{{ route('admin.role.delete', $role->id) }}" class="btn-agri" style="padding: 8px 12px; background: #FEF2F2; color: var(--agri-error); border-radius: 10px; border: none; text-decoration: none; font-size: 12px; font-weight: 700;" onclick="return confirm('CRITICAL: Permanently delete this role? All associated users will revert to non-privileged access.')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div style="color: var(--agri-border); font-size: 48px; margin-bottom: 20px;"><i class="fas fa-id-badge"></i></div>
                            <div style="font-weight: 700; color: var(--agri-text-muted);">No administrative roles identified in the matrix.</div>
                        </td>
                    </tr>
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
