@extends('layouts.app')



@section('content')

    <div class="container-fluid" style="padding-top: 24px; padding-bottom: 48px;">

        {{-- Header Section --}}
        <div style="margin-bottom: 32px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.app_setting_global')}}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Global Application Controls</h1>
            <p style="color: var(--agri-text-muted); margin-top: 4px;">Manage core identity, configurations, and system-wide integrations.</p>
        </div>

        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="spinner-border text-primary mr-2" role="status"></div>
            {{trans('lang.processing')}}
        </div>

        <div class="error_top" style="display:none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

        <div class="row g-4">
            {{-- App Identity & SEO --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">App Identity & SEO</h4>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_app_name')}}</label>
                        <input type="text" class="form-agri application_name" placeholder="Enter App Name">
                        <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.app_setting_app_name_help") }}</div>
                    </div>

                    <div style="margin-bottom: 0;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_meta_title')}}</label>
                        <input type="text" class="form-agri meta_title" placeholder="Enter Meta Title">
                        <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.app_setting_meta_title_help") }}</div>
                    </div>
                </div>
            </div>

            {{-- Visual Assets --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: var(--agri-secondary-light); color: var(--agri-secondary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Visual Assets</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.upload_app_logo')}}</label>
                            <input type="file" class="form-control-sm" style="font-size: 11px;" onChange="handleFileSelect(event)">
                            <div class="logo_img_thumb mt-2" style="border: 1px dashed var(--agri-border); padding: 5px; border-radius: 8px; min-height: 60px; display: flex; align-items: center; justify-content: center;"></div>
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.upload_favicon')}}</label>
                            <input type="file" class="form-control-sm" style="font-size: 11px;" onChange="handleFileSelectFavicon(event)">
                            <div class="favicon_img_thumb mt-2" style="border: 1px dashed var(--agri-border); padding: 5px; border-radius: 8px; min-height: 60px; display: flex; align-items: center; justify-content: center;"></div>
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.menu_placeholder_image')}}</label>
                            <input type="file" class="form-control-sm" style="font-size: 11px;" onChange="handleFileSelectplaceholder(event)">
                            <div class="placeholder_img_thumb mt-2" style="border: 1px dashed var(--agri-border); padding: 5px; border-radius: 8px; min-height: 60px; display: flex; align-items: center; justify-content: center;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Color Palette & Branding --}}
            <div class="col-12">
                <div class="card-agri">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #EEF2FF; color: #4F46E5; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Global Branding Palette</h4>
                    </div>

                    <div class="row g-4">
                        @php
                            $color_settings = [
                                ['id' => 'admin_color', 'label' => trans('lang.admin_panel_color_settings')],
                                ['id' => 'store_color', 'label' => trans('lang.store_panel_color_settings')],
                                ['id' => 'website_color', 'label' => trans('lang.website_color_settings')],
                                ['id' => 'customer_app_color', 'label' => trans('lang.app_customer_color_settings')],
                                ['id' => 'driver_app_color', 'label' => trans('lang.app_driver_color_settings')],
                                ['id' => 'restaurant_app_color', 'label' => trans('lang.app_store_color_settings')],
                            ];
                        @endphp
                        @foreach($color_settings as $color)
                            <div class="col-md-2 col-sm-4">
                                <label style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); margin-bottom: 8px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $color['label'] }}</label>
                                <div style="position: relative; height: 44px;">
                                    <input type="color" name="{{ $color['id'] }}" id="{{ $color['id'] }}" style="position: absolute; width: 100%; height: 100%; border: none; padding: 0; background: none; cursor: pointer; border-radius: 10px;">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #F0FDF4; color: #166534; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Support & Contact</h4>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.contact_us_address')}}</label>
                        <textarea class="form-agri contact_us_address" rows="3" placeholder="Enter physical address"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.contact_us_email')}}</label>
                            <input type="email" class="form-agri contact_us_email" placeholder="support@domain.com">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.contact_us_phone')}}</label>
                            <input type="text" class="form-agri contact_us_phone" placeholder="+1234567890">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Email Delivery (SMTP) --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #ECFDF5; color: #065F46; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Email Delivery (SMTP)</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">From Name</label>
                            <input type="text" class="form-agri from_name">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">SMTP Host</label>
                            <input type="text" class="form-agri host">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">SMTP Port</label>
                            <input type="text" class="form-agri port">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Username</label>
                            <input type="text" class="form-agri user_name">
                        </div>
                        <div class="col-12">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Password</label>
                            <input type="password" class="form-agri password">
                        </div>
                    </div>
                </div>
            </div>

        {{-- Sticky Footer Actions --}}
        <div style="position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); border-top: 1px solid var(--agri-border); padding: 16px 24px; display: flex; justify-content: flex-end; gap: 16px; z-index: 1000;">
            <a href="{{url('/dashboard')}}" class="btn-agri btn-agri-outline" style="height: 44px; display: flex; align-items: center; text-decoration: none;">
                <i class="fas fa-undo" style="margin-right: 8px;"></i> Discard Changes
            </a>
            <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="height: 44px; min-width: 160px;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> Broadcast Updates
            </button>
        </div>
    </div>

    <style>
    </style>



@endsection



@section('scripts')

    <script>

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

        var photo = "";
        var placeholderphoto = '';
        var favicon="";
        var appLogoImagePath = '';
        var appFavIconImagePath = '';
        var placeholderImagePath = '';
        var logoFileName = '';
        var favIconFileName = '';
        var serviceJsonFile = '';
        var placeholderFileName='';

        $(document).ready(function () {

            // ── Settings injected server-side – no AJAX needed ──────────
            var globalSettings = @json($settings);

            // Basic settings
            $(".application_name").val(globalSettings.application_name || '');
            $(".meta_title").val(globalSettings.meta_title || '');
            $("#website_color").val(globalSettings.website_color || '#2EC7D9');
            $("#admin_color").val(globalSettings.admin_panel_color || '#2EC7D9');
            $("#store_color").val(globalSettings.store_panel_color || '#2EC7D9');
            $("#customer_app_color").val(globalSettings.app_customer_color || '#2EC7D9');
            $("#driver_app_color").val(globalSettings.app_driver_color || '#2EC7D9');
            $("#restaurant_app_color").val(globalSettings.app_restaurant_color || '#2EC7D9');

            // App Images
            if (globalSettings.app_logo) {
                photo = globalSettings.app_logo;
                appLogoImagePath = globalSettings.app_logo;
                $(".logo_img_thumb").html('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            }
            if (globalSettings.favicon) {
                favicon = globalSettings.favicon;
                appFavIconImagePath = globalSettings.favicon;
                $(".favicon_img_thumb").html('<img class="rounded" style="width:50px" src="' + favicon + '" alt="image">');
            }

            // Contact Info
            $('.contact_us_address').val(globalSettings.contact_us_address || '');
            $('.contact_us_email').val(globalSettings.contact_us_email || '');
            $('.contact_us_phone').val(globalSettings.contact_us_phone || '');

            // Handle file selections
            window.handleFileSelect = function(event) {
                var file = event.target.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    photo = e.target.result;
                    logoFileName = file.name;
                    $(".logo_img_thumb").html('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                };
                reader.readAsDataURL(file);
            };

            window.handleFileSelectFavicon = function(event) {
                var file = event.target.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    favicon = e.target.result;
                    favIconFileName = file.name;
                    $(".favicon_img_thumb").html('<img class="rounded" style="width:50px" src="' + favicon + '" alt="image">');
                };
                reader.readAsDataURL(file);
            };

            window.handleFileSelectplaceholder = function(event) {
                var file = event.target.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    placeholderphoto = e.target.result;
                    placeholderFileName = file.name;
                    $(".placeholder_img_thumb").html('<img class="rounded" style="width:50px" src="' + placeholderphoto + '" alt="image">');
                };
                reader.readAsDataURL(file);
            };

            // Handle story time toggle
            $('#restaurant_can_upload_story').on('change', function() {
                if($(this).is(':checked')) {
                    $("#story_upload_time_div").show();
                } else {
                    $("#story_upload_time_div").hide();
                }
            });

            // Handle story time toggle
            $('#restaurant_can_upload_story').on('change', function() {
                if($(this).is(':checked')) {
                    $("#story_upload_time_div").show();
                } else {
                    $("#story_upload_time_div").hide();
                }
            });

            // Save all settings
            $('.save-form-btn').click(function() {
                jQuery("#data-table_processing").show();
                $(".error_top").hide();

                var formData = {
                    application_name: $(".application_name").val(),
                    meta_title: $(".meta_title").val(),
                    website_color: $("#website_color").val(),
                    admin_panel_color: $("#admin_color").val(),
                    store_panel_color: $("#store_color").val(),
                    app_customer_color: $("#customer_app_color").val(),
                    app_driver_color: $("#driver_app_color").val(),
                    app_restaurant_color: $("#restaurant_app_color").val(),
                    contact_us_address: $('.contact_us_address').val(),
                    contact_us_email: $('.contact_us_email').val(),
                    contact_us_phone: $('.contact_us_phone').val(),
                    from_name: $('.from_name').val(),
                    host: $('.host').val(),
                    port: $('.port').val(),
                    user_name: $('.user_name').val(),
                    password: $('.password').val()
                };

                $.ajax({
                    url: '/api/admin/settings/update',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(result) {
                        jQuery("#data-table_processing").hide();
                        alert('Settings updated successfully');
                        location.reload();
                    },
                    error: function(xhr) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("<p>Error updating settings. Please try again.</p>");
                    }
                });
            });
        });

    </script>

@endsection

