@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Settings</span>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Payments</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Payment Methods</h1>
    </div>

    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <a href="{{ route('admin.payment.stripe') }}" class="stripe_active_label" style="text-decoration: none; padding: 12px 4px; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fab fa-stripe" style="font-size: 20px;"></i>
            Stripe
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
        <a href="{{ route('admin.payment.cod') }}" class="cod_active_label" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-hand-holding-usd" style="font-size: 18px;"></i>
            COD
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Stripe Integration</h4>
                    <div id="data-table_processing" style="display: none; color: var(--agri-primary); font-weight: 700;">Processing...</div>
                </div>

                <div style="padding: 24px;">
                    <div style="background: var(--agri-bg); padding: 20px; border-radius: 14px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">Enable Stripe</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">Enable Stripe card payments for checkout.</p>
                        </div>
                        <input type="checkbox" class="enable_stripe" id="enable_stripe" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-12">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Publishable Key</label>
                            <input type="text" class="form-agri stripe_key" placeholder="pk_test_...">
                        </div>
                        <div class="col-12">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Secret Key</label>
                            <input type="password" class="form-agri stripe_secret" placeholder="sk_test_...">
                        </div>
                    </div>

                    <div style="background: #ecfdf5; padding: 20px; border-radius: 14px; border: 1px dashed #86efac; display: flex; align-items: center; justify-content: space-between; margin-top: 22px;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">Enable Stripe Withdrawals</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">Allow payouts via Stripe for withdrawals.</p>
                        </div>
                        <input type="checkbox" class="withdraw_enable" id="withdraw_enable" style="width: 50px; height: 26px; cursor: pointer; accent-color: var(--agri-primary);">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="height: 44px;">Save</button>
                        <a href="{{ url('/dashboard') }}" class="btn-agri btn-agri-outline" style="height: 44px; display: inline-flex; align-items: center; text-decoration: none;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px;">
                <h5 style="font-weight: 700; color: var(--agri-text-heading); margin-bottom: 14px;">Security Standard</h5>
                <p style="font-size: 14px; color: var(--agri-text-muted); line-height: 1.6; margin-bottom: 0;">Stripe is a PCI Service Provider Level 1 and supports encrypted card processing.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var csrfToken = '{{ csrf_token() }}';
$(document).ready(function () {
    @if($settings->has('stripe_enabled'))
    $('#enable_stripe').prop('checked', {{ $settings->get('stripe_enabled') == '1' ? 'true' : 'false' }});
    @endif
    @if($settings->has('stripe_key'))
    $('.stripe_key').val('{!! addslashes($settings->get("stripe_key", "")) !!}');
    @endif
    @if($settings->has('stripe_secret'))
    $('.stripe_secret').val('{!! addslashes($settings->get("stripe_secret", "")) !!}');
    @endif
    @if($settings->has('stripe_withdraw_enabled'))
    $('#withdraw_enable').prop('checked', {{ $settings->get('stripe_withdraw_enabled') == '1' ? 'true' : 'false' }});
    @endif
    @if($settings->has('cod_enabled'))
    var codEnabled = {{ $settings->get('cod_enabled') == '1' ? 'true' : 'false' }};
    if (codEnabled) {
        $('.cod_active_label span').addClass('badge-success').text('Active');
    }
    @endif

    $('.save-form-btn').click(function () {
        $('#data-table_processing').show();
        $.ajax({
            url: '{{ route("admin.payment.stripe.save") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                stripe_enabled: $('#enable_stripe').is(':checked') ? 1 : 0,
                stripe_key: $('.stripe_key').val(),
                stripe_secret: $('.stripe_secret').val(),
                stripe_withdraw_enabled: $('#withdraw_enable').is(':checked') ? 1 : 0
            },
            success: function (res) {
                $('#data-table_processing').hide();
                if (res.success) {
                    toastr.success('Stripe settings saved!');
                } else {
                    toastr.error(res.message || 'Save failed.');
                }
            },
            error: function () {
                $('#data-table_processing').hide();
                toastr.error('Server error.');
            }
        });
    });
});
</script>
@endsection
