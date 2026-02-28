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
        <p style="color: var(--agri-text-muted); margin-top: 4px;">Configure real-time push notification channels and Firebase cloud integration.</p>
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

                    {{-- Technical Configuration --}}
                    <div style="margin-bottom: 32px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
                            <i class="fas fa-fire" style="color: #FFA000; font-size: 20px;"></i>
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Firebase Cloud Configuration</h4>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_fcm_key')}}</label>
                                <input type="text" class="form-agri fcm_key" placeholder="Enter Server Key">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{!! trans('lang.app_setting_fcm_key_help') !!}</div>
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_api_key')}}</label>
                                <input type="text" class="form-agri firebase_api_key">
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_project_id')}}</label>
                                <input type="text" class="form-agri firebase_project_id">
                            </div>

                            <div class="col-md-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_database_url')}}</label>
                                <input type="text" class="form-agri firebase_db_url">
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_storage_bucket')}}</label>
                                <input type="text" class="form-agri firebase_storage_bucket">
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_app_id')}}</label>
                                <input type="text" class="form-agri firebase_app_id">
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_auth_domain')}}</label>
                                <input type="text" class="form-agri firebase_auth_domain">
                            </div>

                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_messaging_sender_id')}}</label>
                                <input type="text" class="form-agri firebase_message_sender_id">
                            </div>

                            <div class="col-md-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_firebase_measurement_id')}}</label>
                                <input type="text" class="form-agri firebase_measurment_id">
                            </div>
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
    </div>
@endsection

@section('scripts')

    <script>


        var database = firebase.firestore();
        var ref = database.collection('settings').doc("pushNotification");


        $(document).ready(function () {
            jQuery("#data-table_processing").show();
            ref.get().then(async function (snapshots) {
                var pushNotification = snapshots.data();

                if (pushNotification == undefined) {
                    database.collection('settings').doc('pushNotification').set({});
                }

                try {
                    if (pushNotification.isEnabled) {
                        $(".enable_pushnotification").prop("checked", true);
                    }

                    $(".fcm_key").val(pushNotification.firebaseCloudKey);
                    $(".firebase_api_key").val(pushNotification.apiKey);
                    $(".firebase_db_url").val(pushNotification.databaseURL);
                    $(".firebase_storage_bucket").val(pushNotification.storageBucket);
                    $(".firebase_app_id").val(pushNotification.applicationId);
                    $(".firebase_auth_domain").val(pushNotification.authDomain);
                    $(".firebase_project_id").val(pushNotification.projectId);
                    $(".firebase_message_sender_id").val(pushNotification.messagingSenderId);
                    $(".firebase_measurment_id").val(pushNotification.measurmentId);

                } catch (error) {

                }
                jQuery("#data-table_processing").hide();

            })


            $(".notification_save_btn").click(function () {

                var isEnabled = $(".enable_pushnotification").is(":checked");
                var fcmKey = $(".fcm_key").val();
                var firebaseApiKey = $(".firebase_api_key").val();
                var firebaseDbURL = $(".firebase_db_url").val();
                var storageBucket = $(".firebase_storage_bucket").val();
                var appId = $(".firebase_app_id").val();
                var authDomain = $(".firebase_auth_domain").val();
                var projectId = $(".firebase_project_id").val();
                var messageSenderId = $(".firebase_message_sender_id").val();
                var measurmentId = $(".firebase_measurment_id").val();

                database.collection('settings').doc("pushNotification").update({
                    'isEnabled': isEnabled,
                    'firebaseCloudKey': fcmKey,
                    'apiKey': firebaseApiKey,
                    'databaseURL': firebaseDbURL,
                    'storageBucket': storageBucket,
                    'applicationId': appId,
                    'authDomain': authDomain,
                    'projectId': projectId,
                    'messagingSenderId': messageSenderId,
                    'measurmentId': measurmentId
                }).then(function (result) {
                    window.location.href = '{{ url()->current() }}';
                });


            })
        })


    </script>

@endsection