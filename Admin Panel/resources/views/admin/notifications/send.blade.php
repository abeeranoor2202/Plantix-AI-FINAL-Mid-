@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-4 pb-3 border-bottom">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor fw-bold"><i class="fa fa-bell text-success me-2"></i>Send Notification</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.notification') }}">Notifications</a></li>
                <li class="breadcrumb-item active">Send</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa fa-bullhorn me-2 text-success"></i>Broadcast Notification</h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="data-table_processing" class="text-center py-3" style="display: none;">
                            <i class="fa fa-spinner fa-spin me-2"></i> Sending...
                        </div>
                        <div class="error_top alert alert-danger rounded border-0 shadow-sm" style="display:none"></div>
                        <div class="success_top alert alert-success rounded border-0 shadow-sm" style="display:none"></div>

                        <div class="row">
                            <div class="col-md-10 mx-auto">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-muted">Subject</label>
                                    <input type="text" class="form-control shadow-sm rounded-pill border-0" style="background:#f8f9fa;" id="subject" placeholder="Enter notification subject...">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-muted">Message</label>
                                    <textarea class="form-control shadow-sm border-0" style="background:#f8f9fa; border-radius:12px;" rows="5" id="message" placeholder="Enter the notification message..."></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-muted">Send To</label>
                                    <select id="role" class="form-control shadow-sm rounded-pill border-0" style="background:#f8f9fa;">
                                        <option value="customer">Customers (Farmers)</option>
                                        <option value="vendor">Vendors</option>
                                        <option value="expert">Experts</option>
                                        <option value="admin">Admins</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-4 d-flex justify-content-end gap-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <a href="{{ route('admin.notification') }}" class="btn btn-light rounded-pill px-4 shadow-sm fw-bold border">
                            <i class="fa fa-undo me-2"></i>Cancel
                        </a>
                        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold save-form-btn">
                            <i class="fa fa-paper-plane me-2"></i>Send Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $(".save-form-btn").click(function () {
        $(".success_top").hide();
        $(".error_top").hide();

        var subject = $("#subject").val().trim();
        var message = $("#message").val().trim();
        var role    = $("#role").val();

        if (!subject) {
            $(".error_top").show().html("<p>Please enter a subject.</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (!message) {
            $(".error_top").show().html("<p>Please enter a message.</p>");
            window.scrollTo(0, 0);
            return;
        }

        $("#data-table_processing").show();
        $(".save-form-btn").prop('disabled', true);

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: '{{ route("admin.notification.broadcast") }}',
            data: {
                role:    role,
                subject: subject,
                message: message,
                _token:  '{{ csrf_token() }}'
            },
            success: function (response) {
                $("#data-table_processing").hide();
                $(".save-form-btn").prop('disabled', false);
                if (response.success) {
                    $(".success_top").show().html("<p>" + response.message + "</p>");
                    window.scrollTo(0, 0);
                    setTimeout(function () {
                        window.location.href = '{{ route("admin.notification") }}';
                    }, 2500);
                } else {
                    $(".error_top").show().html("<p>" + response.message + "</p>");
                    window.scrollTo(0, 0);
                }
            },
            error: function (xhr) {
                $("#data-table_processing").hide();
                $(".save-form-btn").prop('disabled', false);
                var msg = xhr.responseJSON?.message ?? 'An error occurred. Please try again.';
                $(".error_top").show().html("<p>" + msg + "</p>");
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endsection
