@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.coupons') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Incentive Registry</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Generate Campaign</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Initialise Promotional Campaign</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Define the parameters, discount vectors, and geospatial targets for your new incentive.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); z-index: 10; position: absolute; top:0; left:0; width:100%; height:100%; border-radius: 16px; align-items: center; justify-content: center;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--agri-primary);"></div>
                        <div style="font-weight: 800; color: var(--agri-primary); letter-spacing: 1px;">INITIALIZING TELEMETRY...</div>
                    </div>
                </div>

                <div class="error_top alert alert-danger" style="display:none; border-radius: 12px; font-size: 14px; font-weight: 700; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; padding: 16px;"></div>

                <form>
                    {{-- Basic Info Section --}}
                    <div style="margin-bottom: 40px;">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 32px; height: 32px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            Campaign Core Parameters
                        </h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Incentive Hash (Code) <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri coupon_code" placeholder="e.g. AGRI2024" style="text-transform: uppercase; font-family: 'Courier New', Courier, monospace; font-weight: 800; font-size: 16px; letter-spacing: 2px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Alphanumeric identifier distributed to end-users.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Yield Vector (Type)</label>
                                <select id="coupon_discount_type" class="form-agri" style="font-weight: 700;">
                                    <option value="Percentage">Percentage Scaling (%)</option>
                                    <option value="Fix Price">Fixed Capital Deduction</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Determine how the incentive modifier is applied to the gross value.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Discount Magnitude <span class="text-danger">*</span></label>
                                <input type="number" class="form-agri coupon_discount" placeholder="0" style="font-weight: 800; font-size: 16px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">The numerical value of the discount (e.g. 15 for 15%).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Vector Expiration Boundary <span class="text-danger">*</span></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-agri date_picker" placeholder="MM/DD/YYYY" style="border-top-right-radius: 0; border-bottom-right-radius: 0; font-weight: 700;"/>
                                    <span class="input-group-text" style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-left: none; border-radius: 0 12px 12px 0;">
                                        <i class="fas fa-calendar-alt" style="color: var(--agri-primary);"></i>
                                    </span>
                                </div>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Chronological limit after which the hash becomes void.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Target & Visibility --}}
                    <div style="margin-bottom: 40px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 32px; height: 32px; background: #ECFDF5; color: #059669; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-crosshairs"></i>
                            </div>
                            Ecosystem Targeting & Governance
                        </h4>

                        <div class="row g-4">
                            @if($id == '')
                            <div class="col-md-12">
                                <label class="agri-label">Geospatial Origin / Fulfillment Partner</label>
                                <select id="vendor_restaurant_select" class="form-agri select2">
                                    <option value="">System-Wide (All Partners)</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Restrict this incentive to a specific vendor node or leave unselected for global application.</div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s; cursor: pointer;" onclick="document.getElementById('coupon_public').click();" onmouseover="this.style.borderColor='var(--agri-primary)'" onmouseout="this.style.borderColor='var(--agri-border)'">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0; pointer-events: none;">
                                        <label class="agri-label mb-0" style="margin-bottom: 0;">
                                            <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: none;">Global Ecosystem Visibility</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Broadcast to all authenticated user nodes.</div>
                                        </label>
                                        <input type="checkbox" class="form-check-input coupon_public" id="coupon_public" style="width: 44px; height: 24px; pointer-events: auto;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s; cursor: pointer;" onclick="document.getElementById('coupon_enabled').click();" onmouseover="this.style.borderColor='var(--agri-primary)'" onmouseout="this.style.borderColor='var(--agri-border)'">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0; pointer-events: none;">
                                        <label class="agri-label mb-0" style="margin-bottom: 0;">
                                            <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: none;">Immediate Activation</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Deploy campaign to the live environment instantly.</div>
                                        </label>
                                        <input type="checkbox" class="form-check-input coupon_enabled" id="coupon_enabled" checked style="width: 44px; height: 24px; pointer-events: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div style="margin-bottom: 0; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <label class="agri-label">Campaign Context & Directives</label>
                        <textarea rows="4" class="form-agri coupon_description" id="coupon_description" placeholder="Summarize the strategic intent and user benefits of this incentive vector..." style="font-weight: 500; font-size: 13px;"></textarea>
                        <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Provide internal or public-facing context regarding the campaign's purpose.</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Image Upload Card --}}
            <div class="card-agri" style="padding: 24px; position: sticky; top: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <i class="fas fa-image me-2 text-muted"></i> Campaign Creative Asset
                </h4>
                
                <div style="border: 2px dashed var(--agri-border); border-radius: 16px; padding: 40px 20px; text-align: center; background: var(--agri-bg); transition: 0.3s;" id="drop-zone">
                    <div class="placeholder_img_thumb coupon_image" style="margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background: white; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; color: var(--agri-text-muted); font-size: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600; margin-bottom: 24px; padding: 0 20px;">Upload a representative graphical asset for this campaign vector.</p>
                    <label class="btn-agri" style="cursor: pointer; display: inline-block; background: white; border: 1px solid var(--agri-border); color: var(--agri-text-heading); font-weight: 700; padding: 10px 20px;">
                        <i class="fas fa-folder-open" style="margin-right: 8px;"></i> BROWSE REGISTRY
                        <input type="file" onChange="handleFileSelect(event)" style="display: none;">
                    </label>
                </div>
                
                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--agri-border);">
                    <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="width: 100%; height: 52px; font-weight: 800; font-size: 14px; margin-bottom: 12px; letter-spacing: 1px;">
                        <i class="fas fa-satellite-dish" style="margin-right: 8px;"></i> DEPLOY CAMPAIGN
                    </button>
                    @if($id != '')
                        <a href="{{route('admin.coupons')}}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">ABORT OPERATION</a>
                    @else
                        <a href="{!! route('admin.coupons') !!}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">ABORT OPERATION</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
    .form-agri:focus { border-color: var(--agri-primary) !important; background: white !important; }
    #drop-zone.dragover { border-color: var(--agri-primary); background: var(--agri-primary-light); }
    .select2-container--default .select2-selection--single { height: 48px; border: 1px solid var(--agri-border); border-radius: 12px; background: var(--agri-bg); }
    .select2-container--default .select2-selection--single:focus, .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--agri-primary); background: white; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; padding-left: 16px; font-weight: 600; font-size: 14px; color: var(--agri-text-heading); }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); box-shadow: 0 0 0 4px var(--agri-primary-light); }
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

<script>
var database = firebase.firestore();
var photo_coupon = "";
var fileName = "";
var resturant = "<?php echo $id; ?>";
var resturant_id = resturant || '';
var id = "<?php echo uniqid();?>";

$(document).ready(function () {
    jQuery("#data-table_processing").css('display', 'flex');

    database.collection('vendors').orderBy('title', 'asc').get().then(async function (snapshots) {
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            $('#vendor_restaurant_select').append($("<option></option>").attr("value", data.id).text(data.title));
        });
        jQuery("#data-table_processing").hide();
    });

    $('#datetimepicker1').datepicker({ dateFormat: 'mm/dd/yyyy', autoclose: true, todayHighlight: true });

    if (resturant == '') {
        $("#vendor_restaurant_select").change(function () {
            resturant_id = $(this).val();
        });
    }

    $(".save-form-btn").click(function () {
        var code = $(".coupon_code").val();
        var discount = $(".coupon_discount").val();
        var description = $(".coupon_description").val();
        var newdate = new Date($(".date_picker").val());
        var expiresAt = new Date(newdate.setHours(23, 59, 59, 999));
        var isEnabled = $(".coupon_enabled").is(":checked");
        var isPublic = $(".coupon_public").is(":checked");
        var discountType = $("#coupon_discount_type").val();

        if (discountType === 'Percentage' && (discount < 0 || discount > 100)) {
            $(".error_top").show().html("<i class='fas fa-exclamation-triangle me-2'></i> Percentage yield must range between 0 and 100.");
            window.scrollTo(0, 0); return;
        }

        if (code == '' || discount == '' || newdate == 'Invalid Date') {
            $(".error_top").show().html("<i class='fas fa-exclamation-triangle me-2'></i> Mandatory parameters missing: Hash, Magnitude, or Expiration Vector.");
            window.scrollTo(0, 0); return;
        }

        jQuery("#data-table_processing").css('display', 'flex');
        database.collection('coupons').where('code', '==', code).get().then(function (snapshot) {
            if (snapshot.size > 0) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<i class='fas fa-exclamation-triangle me-2'></i> Sequence Collision: This Incentive Hash is already active. Generate a unique hash.");
                window.scrollTo(0, 0);
            } else {
                storeImageData().then(IMG => {
                    database.collection('coupons').doc(id).set({
                        'code': code, 'description': description, 'discount': discount, 'expiresAt': expiresAt,
                        'isEnabled': isEnabled, 'id': id, 'discountType': discountType, 'image': IMG,
                        'resturant_id': resturant_id, 'isPublic': isPublic
                    }).then(function (result) {
                        window.location.href = "{{route('admin.coupons')}}";
                    } else {
                        window.location.href = "{{ route('admin.coupons') }}";
                    }
                    });
                });
            }
        });
    });
});

var storageRef = firebase.storage().ref('images');

async function storeImageData() {
    var newPhoto = '';
    if(!photo_coupon) return '';
    try {
        var base64 = photo_coupon.replace(/^data:image\/[a-z]+;base64,/, "");
        var uploadTask = await storageRef.child(fileName).putString(base64, 'base64', {contentType: 'image/jpg'});
        newPhoto = await uploadTask.ref.getDownloadURL();
    } catch (error) { console.log("ERR ===", error); }
    return newPhoto;
}

function handleFileSelect(evt) {
    var f = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = (function (theFile) {
        return function (e) {
            photo_coupon = e.target.result;
            var timestamp = Number(new Date());
            fileName = theFile.name.split('.')[0] + "_" + timestamp + '.' + theFile.name.split('.').pop();
            $(".coupon_image").html('<img style="width:100%; height:160px; object-fit:cover; border-radius:12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" src="' + photo_coupon + '" alt="Creative Asset">');
            $(".coupon_image").parent().css('padding', '20px');
        };
    })(f);
    reader.readAsDataURL(f);
}
</script>
@endsection
