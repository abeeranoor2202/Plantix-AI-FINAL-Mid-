@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ trans('lang.app_setting_notifications')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ trans('lang.app_setting_notifications')}}</h1>
        <p style="color: var(--agri-text-muted); margin-top: 4px;">Configure push notification settings.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">
            <div class="card-agri" style="padding: 40px;">
                
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <form>
                    {{-- Primary Toggle --}}
                    <div style="background: var(--agri-bg); padding: 24px; border-radius: 16px; margin-bottom: 32px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 4px;">{{trans('lang.app_setting_enable_notifications')}}</h4>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">{!! trans('lang.app_setting_enable_notifications_help') !!}</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" class="enable_pushnotification" id="enable_pushnotification" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary notification_save_btn" style="flex: 2; height: 48px; font-size: 15px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Broadcast Settings
                        </button>
                        <a href="{{url('/dashboard')}}" class="btn-agri btn-agri-outline" style="flex: 1; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 15px;">
                             Discard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';

    $(document).ready(function () {
        // Pre-fill settings from DB
        @if($settings->has('push_notification_enabled'))
        $("#enable_pushnotification").prop('checked', {{ $settings->get('push_notification_enabled') == '1' ? 'true' : 'false' }});
        @endif

        $(".notification_save_btn").click(function () {
            jQuery("#data-table_processing").show();
            $.ajax({
                url: '{{ route("admin.settings.app.notifications.save") }}',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    push_notification_enabled: $("#enable_pushnotification").is(":checked") ? 1 : 0
                },
                success: function (res) {
                    jQuery("#data-table_processing").hide();
                    if (res.success) {
                        toastr.success('Notification settings saved!');
                    } else {
                        toastr.error(res.message || 'Save failed.');
                    }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    toastr.error('Server error.');
                }
            });
        });
    });
</script>
@endsection
