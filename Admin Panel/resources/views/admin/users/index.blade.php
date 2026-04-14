@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Internal Governance</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.admin_plural')}}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage high-level administrative accounts and platform governance credentials.</p>
        </div>
        <a href="{!! route('admin.users.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <i class="fas fa-user-plus"></i>
            {{trans('lang.create_admin')}}
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

    {{-- Governance Ledger --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
        <div style="padding: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--agri-border); background: white;">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Administrator Registry</h4>
            
            @can('admin.perm', 'admin.users.delete')
                <button id="deleteAll" class="btn-agri" style="color: var(--agri-error); background: #FEF2F2; border: none; padding: 8px 16px; font-size: 13px; font-weight: 700;">
                    <i class="fas fa-trash-alt me-2"></i> Bulk Revoke Access
                </button>
            @endcan
        </div>
        
        <div class="table-responsive">
            <table id="adminTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        @can('admin.perm', 'admin.users.delete')
                        <th style="padding: 16px 24px; border: none; width: 50px;">
                            <div class="form-check m-0">
                                <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;">
                            </div>
                        </th>
                        @endcan
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Identity</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Communications</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Assigned Authority</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody id="append_list1">
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                            @can('admin.perm', 'admin.users.delete')
                            <td style="padding: 16px 24px;">
                                <div class="form-check m-0">
                                    <input type="checkbox" id="is_open_{{$user->id}}" class="is_open form-check-input" dataid="{{$user->id}}" style="cursor: pointer; width: 18px; height: 18px;">
                                </div>
                            </td>
                            @endcan

                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; border: 1px solid var(--agri-primary)30;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ $user->name }}</div>
                                        <div style="font-size: 10px; color: var(--agri-text-muted); font-weight: 800; text-transform: uppercase; margin-top: 2px;">
                                            <i class="fas fa-crown me-1" style="color: var(--agri-secondary);"></i> Governance Member
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 8px; color: var(--agri-text-main); font-size: 13px; font-weight: 600;">
                                    <i class="far fa-envelope-open" style="color: var(--agri-primary); font-size: 12px;"></i>
                                    {{ $user->email }}
                                </div>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="background: var(--agri-bg); color: var(--agri-text-heading); padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--agri-border);">
                                    <i class="fas fa-key" style="font-size: 10px; color: var(--agri-primary);"></i>
                                    {{ $user->roleName }}
                                </div>
                            </td>
                            <td style="padding: 16px 24px;" class="text-end">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{route('admin.users.edit', ['id' => $user->id])}}" class="btn-agri" style="padding: 8px 12px; background: var(--agri-bg); color: var(--agri-text-heading); border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;" title="Edit Governance Account">
                                        <i class="fas fa-user-cog"></i> Modify
                                    </a>
                                    @if($user->id != 1)
                                        @can('admin.perm', 'admin.users.delete')
                                        <a href="{{route('admin.users.delete', ['id' => $user->id])}}" class="btn-agri" style="padding: 8px 12px; background: #FEF2F2; color: var(--agri-error); border-radius: 10px; border: none; text-decoration: none;" onclick="return confirm('CRITICAL: Permanently revoke administrative access for this account?')" title="Revoke Access">
                                            <i class="fas fa-user-minus"></i>
                                        </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#adminTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

        $('#is_active').on('click', function () {
            $('#adminTable .is_open').prop('checked', $(this).prop('checked'));
        });

        $('#deleteAll').on('click', function () {
            if ($('#adminTable .is_open:checked').length) {
                if (confirm('Are You Sure want to Delete Selected Data ?')) {
                    var arrayUsers = [];
                    $('#adminTable .is_open:checked').each(function () {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('admin-users/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    window.location.href = url;
                }
            } else {
                alert('Please Select Any One Record.');
            }
        });
    });
</script>
@endsection
