@extends('layouts.app')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.notification') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Notifications
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Send</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-bell text-success me-2"></i>Send Notification</h1>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fa fa-paper-plane" style="color: var(--agri-primary);"></i> Custom Notification
                        </h2>
                    </div>
                    <div style="padding: 32px 28px;">
                        <div id="data-table_processing" class="text-center py-3" style="display: none; color: var(--agri-text-muted);">
                            <i class="fa fa-spinner fa-spin me-2"></i> Sending...
                        </div>
                        <div class="error_top alert alert-danger" style="display:none; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; border-radius: 12px; padding: 16px; font-size: 14px; font-weight: 600; margin-bottom: 24px;"></div>
                        <div class="success_top alert alert-success" style="display:none; background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 12px; padding: 16px; font-size: 14px; font-weight: 600; margin-bottom: 24px;"></div>

                        <div class="row">
                            <div class="col-md-10 mx-auto">
                                <!-- Notification Type Selection -->
                                <div class="mb-4">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">
                                        <i class="fa fa-list me-2"></i>Send To (Select Type)
                                    </label>
                                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; color: var(--agri-text-heading);">
                                            <input type="radio" name="send-type" value="role" checked style="cursor: pointer;">
                                            <span>All Users by Role</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; color: var(--agri-text-heading);">
                                            <input type="radio" name="send-type" value="single" style="cursor: pointer;">
                                            <span>Specific User</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Role Selection (Default) -->
                                <div id="role-section" class="mb-4">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Select Role</label>
                                    <select id="role" class="form-agri">
                                        <option value="customer">Customers (Farmers)</option>
                                        <option value="vendor">Vendors</option>
                                        <option value="expert">Experts</option>
                                        <option value="admin">Admins</option>
                                    </select>
                                </div>

                                <!-- User Selection (Hidden by default) -->
                                <div id="user-section" class="mb-4" style="display: none;">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Select User</label>
                                    <select id="user-id" class="form-agri">
                                        <option value="">-- Choose a User --</option>
                                    </select>
                                    <small style="color: var(--agri-text-muted); display: block; margin-top: 8px;">Type to search users by name or email</small>
                                </div>

                                <!-- Title -->
                                <div class="mb-4">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">
                                        <i class="fa fa-heading me-2"></i>Subject/Title
                                    </label>
                                    <input type="text" class="form-agri" id="subject" placeholder="Enter notification subject..." maxlength="255">
                                </div>

                                <!-- Message -->
                                <div class="mb-4">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">
                                        <i class="fa fa-envelope me-2"></i>Message
                                    </label>
                                    <textarea class="form-agri" rows="6" id="message" placeholder="Enter the notification message..." maxlength="1000"></textarea>
                                    <small style="color: var(--agri-text-muted); display: block; margin-top: 4px;">
                                        <span id="char-count">0</span>/1000 characters
                                    </small>
                                </div>

                                <!-- Email Delivery Option -->
                                <div class="mb-4" style="background: #F0F9FF; border: 1px solid #BFE7F9; border-radius: 12px; padding: 16px;">
                                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; font-weight: 500; color: var(--agri-text-heading);">
                                        <input type="checkbox" id="send_email" style="cursor: pointer; margin-top: 4px;">
                                        <span>
                                            <div style="font-weight: 600; margin-bottom: 4px;">
                                                <i class="fa fa-envelope me-2" style="color: var(--agri-primary);"></i>Also Send via Email (SMTP)
                                            </div>
                                            <small style="color: var(--agri-text-muted); display: block;">
                                                Check this box to send the notification via email to the selected users' email addresses in addition to in-app notification.
                                            </small>
                                        </span>
                                    </label>
                                </div>

                                <!-- Info Box -->
                                <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #92400E; font-size: 13px; line-height: 1.6;">
                                        <i class="fa fa-info-circle me-2" style="color: #F59E0B;"></i>
                                        <strong>Note:</strong> Notifications are sent asynchronously using a job queue. In-app notifications are stored immediately in the database, while email delivery may take a few moments to process.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 24px 28px; border-top: 1px solid var(--agri-border); display: flex; justify-content: flex-end; gap: 16px; background: #F9FAFB;">
                        <a href="{{ route('admin.notification') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                            <i class="fa fa-undo"></i> Cancel
                        </a>
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn">
                            <i class="fa fa-paper-plane"></i> Send Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const sendTypeRadios = $('input[name="send-type"]');
    const roleSection = $('#role-section');
    const userSection = $('#user-section');
    const userSelect = $('#user-id');

    // Toggle between role and user selection
    sendTypeRadios.on('change', function() {
        if ($(this).val() === 'role') {
            roleSection.show();
            userSection.hide();
            userSelect.val('');
        } else {
            roleSection.hide();
            userSection.show();
            loadUsers();
        }
    });

    // Load users when opening user section
    function loadUsers() {
        $.ajax({
            url: '{{ route("admin.notification.users.list") }}',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                userSelect.html('<option value="">-- Choose a User --</option>');
                if (response.success && response.users.length > 0) {
                    response.users.forEach(function(user) {
                        userSelect.append(
                            '<option value="' + user.id + '">' + 
                            user.name + ' (' + user.email + ')</option>'
                        );
                    });
                }
            }
        });
    }

    // Character counter
    $('#message').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count);
    });

    // Send button
    $(".save-form-btn").click(function () {
        $(".success_top").hide();
        $(".error_top").hide();

        const sendType = $('input[name="send-type"]:checked').val();
        const subject = $("#subject").val().trim();
        const message = $("#message").val().trim();
        const sendEmail = $("#send_email").is(':checked');

        if (!subject) {
            $(".error_top").show().html("<p><i class='fa fa-exclamation-circle me-2'></i>Please enter a subject.</p>");
            window.scrollTo(0, 0);
            return;
        }
        if (!message) {
            $(".error_top").show().html("<p><i class='fa fa-exclamation-circle me-2'></i>Please enter a message.</p>");
            window.scrollTo(0, 0);
            return;
        }

        const data = {
            subject: subject,
            message: message,
            send_email: sendEmail,
            _token: '{{ csrf_token() }}'
        };

        // Add role or user_id based on send type
        if (sendType === 'role') {
            data.role = $("#role").val();
        } else {
            const userId = $("#user-id").val();
            if (!userId) {
                $(".error_top").show().html("<p><i class='fa fa-exclamation-circle me-2'></i>Please select a user.</p>");
                window.scrollTo(0, 0);
                return;
            }
            data.user_id = userId;
        }

        $("#data-table_processing").show();
        $(".save-form-btn").prop('disabled', true);

        const url = sendType === 'role' 
            ? '{{ route("admin.notification.broadcast") }}'
            : '{{ route("admin.notification.send") }}';

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (response) {
                $("#data-table_processing").hide();
                $(".save-form-btn").prop('disabled', false);
                if (response.success) {
                    $(".success_top").show().html("<p><i class='fa fa-check-circle me-2'></i>" + response.message + "</p>");
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
                let msg = 'An error occurred. Please try again.';
                if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    msg = errors.join('<br>');
                }
                $(".error_top").show().html("<p>" + msg + "</p>");
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endsection

