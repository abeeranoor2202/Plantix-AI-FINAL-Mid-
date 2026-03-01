@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Settings</span>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Payments</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.app_setting_payment_method')}}</h1>
    </div>

    {{-- Payment Sub-Navigation --}}
    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <a href="{{ route('admin.payment.stripe') }}" class="stripe_active_label" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fab fa-stripe" style="font-size: 20px;"></i>
            {{trans('lang.app_setting_stripe')}}
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
        <a href="{{ route('admin.payment.cod') }}" class="cod_active_label" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-hand-holding-usd" style="font-size: 18px;"></i>
            {{trans('lang.app_setting_cod_short')}}
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 40px;">
                
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700; border-radius: 12px; z-index: 10;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px;">
                    <div style="width: 48px; height: 48px; background: rgba(var(--agri-primary-rgb), 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--agri-primary);">
                        <i class="fab fa-cc-stripe" style="font-size: 24px;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Stripe Integration</h4>
                        <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">Secure online credit/debit card processing.</p>
                    </div>
                </div>

                <form>
                    {{-- Enable Stripe --}}
                    <div style="background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">{{trans('lang.app_setting_enable_stripe')}}</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">{!! trans('lang.app_setting_enable_stripe_help') !!}</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" class="enable_stripe" id="enable_stripe" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                        </div>
                    </div>

                    {{-- API Credentials --}}
                    <div style="margin-bottom: 32px;">
                        <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-key" style="color: var(--agri-primary);"></i> API Credentials
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_stripe_key')}}</label>
                                <input type="text" class="form-agri stripe_key" placeholder="pk_test_...">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{!! trans('lang.app_setting_stripe_key_help') !!}</div>
                            </div>

                            <div class="col-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.app_setting_stripe_secret')}}</label>
                                <input type="password" class="form-agri stripe_secret" placeholder="sk_test_...">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">{!! trans('lang.app_setting_stripe_secret_help') !!}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Payouts Section --}}
                    <div style="background: rgba(var(--agri-primary-rgb), 0.05); padding: 24px; border-radius: 16px; border: 1px dashed var(--agri-primary); display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 4px;">{{trans('lang.withdraw_setting')}}</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">{!! trans('lang.withdraw_setting_enable_stripe_help') !!}</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" class="withdraw_enable" id="withdraw_enable" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-top: 40px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="flex: 2; height: 48px; font-size: 15px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> {{trans('lang.save')}}
                        </button>
                        <a href="{{url('/dashboard')}}" class="btn-agri btn-agri-outline" style="flex: 1; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 15px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Security Card --}}
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px; background: white; border-top: 4px solid var(--agri-primary);">
                <h5 style="font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-shield-alt"></i> Security Standard
                </h5>
                <p style="font-size: 14px; color: var(--agri-text-muted); line-height: 1.6; margin-bottom: 20px;">
                    Stripe is a PCI Service Provider Level 1, which is the most stringent level of security available in the payments industry.
                </p>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e3f2fd; color: #1e88e5; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading);">SSL Encrypted</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e8f5e9; color: #43a047; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading);">Verified by Stripe</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--agri-border);">
                     <a href="https://stripe.com/docs/keys" target="_blank" style="color: var(--agri-primary); font-size: 13px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                         How to get API keys? <i class="fas fa-external-link-alt" style="font-size: 10px;"></i>
                     </a>
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
        // Pre-fill stripe settings from DB
        @if($settings->has('stripe_enabled'))
        $("#enable_stripe").prop('checked', {{ $settings->get('stripe_enabled') == '1' ? 'true' : 'false' }});
        @endif
        @if($settings->has('stripe_key'))
        $(".stripe_key").val('{!! addslashes($settings->get("stripe_key", "")) !!}');
        @endif
        @if($settings->has('stripe_secret'))
        $(".stripe_secret").val('{!! addslashes($settings->get("stripe_secret", "")) !!}');
        @endif
        @if($settings->has('stripe_withdraw_enabled'))
        $("#withdraw_enable").prop('checked', {{ $settings->get('stripe_withdraw_enabled') == '1' ? 'true' : 'false' }});
        @endif
        @if($settings->has('cod_enabled'))
        var codEnabled = {{ $settings->get('cod_enabled') == '1' ? 'true' : 'false' }};
        if (codEnabled) {
            $(".cod_status_badge").removeClass('bg-danger').addClass('bg-success').text('Active');
        } else {
            $(".cod_status_badge").removeClass('bg-success').addClass('bg-danger').text('Inactive');
        }
        @endif

        $(".save-form-btn").click(function () {
            jQuery("#data-table_processing").show();
            $.ajax({
                url: '{{ route("admin.payment.stripe.save") }}',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    stripe_enabled: $("#enable_stripe").is(":checked") ? 1 : 0,
                    stripe_key: $(".stripe_key").val(),
                    stripe_secret: $(".stripe_secret").val(),
                    stripe_withdraw_enabled: $("#withdraw_enable").is(":checked") ? 1 : 0
                },
                success: function (res) {
                    jQuery("#data-table_processing").hide();
                    if (res.success) {
                        toastr.success('Stripe settings saved!');
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

        // COD toggle (if present)
        $(document).on('click', '#cod_toggle_btn', function () {
            var current = $(".cod_status_badge").text() === 'Active' ? 1 : 0;
            var newVal = current ? 0 : 1;
            $.ajax({
                url: '{{ route("admin.payment.stripe.save") }}',
                method: 'POST',
                data: { _token: csrfToken, cod_enabled: newVal },
                success: function () {
                    if (newVal) {
                        $(".cod_status_badge").removeClass('bg-danger').addClass('bg-success').text('Active');
                    } else {
                        $(".cod_status_badge").removeClass('bg-success').addClass('bg-danger').text('Inactive');
                    }
                }
            });
        });
    });
</script>
@endsection
