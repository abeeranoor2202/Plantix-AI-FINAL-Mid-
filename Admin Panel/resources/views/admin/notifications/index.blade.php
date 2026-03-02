@extends('layouts.app')

@section('content')


    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> {{trans('lang.dashboard')}}
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">{{trans('lang.notifications')}}</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-bell text-success me-2"></i>{{trans('lang.notifications')}}</h1>
        </div>
    </div>


    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <ul class="nav nav-tabs card-header-tabs w-100 border-0" style="margin: 0; gap: 24px;">
                            <li class="nav-item border-0">
                                <a class="nav-link active" href="{!! url()->current() !!}" style="color: var(--agri-primary); font-weight: 800; font-size: 14px; text-transform: uppercase; padding: 0 0 16px 0; border: none; border-bottom: 3px solid var(--agri-primary); background: transparent;"><i class="fa fa-list me-2"></i>{{trans('lang.notificaions_table')}}</a>
                            </li>
                            <li class="nav-item border-0">
                                <a class="nav-link" href="{!! url('notification/send') !!}" style="color: var(--agri-text-muted); font-weight: 700; font-size: 14px; text-transform: uppercase; padding: 0 0 16px 0; border: none; border-bottom: 3px solid transparent; background: transparent;"><i class="fa fa-plus me-2"></i>{{trans('lang.create_notificaion')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="table-responsive">


                            <table id="notificationTable" class="table mb-0" style="vertical-align: middle;">
                                <thead style="background: var(--agri-bg);">
                                    <tr>
                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; width: 60px;">
                                                <input type="checkbox" id="is_active">
                                                <label class="col-3 control-label d-none" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="fa fa-trash"></i> {{trans('lang.all')}}
                                                    </a>
                                                </label>
                                            </th>
                                        <?php } ?>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.subject')}}</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.message')}}</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.date_created')}}</th>
                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end;">{{trans('lang.actions')}}</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="append_restaurants">
                                    @forelse($notifications as $notif)
                                    <tr style="border-bottom: 1px solid var(--agri-border);">
                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <td style="padding: 18px 24px;"></td>
                                        <?php } ?>
                                        <td style="padding: 18px 24px; font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">{{ $notif->title ?? '—' }}</td>
                                        <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-main); max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Str::limit($notif->message ?? '', 80) }}</td>
                                        <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-muted);">
                                            <span style="background: #E0F2FE; color: #0369A1; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid #BAE6FD; margin-right: 8px;">{{ $notif->type ?? 'general' }}</span>
                                            {{ \Carbon\Carbon::parse($notif->created_at)->format('d M Y') }}
                                        </td>
                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <td style="padding: 18px 24px; text-align: end;">
                                                <button class="btn-agri delete-notif-btn" data-id="{{ $notif->id }}" style="padding: 6px 12px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; font-weight: 700;">
                                                    <i class="fas fa-trash me-1"></i>{{ trans('lang.delete') }}
                                                </button>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" style="padding: 40px 24px; text-align: center; color: var(--agri-text-muted);">
                                            <i class="fa fa-bell-slash" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                                            <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No notifications found.</p>
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

    </div>

@endsection

@section('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';

    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#notificationTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

        $(document).on('click', '.delete-notif-btn', function () {
            var id = $(this).data('id');
            if (confirm("Delete this notification?")) {
                $.ajax({
                    url: '{{ url("admin/notification/delete") }}/' + id,
                    method: 'POST',
                    data: { _method: 'DELETE', _token: csrfToken },
                    success: function () { location.reload(); },
                    error: function () { alert('Delete failed.'); }
                });
            }
        });
    });
</script>
@endsection
