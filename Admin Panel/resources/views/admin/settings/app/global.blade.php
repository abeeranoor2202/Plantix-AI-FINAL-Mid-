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

            {{-- Homepage Theme Selection --}}
            <div class="col-12">
                <div class="card-agri">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #FFF7ED; color: #C2410C; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Homepage Layout Strategy</h4>
                    </div>

                    <div style="display: flex; gap: 24px;">
                        <label class="theme-card" for="app_homepage_theme_1" style="flex: 1; cursor: pointer; position: relative;">
                            <input type="radio" name="app_homepage_theme" id="app_homepage_theme_1" value="theme_1" style="position: absolute; opacity: 0;">
                            <div class="theme-preview" style="border: 2px solid var(--agri-border); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                                <img src="{{url('images/app_homepage_theme_1.png')}}" style="width: 100%; height: 200px; object-fit: cover;">
                                <div style="padding: 12px; text-align: center; font-weight: 700; background: var(--agri-bg);">Modern Minimalist</div>
                            </div>
                        </label>
                        <label class="theme-card" for="app_homepage_theme_2" style="flex: 1; cursor: pointer; position: relative;">
                            <input type="radio" name="app_homepage_theme" id="app_homepage_theme_2" value="theme_2" style="position: absolute; opacity: 0;">
                            <div class="theme-preview" style="border: 2px solid var(--agri-border); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                                <img src="{{url('images/app_homepage_theme_2.png')}}" style="width: 100%; height: 200px; object-fit: cover;">
                                <div style="padding: 12px; text-align: center; font-weight: 700; background: var(--agri-bg);">Feature Centric</div>
                            </div>
                        </label>
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

            {{-- Map & Location Logic --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #FEF2F2; color: #991B1B; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Geospatial Intelligence</h4>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.google_map_api_key')}}</label>
                        <input type="password" class="form-agri" name="map_key" id="map_key" placeholder="••••••••••••••••">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">App Map Provider</label>
                            <select id="selectedMapType" class="form-agri">
                                <option value="google">{{trans("lang.google_maps")}}</option>
                                <option value="osm">{{trans("lang.open_street_map")}}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Navigation Preference</label>
                            <select id="map_type" class="form-agri">
                                <option value="">{{trans("lang.select_type")}}</option>
                                <option value="google">{{trans("lang.google_map")}}</option>
                                <option value="googleGo">{{trans("lang.google_go_map")}}</option>
                                <option value="waze">{{trans("lang.waze_map")}}</option>
                                <option value="mapswithme">{{trans("lang.mapswithme_map")}}</option>
                                <option value="yandexNavi">{{trans("lang.vandexnavi_map")}}</option>
                                <option value="yandexMaps">{{trans("lang.vandex_map")}}</option>
                                <option value="inappmap">{{trans("lang.inapp_map")}}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.driver_location_update')}} (Radius)</label>
                            <input name="radius" id="driver_location_update" class="form-agri" placeholder="e.g. 50">
                        </div>
                        <div class="col-12">
                            <div class="form-check" style="padding-left: 0; display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="single_order_receive" style="width: 18px; height: 18px; accent-color: var(--agri-primary);">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin: 0;" for="single_order_receive">{{ trans('lang.single_order_receive')}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial & Growth Engine --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #F0F9FF; color: #075985; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Financials & Growth</h4>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{ trans('lang.minimum_deposit_amount')}}</label>
                            <div style="position: relative;">
                                <input type="number" class="form-agri minimum_deposit_amount" style="padding-right: 40px;">
                                <span class="currentCurrency" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-primary);"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{ trans('lang.minimum_withdrawal_amount')}}</label>
                            <div style="position: relative;">
                                <input type="number" class="form-agri minimum_withdrawal_amount" style="padding-right: 40px;">
                                <span class="currentCurrency" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-primary);"></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{ trans('lang.referral_amount')}}</label>
                            <div style="position: relative;">
                                <input type="number" class="form-agri referral_amount" style="padding-right: 40px;">
                                <span class="currentCurrency" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--agri-primary);"></span>
                            </div>
                            <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 6px;">{{ trans("lang.referral_amount_help") }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operational Rules --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #FAF5FF; color: #6B21A8; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Operational Rules</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check" style="padding-left: 0; display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <input type="checkbox" id="restaurant_can_upload_story" style="width: 18px; height: 18px; accent-color: var(--agri-primary);">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin: 0;" for="restaurant_can_upload_story">{{ trans('lang.store_can_upload_story')}}</label>
                            </div>
                            <div class="form-check" style="padding-left: 0; display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="auto_approve_restaurant" style="width: 18px; height: 18px; accent-color: var(--agri-primary);">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin: 0;" for="auto_approve_restaurant">{{ trans('lang.auto_approve_store')}}</label>
                            </div>
                        </div>
                        <div class="col-12" id="story_upload_time_div" style="display:none;">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.story_upload_time')}} (Seconds)</label>
                            <input type="number" class="form-agri" id="story_upload_time" value="30" min="0">
                            <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 6px;">{{ trans("lang.story_upload_time_help") }}</div>
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

            {{-- Push Notifications --}}
            <div class="col-lg-6">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #FFF1F2; color: #BE123C; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Push Communications</h4>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">FCM Sender ID</label>
                        <input type="text" class="form-agri" id="sender_id" placeholder="Enter Sender ID">
                        <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{{ trans("lang.notification_sender_id_help") }}</div>
                    </div>

                    <div style="margin-bottom: 0;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Service Account JSON</label>
                        <div style="background: var(--agri-bg); border: 2px dashed var(--agri-border); padding: 20px; border-radius: 12px; text-align: center;">
                            <input type="file" id="json_upload" style="display: none;" onChange="handleUploadJsonFile(event)">
                            <label for="json_upload" class="btn-agri btn-agri-outline" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fas fa-upload"></i> Choose JSON File
                            </label>
                            <div id="uploding_json_file" class="mt-2 small text-primary"></div>
                            <div id="uploded_json_file" class="mt-2"></div>
                            <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 10px;">{{ trans("lang.notification_json_file_help") }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Versioning & Distribution --}}
            <div class="col-12">
                <div class="card-agri">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                        <div style="width: 40px; height: 40px; background: #F8FAFC; color: #475569; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Versioning & App Stores</h4>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">App Build Version</label>
                            <input type="text" class="form-agri app_version">
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Web Core Version</label>
                            <input type="text" class="form-agri" id="web_version">
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">App Store URL</label>
                            <input type="text" class="form-agri" id="app_store_link">
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Play Store URL</label>
                            <input type="text" class="form-agri" id="play_store_link">
                        </div>
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
        .theme-card input:checked + .theme-preview {
            border-color: var(--agri-primary) !important;
            box-shadow: 0 0 0 4px var(--agri-primary-light);
            transform: translateY(-4px);
        }
        .theme-card:hover .theme-preview {
            border-color: var(--agri-primary);
        }
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

            jQuery("#data-table_processing").show();

            // Fetch all global settings via AJAX
            $.ajax({
                url: '/api/admin/settings/global',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        var globalSettings = response.data;

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
                        if(globalSettings.app_logo) {
                            photo = globalSettings.app_logo;
                            appLogoImagePath = globalSettings.app_logo;
                            $(".logo_img_thumb").html('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                        }

                        if(globalSettings.favicon) {
                            favicon = globalSettings.favicon;
                            appFavIconImagePath = globalSettings.favicon;
                            $(".favicon_img_thumb").html('<img class="rounded" style="width:50px" src="' + favicon + '" alt="image">');
                        }

                        // Contact Info
                        $('.contact_us_address').val(globalSettings.contact_us_address || '');
                        $('.contact_us_email').val(globalSettings.contact_us_email || '');
                        $('.contact_us_phone').val(globalSettings.contact_us_phone || '');

                        // Map Settings
                        $('#map_key').val(globalSettings.map_key || '');
                        $('#selectedMapType').val(globalSettings.selected_map_type || 'google');
                        $('#map_type').val(globalSettings.map_type || 'google');

                        // Version Info
                        $('.app_version').val(globalSettings.app_version || '');
                        $('#web_version').val(globalSettings.web_version || '');
                        $('#app_store_link').val(globalSettings.app_store_link || '');
                        $('#play_store_link').val(globalSettings.play_store_link || '');

                        // Theme Selection
                        if(globalSettings.app_homepage_theme) {
                            $('input[name="app_homepage_theme"][value="' + globalSettings.app_homepage_theme + '"]').prop('checked', true);
                        }

                        // Restaurant Settings
                        if(globalSettings.auto_approve_restaurant) {
                            $("#auto_approve_restaurant").prop('checked', true);
                        }

                        // Story Settings
                        if(globalSettings.story_enabled) {
                            $("#restaurant_can_upload_story").prop('checked', true);
                            $("#story_upload_time_div").show();
                        }
                        $("#story_upload_time").val(globalSettings.story_upload_time || '');

                        // Placeholder Image
                        if(globalSettings.placeholder_image) {
                            placeholderphoto = globalSettings.placeholder_image;
                            placeholderImagePath = globalSettings.placeholder_image;
                            $(".placeholder_img_thumb").html('<img class="rounded" style="width:50px" src="' + placeholderphoto + '" alt="image">');
                        }

                        // Notification Settings
                        $('#sender_id').val(globalSettings.sender_id || '');
                        if(globalSettings.service_json) {
                            $('#uploded_json_file').html("<a href='" + globalSettings.service_json + "' class='btn-link pl-3' target='_blank'>See Uploaded File</a>");
                            serviceJsonFile = globalSettings.service_json;
                        }

                        $(".currentCurrency").text(globalSettings.currency_symbol || '$');
                    }
                    jQuery("#data-table_processing").hide();
                },
                error: function(xhr) {
                    jQuery("#data-table_processing").hide();
                    console.log('Error loading global settings', xhr);
                }
            });

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

            // Handle theme modal
            $(document).on('click', '.theme-card', function() {
                var modal = $('#themeModal');
                var selectedTheme = $(this).find('input[name="app_homepage_theme"]').val();
                
                if(selectedTheme === 'theme_1') {
                    modal.find('#themeImage').attr('src',$('#theme_1_url').val() || '');
                } else if(selectedTheme === 'theme_2') {
                    modal.find('#themeImage').attr('src',$('#theme_2_url').val() || '');
                }
                modal.modal('show');
            });

            // Save all settings
            $('.edit-form-btn').click(function() {
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
                    map_key: $('#map_key').val(),
                    selected_map_type: $('#selectedMapType').val(),
                    map_type: $('#map_type').val(),
                    app_version: $('.app_version').val(),
                    web_version: $('#web_version').val(),
                    app_store_link: $('#app_store_link').val(),
                    play_store_link: $('#play_store_link').val(),
                    app_homepage_theme: $('input[name="app_homepage_theme"]:checked').val(),
                    auto_approve_restaurant: $("#auto_approve_restaurant").is(':checked') ? 1 : 0,
                    story_enabled: $("#restaurant_can_upload_story").is(':checked') ? 1 : 0,
                    story_upload_time: $("#story_upload_time").val(),
                    sender_id: $('#sender_id').val()
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

        // Handle JSON file upload
        $('#addFile').on('change', function(e) {
            if (e.target.files.length > 0) {
                var f = e.target.files[0];
                var reader = new FileReader();
                
                reader.onload = (function(file) {
                    return function(e) {
                        var formData = new FormData();
                        formData.append('file', file);

                        $.ajax({
                            url: '/api/admin/settings/upload-json',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if(response.success) {
                                    serviceJsonFile = response.data.file_url;
                                    jQuery("#uploding_json_file").text("Upload is completed");
                                    $('#uploded_json_file').html("<a href='" + serviceJsonFile + "' class='btn-link pl-3' target='_blank'>See Uploaded File</a>");
                                    setTimeout(function(){
                                        jQuery("#uploding_json_file").hide();
                                    }, 3000);
                                }
                            },
                            error: function(xhr) {
                                jQuery("#uploding_json_file").text("Upload failed");
                            }
                        });
                    };
                })(f);
                
                reader.readAsDataURL(f);
            }
        });

    </script>

@endsection


                } catch (error) {



                }



            });



        });

        $(".save-form-btn").click(function () {



            var website_color = $("#website_color").val();

            var admin_color = $("#admin_color").val();

            var customer_app_color = $("#customer_app_color").val();
            
            var driver_app_color = $("#driver_app_color").val();
            
            var restaurant_app_color = $("#restaurant_app_color").val();

            var googleApiKey = $("#map_key").val();

            var store_color = $("#store_color").val();

            var contact_us_address = $('.contact_us_address').val();

            var contact_us_email = $('.contact_us_email').val();

            var contact_us_phone = $('.contact_us_phone').val();

            var app_version = $('.app_version').val();

            var web_version = $('#web_version').val();

            var app_store_link = $('#app_store_link').val();

            var play_store_link = $('#play_store_link').val();

            var auto_approve_restaurant = $("#auto_approve_restaurant").is(":checked");

            var restaurant_can_upload_story = $("#restaurant_can_upload_story").is(":checked");

            var story_upload_time = parseInt($('#story_upload_time').val());

            var minimumDepositToRideAccept = $(".minimum_deposit_amount").val();

            var minimumAmountToWithdrawal = $(".minimum_withdrawal_amount").val();

            var referralAmount = $(".referral_amount").val();

            var app_homepage_theme = $(".form-group input[name='app_homepage_theme']:checked").val();

            var senderId = $("#sender_id").val();



            var fromName = $('.from_name').val();

            var host = $('.host').val();

            var port = $('.port').val();

            var userName = $('.user_name').val();

            var password = $('.password').val();



            if (admin_color != null) {

                setCookie('admin_panel_color', admin_color, 365);

            }



            var applicationName = $(".application_name").val();

            var meta_title = $(".meta_title").val();

            var selectedMapType = $("#selectedMapType").val();

            var map_type = $('#map_type').val();

            var driver_location_update = $('#driver_location_update').val();

            var single_order_receive = $("#single_order_receive").is(":checked");



            if (applicationName == '') {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.enter_app_name_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (minimumDepositToRideAccept == '') {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.enter_minimum_deposit_amount_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (minimumAmountToWithdrawal == '') {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.enter_minimum_withdrawal_amount_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (referralAmount == '') {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.enter_referral_amount_error')}}</p>");

                window.scrollTo(0, 0);

                window.scrollTo(0, 0);

            } else if (host == "") {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.host_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (port == "") {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.port_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (userName == "") {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.username_error')}}</p>");

                window.scrollTo(0, 0);

            } else if (password == "") {

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.password_error')}}</p>");

                window.scrollTo(0, 0);

            }else if(senderId == ''){

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.notification_sender_id_error')}}</p>");

                window.scrollTo(0, 0);

            }else if(serviceJsonFile == ''){

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.notification_service_json_error')}}</p>");

                window.scrollTo(0, 0);

            } else {



                jQuery("#data-table_processing").show();

                var formData = {
                    application_name: applicationName,
                    meta_title: meta_title,
                    website_color: website_color,
                    admin_panel_color: admin_color,
                    store_panel_color: store_color,
                    app_customer_color: customer_app_color,
                    app_driver_color: driver_app_color,
                    app_restaurant_color: restaurant_app_color,
                    contact_us_address: contact_us_address,
                    contact_us_email: contact_us_email,
                    contact_us_phone: contact_us_phone,
                    map_key: googleApiKey,
                    selected_map_type: selectedMapType,
                    map_type: map_type,
                    app_version: app_version,
                    web_version: web_version,
                    app_store_link: app_store_link,
                    play_store_link: play_store_link,
                    app_homepage_theme: app_homepage_theme,
                    auto_approve_restaurant: auto_approve_restaurant ? 1 : 0,
                    story_enabled: restaurant_can_upload_story ? 1 : 0,
                    story_upload_time: story_upload_time,
                    sender_id: senderId,
                    from_name: fromName,
                    host: host,
                    port: port,
                    user_name: userName,
                    password: password
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
                        window.location.href = '{{ url("settings/app/globals")}}';
                    },
                    error: function(xhr) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>Error updating settings. Please try again.</p>");
                        window.scrollTo(0, 0);
                    }
                });



            }



        });



        $("#restaurant_can_upload_story").click(function () {

            if ($(this).is(':checked')) {

                $("#story_upload_time_div").show();

            } else {

                $("#story_upload_time_div").hide();

            }

        });



        $(".form-group input[name='app_homepage_theme']").click(function () {

            if ($(this).is(':checked')) {

                var modal = $('#themeModal');

                if ($(this).val() == "theme_1") {

                    modal.find('#themeImage').attr('src',theme_1_url);

                } else {

                    modal.find('#themeImage').attr('src',theme_2_url);

                }

                $('#themeModal').modal('show');

            }

        });



        $('#themeModal').on('hide.bs.modal', function (event) {

            var modal = $(this);

            modal.find('#themeImage').attr('src','');

        });

        // File uploads are now handled via AJAX in the form submission

        function storeImageData() {

            return Promise.resolve({photo: photo, favicon: favicon, placeholderphoto: placeholderphoto});

        }



            } catch (error) {

                console.log("ERR ===", error);

            }

            return newPhoto;

        }



        function handleFileSelect(evt) {



            var f = evt.target.files[0];

            var reader = new FileReader();



            reader.onload = (function (theFile) {

                return function (e) {



                    var filePayload = e.target.result;

                    var val = f.name;

                    var ext = val.split('.')[1];

                    var docName = val.split('fakepath')[1];

                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')



                    var timestamp = Number(new Date());

                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                    photo = filePayload;

                    logoFileName=filename;

                    $(".logo_img_thumb").empty();

                    $(".logo_img_thumb").append('<span class="image-item"><img class="rounded" style="width:50px" src="' + filePayload + '" alt="image"></span>');





                };

            })(f);

            reader.readAsDataURL(f);

        }





        function handleFileSelectplaceholder(evt) {



            var f = evt.target.files[0];

            var reader = new FileReader();



            reader.onload = (function (theFile) {

                return function (e) {



                    var filePayload = e.target.result;

                    var val = f.name;

                    var ext = val.split('.')[1];

                    var docName = val.split('fakepath')[1];

                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')



                    var timestamp = Number(new Date());

                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                    placeholderphoto = filePayload;

                    placeholderFileName=filename;

                    $(".placeholder_img_thumb").empty();

                    $(".placeholder_img_thumb").append('<span class="image-item"><img class="rounded" style="width:50px" src="' + filePayload + '" alt="image"></span>');





                };

            })(f);

            reader.readAsDataURL(f);

        }



        function handleFileSelectFavicon(evt) {



            var f = evt.target.files[0];

            var reader = new FileReader();



            reader.onload = (function (theFile) {

                return function (e) {



                    var filePayload = e.target.result;

                    var val = f.name;

                    var ext = val.split('.')[1];

                    var docName = val.split('fakepath')[1];

                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')



                    var timestamp = Number(new Date());

                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;

                    favicon = filePayload;

                    favIconFileName=filename;

                    $(".favicon_img_thumb").empty();

                    $(".favicon_img_thumb").append('<span class="image-item"><img class="rounded" style="width:50px" src="' + filePayload + '" alt="image"></span>');





                };

            })(f);

            reader.readAsDataURL(f);

        }



        function handleUploadJsonFile(evt) {

            // File upload handled via AJAX in the #addFile change event handler

        }



    </script>



@endsection

