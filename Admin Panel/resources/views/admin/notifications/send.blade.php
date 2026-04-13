@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.notification') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Notifications</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Send</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Send Notification</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Broadcast platform updates by role or target a specific user.</p>
        </div>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden; max-width: 980px; margin: 0 auto;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Custom Notification</h4>
        </div>
        <div style="padding: 24px;">
            <div id="data-table_processing" class="text-center py-3" style="display: none; color: var(--agri-text-muted);">Sending...</div>
            <div class="error_top alert alert-danger" style="display:none;"></div>
            <div class="success_top alert alert-success" style="display:none;"></div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Send Type</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <label style="display: inline-flex; gap: 8px; align-items: center;">
                        <input type="radio" name="send-type" value="role" checked> All Users by Role
                    </label>
                    <label style="display: inline-flex; gap: 8px; align-items: center;">
                        <input type="radio" name="send-type" value="single"> Specific User
                    </label>
                </div>
            </div>

            <div id="role-section" class="mb-3">
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Role</label>
                <select id="role" class="form-agri">
                    <option value="customer">Customers (Farmers)</option>
                    <option value="vendor">Vendors</option>
                    <option value="expert">Experts</option>
                    <option value="admin">Admins</option>
                </select>
            </div>

            <div id="user-section" class="mb-3" style="display:none;">
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">User</label>
                <select id="user-id" class="form-agri"><option value="">-- Choose a User --</option></select>
            </div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Subject</label>
                <input type="text" class="form-agri" id="subject" maxlength="255" placeholder="Notification subject">
            </div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Message</label>
                <textarea class="form-agri" id="message" rows="5" maxlength="1000" placeholder="Notification message"></textarea>
                <small style="color: var(--agri-text-muted);"><span id="char-count">0</span>/1000</small>
            </div>

            <div class="mb-4">
                <label style="display: inline-flex; gap: 8px; align-items: center;">
                    <input type="checkbox" id="send_email"> Also send via email (SMTP)
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.notification') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Cancel</a>
                <button type="button" class="btn-agri btn-agri-primary save-form-btn">Send Notification</button>
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
    const roleSelect = $('#role');
    const userSelect = $('#user-id');
    let usersLoaded = false;

    function syncSendTypeUI() {
        const sendType = $('input[name="send-type"]:checked').val();

        if (sendType === 'role') {
            roleSection.show();
            roleSelect.prop('disabled', false);
            userSection.hide();
            userSelect.prop('disabled', true).val('');
            return;
        }

        roleSection.hide();
        roleSelect.prop('disabled', true);
        userSection.show();
        userSelect.prop('disabled', false);

        if (!usersLoaded) {
            loadUsers();
        }
    }

    sendTypeRadios.on('change', syncSendTypeUI);

    function loadUsers() {
        $.ajax({
            url: '{{ route("admin.notification.users.list") }}',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                userSelect.html('<option value="">-- Choose a User --</option>');
                if (response.success && response.users.length > 0) {
                    response.users.forEach(function (user) {
                        userSelect.append('<option value="' + user.id + '">' + user.name + ' (' + user.email + ')</option>');
                    });
                }
                usersLoaded = true;
            }
        });
    }

    syncSendTypeUI();

    $('#message').on('input', function () {
        $('#char-count').text($(this).val().length);
    });

    $('.save-form-btn').click(function () {
        $('.success_top').hide();
        $('.error_top').hide();

        const sendType = $('input[name="send-type"]:checked').val();
        const subject = $('#subject').val().trim();
        const message = $('#message').val().trim();
        const sendEmail = $('#send_email').is(':checked');

        if (!subject) {
            $('.error_top').show().text('Please enter a subject.');
            window.scrollTo(0, 0);
            return;
        }
        if (!message) {
            $('.error_top').show().text('Please enter a message.');
            window.scrollTo(0, 0);
            return;
        }

        const data = {
            message: message,
            send_email: sendEmail ? 1 : 0,
            send_type: sendType,
            _token: '{{ csrf_token() }}'
        };

        if (sendType === 'role') {
            data.role = $('#role').val();
            data.subject = subject;
        } else {
            const userId = $('#user-id').val();
            if (!userId) {
                $('.error_top').show().text('Please select a user.');
                window.scrollTo(0, 0);
                return;
            }
            data.user_id = userId;
            data.title = subject;
        }

        $('.save-form-btn').prop('disabled', true).text('Sending...');
        $('#data-table_processing').show();

        const url = sendType === 'role' ? '{{ route("admin.notification.broadcast") }}' : '{{ route("admin.notification.send") }}';

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (response) {
                $('#data-table_processing').hide();
                $('.save-form-btn').prop('disabled', false).text('Send Notification');
                if (response.success) {
                    $('.success_top').show().text(response.message);
                    window.scrollTo(0, 0);
                    setTimeout(function () {
                        window.location.href = '{{ route("admin.notification") }}';
                    }, 1800);
                } else {
                    $('.error_top').show().text(response.message || 'Unable to send notification.');
                }
            },
            error: function (xhr) {
                $('#data-table_processing').hide();
                $('.save-form-btn').prop('disabled', false).text('Send Notification');
                const msg = xhr.responseJSON?.message || 'An unexpected error occurred.';
                $('.error_top').show().text(msg);
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endsection
