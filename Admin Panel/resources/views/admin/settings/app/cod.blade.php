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
        <a href="{{url('settings/payment/stripe')}}" class="stripe_active_label" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fab fa-stripe" style="font-size: 20px;"></i>
            {{trans('lang.app_setting_stripe')}}
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
        <a href="{{url('settings/payment/cod')}}" class="cod_active_label" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary); display: flex; align-items: center; gap: 8px;">
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
                        <i class="fas fa-money-bill-wave" style="font-size: 24px;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Cash on Delivery</h4>
                        <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">Direct cash payments upon successful delivery.</p>
                    </div>
                </div>

                <form>
                    <div style="background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">{{trans('lang.app_setting_enable_cod')}}</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">{!! trans('lang.app_settings_enable_cod_help') !!}</p>
                        </div>
                        <div class="form-check form-switch" style="padding: 0; margin: 0;">
                            <input type="checkbox" class="enable_cod" id="enable_cod" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-top: 40px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="flex: 2; height: 48px; font-size: 15px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> {{trans('lang.save')}}
                        </button>
                        <a href="{{url('/dashboard')}}" class="btn-agri btn-agri-outline" style="flex: 1; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 15px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px; background: linear-gradient(135deg, var(--agri-primary-dark) 0%, #1b3d2f 100%); color: white; border: none;">
                <h5 style="font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-info-circle"></i> Logistic Insight
                </h5>
                <p style="font-size: 14px; opacity: 0.9; line-height: 1.6;">
                    Cash on Delivery (COD) remains a vital payment option for agricultural merchants. It builds trust with farmers who prefer manual verification of received seeds, equipment, or products before finalizing payment.
                </p>
                <div style="background: rgba(255,255,255,0.1); padding: 16px; border-radius: 12px; margin-top: 16px;">
                    <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; margin-bottom: 4px;">Merchant Rule</div>
                    <div style="font-size: 14px;">Ensure delivery partners are equipped for secure cash collection.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        // Fetch payment settings from backend API
        $.ajax({
            url: '{{ route("api.admin.settings.payment-methods") }}',
            method: 'GET',
            success: function(response) {
                if (response && response.data) {
                    var paymentSettings = response.data;

                    // Update COD status
                    if (paymentSettings.cod && paymentSettings.cod.is_enabled) {
                        $(".enable_cod").prop('checked', true);
                        jQuery(".cod_active_label span").addClass('badge-success');
                        jQuery(".cod_active_label span").text('Active');
                    }

                    // Update Stripe status
                    if (paymentSettings.stripe && paymentSettings.stripe.is_enabled) {
                        jQuery(".stripe_active_label span").addClass('badge-success');
                        jQuery(".stripe_active_label span").text('Active');
                    }
                }
                jQuery("#data-table_processing").hide();
            },
            error: function(xhr) {
                jQuery("#data-table_processing").hide();
                console.log('Error loading payment settings', xhr);
            }
        });

        $(".edit-form-btn").click(function () {
            var isCODEnabled = $(".enable_cod").is(":checked");
            jQuery("#data-table_processing").show();

            $.ajax({
                url: '{{ route("api.admin.settings.update") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'cod_enabled': isCODEnabled
                },
                success: function(result) {
                    window.location.href = '{{ url("settings/payment/cod") }}';
                },
                error: function(xhr) {
                    jQuery("#data-table_processing").hide();
                    console.log('Error updating settings', xhr);
                    alert('Failed to save settings. Please try again.');
                }
            });
        });
    });

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
</script>
@endsection
