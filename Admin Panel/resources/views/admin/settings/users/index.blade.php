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

    @php
        $canDeleteUsers = \Illuminate\Support\Facades\Gate::check('admin.perm', 'users.delete')
            || \Illuminate\Support\Facades\Gate::check('admin.perm', 'admin.users.delete');
    @endphp

    {{-- Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                 <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Customer List</h4>
                 <div id="data-table_processing" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;"></div>
            </div>
            
            <form method="GET" action="{{ route('admin.users') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div class="input-group" style="width: 280px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: var(--agri-border);">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" name="search" class="form-agri border-start-0" value="{{ request('search') }}" placeholder="Search customers..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
                <select name="status" class="form-agri" style="height: 42px; min-width: 140px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="banned" @selected(request('status') === 'banned')>Banned</option>
                </select>
                <select name="activity" class="form-agri" style="height: 42px; min-width: 180px; margin-bottom: 0;">
                    <option value="">All Activity</option>
                    <option value="active_7d" @selected(request('activity') === 'active_7d')>Active in 7 days</option>
                    <option value="active_30d" @selected(request('activity') === 'active_30d')>Active in 30 days</option>
                    <option value="inactive_30d" @selected(request('activity') === 'inactive_30d')>Inactive 30+ days</option>
                    <option value="never_logged_in" @selected(request('activity') === 'never_logged_in')>Never logged in</option>
                </select>
                <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                <a href="{{ route('admin.users') }}" class="btn-agri btn-agri-outline" style="height: 42px; padding: 0 16px; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
            </form>
        </div>
        
        <div class="table-responsive">
            <table id="userTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.extra_image')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.user_name')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.email')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.date')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.active')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">{{trans('lang.actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $editRoute = route('admin.users.edit', $user->id);
                        $viewRoute = route('admin.users.view', $user->id);
                        $deleteRoute = route('admin.users.delete', $user->id);
                        $rawPhoto = (string) ($user->profile_photo ?? '');
                        $normalizedPhoto = ltrim($rawPhoto, '/');
                        $isExternalPhoto = \Illuminate\Support\Str::startsWith($rawPhoto, ['http://', 'https://', '//']);

                        if ($normalizedPhoto === '') {
                            $photoUrl = asset('images/favicon.png');
                        } elseif ($isExternalPhoto) {
                            $photoUrl = $rawPhoto;
                        } elseif (\Illuminate\Support\Str::startsWith($normalizedPhoto, 'storage/')) {
                            $photoUrl = asset($normalizedPhoto);
                        } else {
                            $photoUrl = asset('storage/' . $normalizedPhoto);
                        }

                        $fallbackPhotoUrl = asset('images/favicon.png');
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div style="width:40px;height:40px;border-radius:8px;overflow:hidden;border:1px solid var(--agri-border);">
                                <img src="{{ $photoUrl }}"
                                     onerror="this.onerror=null;this.src='{{ $fallbackPhotoUrl }}'"
                                     style="width:100%;height:100%;object-fit:cover;" alt="">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight:700;color:var(--agri-text-heading);">
                                <a href="{{ $viewRoute }}" style="text-decoration:none;color:inherit;">
                                    {{ $user->name ?? 'No Name' }}
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
                            <div class="text-end" style="display:flex;justify-content:flex-end;gap:8px;">
                                <a href="{{ $viewRoute }}" class="btn-agri" style="padding:8px;background:var(--agri-bg);color:var(--agri-primary);border-radius:10px;" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ $editRoute }}" class="btn-agri" style="padding:8px;background:var(--agri-bg);color:var(--agri-primary);border-radius:10px;" title="Edit"><i class="fas fa-edit"></i></a>
                                @if($canDeleteUsers)
                                <form method="POST" action="{{ $deleteRoute }}" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-agri" style="padding:8px;background:var(--agri-error-light);color:var(--agri-error);border-radius:10px;border:none;" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5" style="color:var(--agri-text-muted);">No users match your current filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div style="padding: 20px 24px; background: #fff; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif

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
