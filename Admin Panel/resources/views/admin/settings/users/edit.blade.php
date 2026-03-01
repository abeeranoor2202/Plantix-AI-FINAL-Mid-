@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{!! route('admin.users') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.user_plural')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.user_edit')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Farmer Account</h1>
    </div>

    {{-- Summary Stats Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <a href="{{route('admin.orders.index')}}?userId={{$id}}" style="text-decoration: none;">
                <div class="card-agri" style="padding: 24px; padding-left: 32px; border-left: 6px solid #3b82f6; transition: transform 0.2s;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">{{trans('lang.dashboard_total_orders')}}</p>
                            <h2 style="font-size: 32px; font-weight: 800; color: var(--agri-text-heading); margin: 0;" id="total_orders">0</h2>
                        </div>
                        <div style="width: 56px; height: 56px; border-radius: 14px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                    </div>
                </div>

            <div class="card-agri" style="padding: 24px; padding-left: 32px; border-left: 6px solid var(--agri-primary); transition: transform 0.2s;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">{{trans('lang.wallet_Balance')}}</p>
                        <h2 style="font-size: 32px; font-weight: 800; color: var(--agri-primary); margin: 0;" id="wallet_amount">0</h2>
                    </div>
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--agri-primary-light); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 40px;">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px; z-index: 10;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <div class="error_top" style="display: none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

                <form>
                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user-edit"></i> Account Details
                        </h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.first_name')}}</label>
                                <input type="text" class="form-agri user_first_name" onkeypress="return chkAlphabets(event,'error')">
                                <div id="error" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.last_name')}}</label>
                                <input type="text" class="form-agri user_last_name" onkeypress="return chkAlphabets(event,'error1')">
                                <div id="error1" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.email')}}</label>
                                <input type="email" class="form-agri user_email" style="background-color: var(--agri-bg); cursor: not-allowed;" readonly>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">Email cannot be updated.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.user_phone')}}</label>
                                <input type="text" class="form-agri user_phone" onkeypress="return chkAlphabets2(event,'error2')">
                                <div id="error2" class="err text-danger" style="font-size: 11px; margin-top: 4px;"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                         <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">{{trans('lang.store_image')}}</h4>
                         <div style="display: flex; align-items: center; gap: 20px;">
                             <div class="user_image" style="width: 80px; height: 80px; border-radius: 12px; background: white; border: 1px solid var(--agri-border); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                 {{-- Image injected via JS --}}
                             </div>
                             <div style="flex: 1;">
                                 <input type="file" onChange="handleFileSelect(event)" class="form-control" style="font-size: 13px;">
                             </div>
                         </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Update Changes
                        </button>
                        <a href="{!! route('admin.users') !!}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
             <div class="card-agri" style="padding: 24px; background: #fffbeb; border-top: 4px solid #f59e0b;">
                 <h4 style="font-size: 16px; font-weight: 700; color: #92400e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                     <i class="fas fa-shield-alt"></i> Security & Status
                 </h4>
                 
                 <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
                     <div>
                         <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">Account Status</span>
                         <p style="font-size: 12px; color: #b45309; margin: 0;">Toggle system access.</p>
                     </div>
                     <div class="form-check form-switch" style="padding: 0; margin: 0;">
                         <input type="checkbox" class="user_active" id="user_active" style="width: 44px; height: 22px; cursor: pointer; accent-color: var(--agri-primary);">
                     </div>
                 </div>

                 <div style="margin-bottom: 24px; padding-top: 24px; border-top: 1px solid rgba(146, 64, 14, 0.1);">
                     <div class="form-check mb-3">
                         <input type="checkbox" id="reset_password" class="form-check-input">
                         <label class="form-check-label" for="reset_password" style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">{{trans('lang.reset_password')}}</label>
                     </div>
                     <p style="font-size: 12px; color: #b45309; line-height: 1.5; margin-bottom: 16px;">{{ trans("lang.note_reset_password_email") }}</p>
                     <button type="button" class="btn-agri" id="send_mail" style="width: 100%; height: 42px; background: #f59e0b; color: white; border: none; font-size: 14px; font-weight: 600;">
                         <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> {{trans('lang.send_mail')}}
                     </button>
                 </div>
             </div>
        </div>
    </div>
</div>

<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }
    .card-agri:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('scripts')
<script>
    var id             = "<?php echo $id; ?>";
    var currentCurrency  = @json($currencySymbol);
    var currencyAtRight  = @json($currencyAtRight);
    var decimal_degits   = @json($decimalDigits);
    var photoFile        = null;
    var currentImageUrl  = '';
    var placeholderImage = @json($placeholderImage);

    // ── User data injected server-side ────────────────────────────────────
    @if($user)
    var userData = @json([
        'first_name'          => $user->first_name,
        'last_name'           => $user->last_name,
        'email'               => $user->email,
        'phone_number'        => $user->phone_number,
        'wallet_amount'       => $user->wallet_amount,
        'profile_picture_url' => $user->profile_photo,
        'is_active'           => (bool)$user->active,
    ]);
    var totalOrders = @json($totalOrders);
    @else
    var userData = null;
    var totalOrders = 0;
    @endif

    $("#send_mail").click(function() {
        if ($("#reset_password").is(":checked")) {
            var email = $(".user_email").val();
            $.ajax({
                url: '{{ route("api.admin.users.send-password-reset", ":id") }}'.replace(':id', id),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('{{trans("lang.mail_sent")}}');
                },
                error: function(xhr) {
                    alert('{{trans("lang.mail_send_error")}}');
                }
            });
        } else {
            alert('{{trans("lang.mail_send_error")}}');
        }
    });

    $(document).ready(function() {
        if (userData) {
            var user = userData;
            $(".user_first_name").val(user.first_name || '');
            $(".user_last_name").val(user.last_name || '');
            $(".user_email").val(user.email || '');
            $(".user_phone").val(user.phone_number || '');

            if (user.profile_picture_url && user.profile_picture_url != '') {
                currentImageUrl = user.profile_picture_url;
                $(".user_image").html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + user.profile_picture_url + '" alt="image">');
            } else {
                $(".user_image").html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + placeholderImage + '" alt="image">');
            }

            if (user.is_active) $(".user_active").prop('checked', true);

            var wallet_amount = user.wallet_amount || 0;
            if (currencyAtRight) {
                wallet_amount = parseFloat(wallet_amount).toFixed(decimal_degits) + currentCurrency;
            } else {
                wallet_amount = currentCurrency + parseFloat(wallet_amount).toFixed(decimal_degits);
            }
            $("#wallet_amount").text(wallet_amount);
            $("#total_orders").text(totalOrders);
        }

        $(".edit-form-btn").click(function() {
            var userFirstName = $(".user_first_name").val();
            var userLastName = $(".user_last_name").val();
            var userPhone = $(".user_phone").val();
            var active = $(".user_active").is(":checked");

            if (userFirstName == '') {
                $(".error_top").show().html("<p>{{trans('lang.user_firstname_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userLastName == '') {
                $(".error_top").show().html("<p>{{trans('lang.user_lastname_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();

                // Prepare form data
                var formData = new FormData();
                formData.append('first_name', userFirstName);
                formData.append('last_name', userLastName);
                formData.append('phone_number', userPhone);
                formData.append('is_active', active ? 1 : 0);
                if (photoFile) {
                    formData.append('profile_picture', photoFile);
                }

                // Update user via API
                $.ajax({
                    url: '{{ route("api.admin.users.update", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function(response) {
                        jQuery("#data-table_processing").hide();
                        window.location.href = '{{ route("admin.users")}}';
                    },
                    error: function(xhr) {
                        jQuery("#data-table_processing").hide();
                        var message = 'Error updating user';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $(".error_top").show().html("<p>" + message + "</p>");
                    }
                });
            }
        });
    });

    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        if (!f) return;

        var reader = new FileReader();
        photoFile = f;

        reader.onload = (function(theFile) {
            return function(e) {
                var filePayload = e.target.result;
                $(".user_image").html('<img class="rounded" style="width:100%; height:100%; object-fit:cover;" src="' + filePayload + '" alt="image">');
            };
        })(f);
        reader.readAsDataURL(f);
    }

    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Letters only please";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)) {
            document.getElementById(msg).innerHTML = "Numbers only please";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            cookie = cookie.trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return '';
