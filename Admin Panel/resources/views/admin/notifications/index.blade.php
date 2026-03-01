@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-bell text-success me-2"></i>{{trans('lang.notifications')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item">{{trans('lang.notifications')}}</li>
                <li class="breadcrumb-item active">{{trans('lang.notifications')}}</li>
            </ol>
        </div>
    </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100 border-0">
                            <li class="nav-item border-0">
                                <a class="nav-link active fw-bold text-success border-0 pb-3" href="{!! url()->current() !!}"><i
                                        class="fa fa-list mr-2"></i>{{trans('lang.notificaions_table')}}</a>
                            </li>
                            <li class="nav-item border-0">
                                <a class="nav-link fw-bold text-muted border-0 pb-3" href="{!! url('notification/send') !!}"><i
                                        class="fa fa-plus mr-2"></i>{{trans('lang.create_notificaion')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">

                        <div class="table-responsive m-t-10">


                            <table id="notificationTable"
                                class="table table-hover mb-0"
                                style="vertical-align: middle;">

                                <thead>

                                    <tr>
                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                        <?php } ?>

                                        <th>{{trans('lang.subject')}}</th>

                                        <th>{{trans('lang.message')}}</th>

                                        <th>{{trans('lang.date_created')}}</th>

                                        <?php if (in_array('notification.delete', json_decode(@session('admin_permissions'), true))) { ?>
                                            <th>{{trans('lang.actions')}}</th>
                                        <?php } ?>

                                    </tr>

                                </thead>

                                <tbody id="append_restaurants">
                            @forelse($notifications as $notif)
                            <tr>
                                <td style="font-weight:700;">{{ $notif->title ?? '—' }}</td>
                                <td>{{ Str::limit($notif->message ?? '', 80) }}</td>
                                <td><span class="badge bg-info text-dark">{{ $notif->type ?? 'general' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($notif->created_at)->format('d M Y') }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger delete-notif-btn" data-id="{{ $notif->id }}" style="border-radius:8px;font-weight:700;">
                                        <i class="fas fa-trash me-1"></i>{{ trans('lang.delete') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No notifications found.</td></tr>
                            @endforelse


                                </tbody>

                            </table>

                        </div>

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
