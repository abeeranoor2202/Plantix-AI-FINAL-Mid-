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
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.user_details')}}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Farmer Profile</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Comprehensive overview of account activities and details.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus-circle"></i>
                    {{trans('lang.add_wallet_amount')}}
                </a>
                <a href="{!! route('admin.users') !!}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    {{-- Profile Navigation Tabs --}}
    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <a href="{{route('admin.users.view',$id)}}" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary);">
            {{trans('lang.tab_basic')}}
        </a>
        <a href="{{route('admin.orders.index')}}?userId={{$id}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600;">
            {{trans('lang.tab_orders')}}
        </a>
        <a href="#" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600;" title="Wallet transactions feature not available">
            {{trans('lang.wallet_transaction')}}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            {{-- User Portrait Card --}}
            <div class="card-agri" style="text-align: center; padding: 40px 24px;">
                <div class="profile_image" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.1); margin: 0 auto 24px; overflow: hidden; background: var(--agri-bg); display: flex; align-items: center; justify-content: center;">
                    {{-- Profile image injected by JS --}}
                </div>
                <h3 class="user_name" style="font-size: 22px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 8px;">---</h3>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-seedling"></i> Verified Farmer
                </div>
                
                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border);">
                    <div style="margin-bottom: 16px;">
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--agri-text-muted); font-weight: 700; display: block; margin-bottom: 4px;">Wallet Balance</span>
                        <div class="wallet_balance" style="font-size: 24px; font-weight: 800; color: var(--agri-primary);">---</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                         <i class="fas fa-envelope" style="color: var(--agri-text-muted); width: 16px;"></i>
                         <span class="email">---</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-top: 10px;">
                         <i class="fas fa-phone-alt" style="color: var(--agri-text-muted); width: 16px;"></i>
                         <span class="phone">---</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">{{trans('lang.address')}}</h4>
                <div class="address">
                    {{-- Addresses injected by JS --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Wallet Modal --}}
<div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: var(--agri-primary); color: white; padding: 24px; border: none;">
                <h5 class="modal-title" style="font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-wallet"></i> {{trans('lang.add_wallet_amount')}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="border: none; background: transparent; color: white; outline: none;"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 32px;">
                <form id="walletForm">
                    <div style="margin-bottom: 24px;">
                        <label class="agri-label">{{trans('lang.amount')}}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light" style="border-color: var(--agri-border);">{{trans('lang.currency_symbol')}}</span>
                            <input type="number" name="amount" class="form-agri" id="amount" placeholder="0.00" style="margin-bottom: 0; border-left: none;">
                        </div>
                        <div id="wallet_error" style="color: var(--agri-error); font-size: 12px; margin-top: 6px; font-weight: 600;"></div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label class="agri-label">{{trans('lang.note')}}</label>
                        <textarea name="note" class="form-agri" id="note" rows="3" placeholder="Reason for top-up..."></textarea>
                    </div>

                    <div id="user_account_not_found_error" style="color: var(--agri-error); font-size: 13px; margin-bottom: 16px; font-weight: 700;"></div>

                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="flex: 2; height: 48px;">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{trans('submit')}}
                        </button>
                        <button type="button" class="btn-agri btn-agri-outline" data-dismiss="modal" style="flex: 1; height: 48px;">
                            {{trans('close')}}
                        </button>
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
    .address-card {
        background: var(--agri-bg);
        border: 1px solid var(--agri-border);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }
    .address-card:hover {
        border-color: var(--agri-primary);
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>
@endsection

@section('scripts')
<script>
    var id = "{{$id}}";
    var database = firebase.firestore();
    var ref = database.collection('users').where("id", "==", id);
    var placeholderImage = '';

    database.collection('settings').doc('placeHolderImage').get().then(async function (snapshotsimage) {
        placeholderImage = snapshotsimage.data().image;
    });

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    database.collection('currencies').where('isActive', '==', true).get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        decimal_degits = currencyData.decimal_degits || 0;
    });

    var email_templates = database.collection('email_templates').where('type', '==', 'wallet_topup');
    var emailTemplatesData = null;

    $(document).ready(async function () {
        jQuery("#data-table_processing").show();

        await email_templates.get().then(async function (snapshots) {
            if(!snapshots.empty) emailTemplatesData = snapshots.docs[0].data();
        });

        ref.get().then(async function (snapshots) {
            var user = snapshots.docs[0].data();
            $(".user_name").text((user.firstName || '') + ' ' + (user.lastName || ''));
            $(".email").text(user.email || '{{trans("lang.not_mentioned")}}');
            $(".phone").text(user.phoneNumber || '{{trans("lang.not_mentioned")}}');

            var wallet_balance = user.wallet_amount || 0;
            if (currencyAtRight) {
                wallet_balance = parseFloat(wallet_balance).toFixed(decimal_degits) + currentCurrency;
            } else {
                wallet_balance = currentCurrency + parseFloat(wallet_balance).toFixed(decimal_degits);
            }
            $('.wallet_balance').html(wallet_balance);

            var profileImg = '<img class="rounded-circle" style="width:100%; height:100%; object-fit:cover;" src="' + (user.profilePictureURL || placeholderImage) + '" onerror="this.src=\'' + placeholderImage + '\'">';
            $('.profile_image').html(profileImg);

            var addressHtml = '';
            if (user.hasOwnProperty('shippingAddress') && Array.isArray(user.shippingAddress) && user.shippingAddress.length > 0) {
                user.shippingAddress.forEach((addr) => {
                    addressHtml += '<div class="address-card">';
                    addressHtml += '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">';
                    addressHtml += '<span style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--agri-primary);">' + (addr.addressAs || 'Home') + '</span>';
                    if(addr.isDefault) addressHtml += '<span style="background:var(--agri-primary); color:white; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700;">DEFAULT</span>';
                    addressHtml += '</div>';
                    addressHtml += '<h6 style="font-weight:700; color:var(--agri-text-heading); margin-bottom:4px; line-height:1.4;">' + addr.address + '</h6>';
                    addressHtml += '<p style="font-size:13px; color:var(--agri-text-muted); margin:0;">' + (addr.locality || '') + ' ' + (addr.landmark || '') + '</p>';
                    addressHtml += '</div>';
                });
            } else {
                addressHtml = '<div style="background:var(--agri-bg); padding:40px; border-radius:16px; text-align:center; border: 1px dashed var(--agri-border); color:var(--agri-text-muted);">';
                addressHtml += '<i class="fas fa-map-marker-alt" style="font-size:32px; margin-bottom:12px; opacity:0.3;"></i><p style="margin:0; font-weight:600;">No shipping address found</p></div>';
            }
            $('.address').html(addressHtml);

            jQuery("#data-table_processing").hide();
        });

        $(".save-form-btn").click(function () {
            var amount = $('#amount').val();
            if (amount == '') {
                $('#wallet_error').text('{{trans("lang.add_wallet_amount_error")}}');
                return false;
            }

            var note = $('#note').val() || "Manual top-up by admin";
            database.collection('users').doc(id).get().then(async function (snapshot) {
                if (snapshot.exists) {
                    var data = snapshot.data();
                    var currentWallet = parseFloat(data.wallet_amount || 0);
                    var topupAmount = parseFloat(amount);
                    var newTotal = currentWallet + topupAmount;

                    database.collection('users').doc(id).update({ 'wallet_amount': newTotal }).then(function () {
                        var walletId = database.collection("tmp").doc().id;
                        database.collection('wallet').doc(walletId).set({
                            'amount': topupAmount,
                            'date': firebase.firestore.FieldValue.serverTimestamp(),
                            'isTopUp': true,
                            'id': walletId,
                            'order_id': '',
                            'payment_method': 'Admin',
                            'payment_status': 'success',
                            'user_id': id,
                            'note': note,
                            'transactionUser': "user"
                        }).then(async function () {
                            if(emailTemplatesData) {
                                // Email logic preserved but simplified
                                var message = emailTemplatesData.message;
                                var formattedDate = new Date().toLocaleDateString();
                                message = message.replace(/{username}/g, data.firstName + ' ' + data.lastName);
                                message = message.replace(/{date}/g, formattedDate);
                                message = message.replace(/{amount}/g, currentCurrency + topupAmount);
                                message = message.replace(/{paymentmethod}/g, 'Admin Dashboard');
                                message = message.replace(/{transactionid}/g, walletId);
                                message = message.replace(/{newwalletbalance}/g, currentCurrency + newTotal);
                                await sendEmail("{{url('send-email')}}", emailTemplatesData.subject, message, [data.email]);
                            }
                            window.location.reload();
                        });
                    });
                } else {
                    $('#user_account_not_found_error').text('{{trans("lang.user_detail_not_found")}}');
                }
            });
        });
    });
</script>
@endsection