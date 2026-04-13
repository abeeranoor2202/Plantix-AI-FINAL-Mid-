@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{url('email-templates')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.email_templates')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">
                @if($id=='') {{trans('lang.create_email_templates')}} @else {{trans('lang.edit_email_templates')}} @endif
            </span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
            @if($id=='') Create Template @else Edit Template @endif
        </h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card-agri" style="padding: 40px;">

                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px; z-index: 10;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <div class="error_top" style="display: none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>
                <div class="success_top" style="display: none; background: #d1fae5; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

                <form>
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-envelope-open-text"></i> Message Configuration
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri" id="name" placeholder="Booking confirmation">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Email Type <span class="text-danger">*</span></label>
                                <select class="form-agri" id="email_type">
                                    <option value="system">System</option>
                                    <option value="appointment">Appointment</option>
                                    <option value="forum">Forum</option>
                                    <option value="order">Order</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.type')}}</label>
                                <input type="text" class="form-agri" id="type" readonly style="background-color: var(--agri-bg); cursor: not-allowed; border: 1px dashed var(--agri-border);">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.subject')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri" id="subject" placeholder="Enter email subject line...">
                            </div>

                            <div class="col-12 mt-4">
                                <label class="agri-label" style="margin-bottom: 12px;">Body <span class="text-danger">*</span></label>
                                <div style="border: 1px solid var(--agri-border); border-radius: 16px; overflow: hidden;">
                                    <textarea class="form-control" name="body" id="body"></textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="agri-label">Variables</label>
                                <textarea class="form-agri" id="variables" rows="3" placeholder="List placeholder variables separated by commas"></textarea>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 32px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 4px;">{{trans('lang.is_send_to_admin')}}</h5>
                            <p style="font-size: 13px; color: #b45309; margin: 0;">Also CC this notification to the system administrator.</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" id="is_send_to_admin" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> {{trans('lang.save')}}
                        </button>
                        <a href="{{url('email-templates')}}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
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
</style>

        @endsection

        @section('scripts')

            <script>

                var requestId = "<?php echo $id; ?>";

                function getCookie(name) {
                    const nameEQ = name + "=";
                    const cookies = document.cookie.split(';');
                    for(let i = 0; i < cookies.length; i++) {
                        const cookie = cookies[i].trim();
                        if(cookie.indexOf(nameEQ) === 0) {
                            return decodeURIComponent(cookie.substring(nameEQ.length));
                        }
                    }
                    return null;
                }

                function slugify(value) {
                    return value
                        .toString()
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '_')
                        .replace(/-+/g, '_');
                }

                $(document).on('input', '#name', function () {
                    if (!requestId) {
                        $('#type').val(slugify($(this).val()));
                    }
                });

                $('#body').summernote({
                    height: 400,
                    width: 1000,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['forecolor', ['forecolor']],
                        ['backcolor', ['backcolor']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['height', ['height']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                    ]
                });

                $(document).ready(function () {
                    @if($template)
                    // ── Template data injected server-side ────────────────
                    $("#name").val(@json($template->name));
                    $("#subject").val(@json($template->subject));
                    $('#body').summernote("code", @json($template->body));
                    $("#email_type").val(@json($template->email_type ?? 'system'));
                    $("#variables").val(@json(is_array($template->variables) ? implode(', ', $template->variables) : ($template->variables ?? '')));

                    @if($template->is_send_to_admin)
                    $("#is_send_to_admin").prop('checked', true);
                    @endif

                    @php
                        $typeLabels = [
                            'new_order_placed'      => trans('lang.new_order_placed'),
                            'new_vendor_signup'     => trans('lang.new_vendor_signup'),
                            'payout_request'        => trans('lang.payout_request'),
                            'payout_request_status' => trans('lang.payout_request_status'),
                            'wallet_topup'          => trans('lang.wallet_topup'),
                        ];
                    @endphp
                    $('#type').val(@json($typeLabels[$template->type] ?? $template->type));
                    @endif

                    if (!requestId) {
                        $('#type').val(slugify($('#name').val()));
                    }
                });

                $(".edit-form-btn").click(function () {

                    $(".success_top").hide();
                    $(".error_top").hide();
                    var name = $("#name").val();
                    var subject = $("#subject").val();
                    var body = $('#body').summernote('code');
                    var type = $('#type').val();
                    var emailType = $("#email_type").val();
                    var variables = $("#variables").val();
                    var isSendToAdmin = $("#is_send_to_admin").is(":checked");

                    if (name == "") {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>Please enter a template name.</p>");
                        window.scrollTo(0, 0);
                        return false;
                    } else if (subject == "") {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{trans('lang.please_enter_subject')}}</p>");
                        window.scrollTo(0, 0);
                        return false;
                    } else if (body == "") {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{trans('lang.please_enter_message')}}</p>");
                        window.scrollTo(0, 0);
                        return false;
                    } else {
                        jQuery("#data-table_processing").show();
                        
                        var method = requestId == '' ? 'POST' : 'PUT';
                        var url = requestId == '' ? '/api/admin/email-templates' : '/api/admin/email-templates/' + requestId;

                        $.ajax({
                            url: url,
                            method: method,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                'name': name,
                                'subject': subject,
                                'body': body,
                                'type': type,
                                'email_type': emailType,
                                'variables': variables,
                                'is_send_to_admin': isSendToAdmin
                            },
                            success: function (result) {
                                jQuery("#data-table_processing").hide();
                                $(".success_top").show();
                                $(".success_top").html("");
                                if (requestId == '') {
                                    $(".success_top").append("<p>{{trans('lang.email_templates_created_success')}}</p>");
                                } else {
                                    $(".success_top").append("<p>{{trans('lang.email_templates_updated_success')}}</p>");
                                }
                                window.scrollTo(0, 0);
                                setTimeout(function() {
                                    window.location.href = '{{ route("admin.email-templates.index")}}';
                                }, 1000);
                            },
                            error: function (xhr) {
                                jQuery("#data-table_processing").hide();
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append("<p>" + (xhr.responseJSON?.message || "{{trans('lang.please_try_again')}}") + "</p>");
                            }
                        });
                    }

                });

            </script>

@endsection
