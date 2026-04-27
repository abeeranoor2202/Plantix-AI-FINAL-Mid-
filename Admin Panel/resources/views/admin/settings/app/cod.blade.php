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
        <a href="{{ route('admin.payment.stripe') }}" class="stripe_active_label" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fab fa-stripe" style="font-size: 20px;"></i>
            Stripe
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
        <a href="{{ route('admin.payment.cod') }}" class="cod_active_label" style="text-decoration: none; padding: 12px 4px; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-hand-holding-usd" style="font-size: 18px;"></i>
            COD
            <span class="badge" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;"></span>
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Cash on Delivery</h4>
                    <div id="data-table_processing" style="display: none; color: var(--agri-primary); font-weight: 700;">Processing...</div>
                </div>

                <div style="padding: 24px;">
                    <div style="background: var(--agri-bg); padding: 20px; border-radius: 14px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
                        <div>
                            <h5 style="font-size: 15px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 4px;">Enable COD</h5>
                            <p style="font-size: 13px; color: var(--agri-text-muted); margin: 0;">Enable cash payment option for delivery orders.</p>
                        </div>
                        <label class="agri-switch">
                            <input type="checkbox" class="enable_cod" id="enable_cod" @checked($codEnabled)>
                            <span class="agri-slider"></span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="height: 44px;">Save</button>
                        <a href="{{ url('/dashboard') }}" class="btn-agri btn-agri-outline" style="height: 44px; display: inline-flex; align-items: center; text-decoration: none;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px; background: linear-gradient(135deg, #0f5a3e 0%, #124832 100%); color: #eaf7f0; border: none;">
                <h5 style="font-weight: 700; margin-bottom: 14px;">Logistic Insight</h5>
                <p style="font-size: 14px; line-height: 1.6; margin-bottom: 0; color: #d7f3e5;">Cash on Delivery remains useful in regions where customers prefer to verify goods before payment.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var csrfToken = '{{ csrf_token() }}';
$(document).ready(function () {
    @if($codEnabled)
    $('.cod_active_label span').addClass('badge-success').text('Active');
    @endif
    @if($stripeEnabled)
    $('.stripe_active_label span').addClass('badge-success').text('Active');
    @endif

    $('.edit-form-btn').click(function () {
        $('#data-table_processing').show();
        $.ajax({
            url: '{{ route("admin.payment.cod.save") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                cod_enabled: $('#enable_cod').is(':checked') ? 1 : 0
            },
            success: function (res) {
                $('#data-table_processing').hide();
                if (res.success) {
                    toastr.success('COD settings saved!');
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
