@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.user_plural')}}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.user_table')}}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage your platform's farmers and customers efficiently.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{!! route('admin.users.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i>
                {{trans('lang.user_create')}}
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                 <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Customer List</h4>
                 <div id="data-table_processing" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;"></div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: var(--agri-border);">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search farmers..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>

                <?php if (in_array('user.delete', json_decode(@session('admin_permissions'),true))) { ?>
                    <button id="deleteAll" class="btn-agri btn-agri-outline" style="color: var(--agri-error); border-color: var(--agri-error)20; background: var(--agri-error-light); padding: 8px 20px; font-size: 14px; height: 42px;">
                        <i class="fas fa-trash-alt" style="margin-right: 6px;"></i> Delete Selected
                    </button>
                <?php } ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="userTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; border: none; width: 50px;">
                            <div class="form-check m-0">
                                <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer;">
                            </div>
                        </th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.extra_image')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.user_name')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.email')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.date')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.active')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Wallet</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">{{trans('lang.actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $editRoute = route('admin.users.edit', $user->id);
                        $viewRoute = route('admin.users.view', $user->id);
                        $deleteRoute = route('admin.users.delete', $user->id);
                        $canDelete = in_array('user.delete', json_decode(@session('admin_permissions'), true) ?? []);
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="form-check m-0">
                                <input type="checkbox" class="is_open form-check-input" data-id="{{ $user->id }}" style="cursor:pointer;">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="width:40px;height:40px;border-radius:8px;overflow:hidden;border:1px solid var(--agri-border);">
                                <img src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/placeholder.png') }}"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}'"
                                     style="width:100%;height:100%;object-fit:cover;" alt="">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight:700;color:var(--agri-text-heading);">
                                <a href="{{ $viewRoute }}" style="text-decoration:none;color:inherit;">
                                    {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'No Name' }}
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-size:13px;color:var(--agri-text-muted);">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-size:13px;font-weight:500;">
                                {{ $user->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $user->created_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <label class="switch">
                                <input type="checkbox" class="toggle-active" data-id="{{ $user->id }}" {{ $user->active ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <span style="font-size:13px;font-weight:600;">
                                {{ number_format($user->wallet_amount ?? 0, 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-end" style="display:flex;justify-content:flex-end;gap:8px;">
                                <a href="{{ $viewRoute }}" class="btn-agri" style="padding:8px;background:var(--agri-bg);color:var(--agri-primary);border-radius:10px;" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ $editRoute }}" class="btn-agri" style="padding:8px;background:var(--agri-bg);color:var(--agri-primary);border-radius:10px;" title="Edit"><i class="fas fa-edit"></i></a>
                                @if($canDelete)
                                <a href="{{ $deleteRoute }}" class="btn-agri" style="padding:8px;background:var(--agri-error-light);color:var(--agri-error);border-radius:10px;"
                                   onclick="return confirm('Delete this user?')" title="Delete"><i class="fas fa-trash"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5" style="color:var(--agri-text-muted);">{{trans('lang.no_record_found')}}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        

    </div>
</div>

<style>
    /* Custom Slider for Active Toggle */
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
    input:checked + .slider {
        background-color: var(--agri-primary);
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    
    #userTable tbody tr:hover {
        background-color: rgba(var(--agri-primary-rgb), 0.02);
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">

    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#userTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

        $('#is_active').on('click', function () {
            $('#userTable .is_open').prop('checked', $(this).prop('checked'));
        });

        $('#deleteAll').on('click', function () {
            var ids = $('#userTable .is_open:checked').map(function () {
                return $(this).data('id');
            }).get();
            if (!ids.length) { alert("{{ trans('lang.select_delete_alert') }}"); return; }
            if (!confirm("{{ trans('lang.selected_delete_alert') }}")) return;
            $("#data-table_processing").show();
            window.location.href = '{{ route("admin.users.delete", "__ID__") }}'.replace('__ID__', ids[0]);
        });

        $(document).on('change', '.toggle-active', function () {
            var id  = $(this).data('id');
            var val = $(this).is(':checked') ? 1 : 0;
            $.ajax({
                url: '{{ url("api/admin/users") }}/' + id + '/toggle-active',
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { is_active: val }
            });
        });
    });
</script>
@endsection
