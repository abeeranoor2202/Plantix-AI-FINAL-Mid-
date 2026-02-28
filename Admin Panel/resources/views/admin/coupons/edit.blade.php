@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{route('admin.stores.coupons',$_GET['eid'])}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_plural')}}</a>
            <?php } else { ?>
                <a href="{!! route('admin.coupons') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_plural')}}</a>
            <?php } ?>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_edit')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.coupon_edit')}}</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update the configuration and properties of your promotional campaign.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px;">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8);">
                    <div class="spinner-border spinner-border-sm text-success"></div> {{trans('lang.processing')}}
                </div>

                <div class="error_top alert alert-danger" style="display:none; border-radius: 12px; font-size: 14px; font-weight: 600;"></div>

                <form>
                    {{-- Basic Info Section --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-edit"></i> Campaign Details
                        </h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_code')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri coupon_code" placeholder="e.g. AGRI2024" style="text-transform: uppercase;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_code_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_discount_type')}}</label>
                                <select id="coupon_discount_type" class="form-agri">
                                    <option value="Percentage">{{trans('lang.coupon_percent')}}</option>
                                    <option value="Fix Price">{{trans('lang.coupon_fixed')}}</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_discount_type_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_discount')}} <span class="text-danger">*</span></label>
                                <input type="number" class="form-agri coupon_discount" placeholder="0">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_discount_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_expires_at')}} <span class="text-danger">*</span></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-agri date_picker" placeholder="MM/DD/YYYY" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"/>
                                    <span class="input-group-text" style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-left: none; border-radius: 0 12px 12px 0;">
                                        <i class="fas fa-calendar-alt" style="color: var(--agri-primary);"></i>
                                    </span>
                                </div>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_expires_at_help") }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Target & Visibility --}}
                    <div style="margin-bottom: 32px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-bullseye"></i> Targeting & Visibility
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="agri-label">{{trans('lang.coupon_store_id')}}</label>
                                <select id="vendor_restaurant_select" class="form-agri select2">
                                    <option value="">{{trans('lang.select_store')}}</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_store_id_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--agri-border);">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0;">
                                        <label class="agri-label mb-0" for="coupon_public" style="cursor: pointer;">
                                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{trans('lang.coupon_public')}}</div>
                                            <div style="font-size: 11px; font-weight: 500; color: var(--agri-text-muted);">Visible to all customers</div>
                                        </label>
                                        <input type="checkbox" class="form-check-input coupon_public" id="coupon_public" style="width: 44px; height: 22px; cursor: pointer; margin: 0;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--agri-border);">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0;">
                                        <label class="agri-label mb-0" for="coupon_enabled" style="cursor: pointer;">
                                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{trans('lang.coupon_enabled')}}</div>
                                            <div style="font-size: 11px; font-weight: 500; color: var(--agri-text-muted);">Status: Active/Inactive</div>
                                        </label>
                                        <input type="checkbox" class="form-check-input coupon_enabled" id="coupon_enabled" style="width: 44px; height: 22px; cursor: pointer; margin: 0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div style="margin-bottom: 0; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <label class="agri-label">{{trans('lang.coupon_description')}}</label>
                        <textarea rows="4" class="form-agri coupon_description" id="coupon_description" placeholder="Summarize the benefits of this promotion..."></textarea>
                        <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_description_help") }}</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Image Upload Card --}}
            <div class="card-agri" style="padding: 24px; position: sticky; top: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">Promotion Creative</h4>
                
                <div style="border: 2px dashed var(--agri-border); border-radius: 16px; padding: 32px; text-align: center; background: white; transition: 0.3s;" id="drop-zone">
                    <div class="placeholder_img_thumb coupon_image" style="margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background: var(--agri-bg); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; color: var(--agri-border); font-size: 32px;">
                            <i class="fas fa-image"></i>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: var(--agri-text-muted); font-weight: 600; margin-bottom: 20px;">Update the campaign banner image.</p>
                    <label class="btn-agri btn-agri-outline" style="cursor: pointer; display: inline-block;">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i> Change Image
                        <input type="file" onChange="handleFileSelect(event)" style="display: none;">
                    </label>
                </div>
                
                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--agri-border);">
                    <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="width: 100%; height: 48px; font-weight: 700; font-size: 15px; margin-bottom: 12px;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Update Campaign
                    </button>
                    <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                        <a href="{{route('admin.stores.coupons',$_GET['eid'])}}" class="btn-agri btn-agri-outline" style="width: 100%; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 600;">{{trans('lang.cancel')}}</a>
                    <?php } else { ?>
                        <a href="{!! route('admin.coupons') !!}" class="btn-agri btn-agri-outline" style="width: 100%; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 600;">{{trans('lang.cancel')}}</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 13px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 10px; display: block; }
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

<script>
var id = "<?php echo $id;?>";
var database = firebase.firestore();
var ref = database.collection('coupons').doc(id);
var photo_coupon = "";
var fileName = "";
var couponImageFile = "";
var storage = firebase.storage();
var storageRef = storage.ref('images');
var placeholderImage = '';

$(document).ready(function () {
    jQuery("#data-table_processing").show();

    database.collection('settings').doc('placeHolderImage').get().then(function (snapshot) {
        placeholderImage = snapshot.data().image;
    });

    $('#datetimepicker1').datepicker({ dateFormat: 'mm/dd/yyyy', autoclose: true, todayHighlight: true });

    ref.get().then(async function (doc) {
        if (!doc.exists) return;
        var coupon = doc.data();

        database.collection('vendors').orderBy('title', 'asc').get().then(function (snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                var selected = (data.id == coupon.resturant_id) ? 'selected' : '';
                $('#vendor_restaurant_select').append('<option value="' + data.id + '" ' + selected + '>' + data.title + '</option>');
            });
        });

        $(".coupon_code").val(coupon.code);
        $("#coupon_discount_type").val(coupon.discountType);
        $(".coupon_discount").val(coupon.discount);
        $(".coupon_description").val(coupon.description);
        $(".coupon_enabled").prop("checked", coupon.isEnabled);
        $(".coupon_public").prop("checked", coupon.isPublic);

        if (coupon.image) {
            photo_coupon = coupon.image;
            couponImageFile = coupon.image;
            $(".coupon_image").html('<img class="rounded" style="width:100px; height:100px; object-fit:cover; border-radius:12px;" src="' + photo_coupon + '" onerror="this.src=\'' + placeholderImage + '\'">');
        } else {
             $(".coupon_image").html('<div style="width:100px; height:100px; background:var(--agri-bg); border-radius:20px; display:flex; align-items:center; justify-content:center; color:var(--agri-border); font-size:32px;"><i class="fas fa-image"></i></div>');
        }

        if (coupon.expiresAt) {
            var date = coupon.expiresAt.toDate();
            var expiresDate = (date.getMonth() + 1).toString().padStart(2, '0') + '/' + date.getDate().toString().padStart(2, '0') + '/' + date.getFullYear();
            $('.date_picker').datepicker('setDate', expiresDate);
        }

        jQuery("#data-table_processing").hide();
    });

    $(".edit-form-btn").click(function () {
        var code = $(".coupon_code").val();
        var discount = $(".coupon_discount").val();
        var description = $(".coupon_description").val();
        var newdate = new Date($(".date_picker").val());
        var expiresAt = new Date(newdate.setHours(23, 59, 59, 999));
        var isEnabled = $(".coupon_enabled").is(":checked");
        var isPublic = $(".coupon_public").is(":checked");
        var discountType = $("#coupon_discount_type").val();
        var resturant_id = $("#vendor_restaurant_select").val();

        if (code == '' || discount == '' || newdate == 'Invalid Date') {
            $(".error_top").show().html("<p>Please fill in all required fields.</p>");
            window.scrollTo(0, 0); return;
        }

        jQuery("#data-table_processing").show();
        
        // Code uniqueness check
        database.collection('coupons').where('code', '==', code).get().then(async function (querySnapshot) {
            let exists = false;
            querySnapshot.forEach((doc) => { if (doc.id !== id) exists = true; });

            if (exists) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>Code already exists try another one!</p>");
                window.scrollTo(0, 0); return;
            }

            storeImageData().then(IMG => {
                ref.update({
                    'code': code, 'description': description, 'discount': discount, 'expiresAt': expiresAt,
                    'isEnabled': isEnabled, 'discountType': discountType, 'image': IMG,
                    'resturant_id': resturant_id, 'isPublic': isPublic
                }).then(() => {
                    window.location.href = "<?php echo (isset($_GET['eid']) && $_GET['eid'] != '') ? route('admin.stores.coupons', $_GET['eid']) : route('admin.coupons'); ?>";
                });
            });
        });
    });
});

async function storeImageData() {
    if (photo_coupon == couponImageFile) return photo_coupon;
    var newPhoto = '';
    try {
        var base64 = photo_coupon.replace(/^data:image\/[a-z]+;base64,/, "");
        var uploadTask = await storageRef.child(fileName).putString(base64, 'base64', { contentType: 'image/jpg' });
        newPhoto = await uploadTask.ref.getDownloadURL();
    } catch (error) { console.log(error); }
    return newPhoto;
}

function handleFileSelect(evt) {
    var f = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = (function (theFile) {
        return function (e) {
            photo_coupon = e.target.result;
            fileName = theFile.name.split('.')[0] + "_" + Number(new Date()) + '.' + theFile.name.split('.').pop();
            $(".coupon_image").html('<img class="rounded" style="width:100px; height:100px; object-fit:cover; border-radius:12px;" src="' + photo_coupon + '">');
        };
    })(f);
    reader.readAsDataURL(f);
}
</script>
@endsection
