@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.coupons') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Coupons</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Add Coupon</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Add Coupon</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Set the coupon code, discount, and usage rules.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); z-index: 10; position: absolute; top:0; left:0; width:100%; height:100%; border-radius: 16px; align-items: center; justify-content: center;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--agri-primary);"></div>
                        <div style="font-weight: 800; color: var(--agri-primary); letter-spacing: 1px;">LOADING...</div>
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
                            Coupon Details
                        </h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="agri-label">Coupon Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri coupon_code" placeholder="e.g. AGRI2024" style="text-transform: uppercase; font-family: 'Courier New', Courier, monospace; font-weight: 800; font-size: 16px; letter-spacing: 2px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Alphanumeric identifier distributed to end-users.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Yield Vector (Type)</label>
                                <select id="coupon_discount_type" class="form-agri" style="font-weight: 700;">
                                    <option value="Percentage">Percentage Scaling (%)</option>
                                    <option value="Fix Price">Fixed Capital Deduction</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Choose whether the discount is a percentage or a fixed amount.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Discount Magnitude <span class="text-danger">*</span></label>
                                <input type="number" class="form-agri coupon_discount" placeholder="0" style="font-weight: 800; font-size: 16px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Enter the discount value, such as 15 for 15%.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Start Date</label>
                                <input type='date' class="form-agri starts_at" style="font-weight: 700;"/>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Optional start date for the coupon.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Minimum Cart Value</label>
                                <input type="number" class="form-agri min_order" placeholder="0" min="0" step="0.01">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Max Discount Cap</label>
                                <input type="number" class="form-agri max_discount" placeholder="0" min="0" step="0.01">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Global Usage Limit</label>
                                <input type="number" class="form-agri usage_limit" placeholder="Optional" min="1" step="1">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Per User Limit</label>
                                <input type="number" class="form-agri per_user_limit" placeholder="1" min="1" step="1" value="1">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Vector Expiration Boundary <span class="text-danger">*</span></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-agri date_picker" placeholder="MM/DD/YYYY" style="border-top-right-radius: 0; border-bottom-right-radius: 0; font-weight: 700;"/>
                                    <span class="input-group-text" style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-left: none; border-radius: 0 12px 12px 0;">
                                        <i class="fas fa-calendar-alt" style="color: var(--agri-primary);"></i>
                                    </span>
                                </div>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Set the date when this coupon ends.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Target & Visibility --}}
                    <div style="margin-bottom: 40px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 32px; height: 32px; background: #ECFDF5; color: #059669; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-crosshairs"></i>
                            </div>
                            Eligibility & Use
                        </h4>

                        <div class="row g-4">
                            @if($id == '')
                            <div class="col-md-12">
                                <label class="agri-label">Geospatial Origin / Fulfillment Partner</label>
                                <select id="vendor_restaurant_select" class="form-agri select2">
                                            <option value="">-- Select Vendor --</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->title }}</option>
                                            @endforeach
                                        </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Limit this coupon to one vendor, or leave it open to all.</div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s; cursor: pointer;" onclick="document.getElementById('coupon_public').click();" onmouseover="this.style.borderColor='var(--agri-primary)'" onmouseout="this.style.borderColor='var(--agri-border)'">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0; pointer-events: none;">
                                        <label class="agri-label mb-0" style="margin-bottom: 0;">
                                            <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: none;">Show to all users</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Apply this coupon to all logged-in users.</div>
                                        </label>
                                        <input type="checkbox" name="is_visible_to_all" class="form-check-input coupon_public" id="coupon_public" style="width: 44px; height: 24px; pointer-events: auto;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s; cursor: pointer;" onclick="document.getElementById('coupon_enabled').click();" onmouseover="this.style.borderColor='var(--agri-primary)'" onmouseout="this.style.borderColor='var(--agri-border)'">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0; pointer-events: none;">
                                        <label class="agri-label mb-0" style="margin-bottom: 0;">
                                            <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: none;">Immediate Activation</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Make the coupon available right away.</div>
                                        </label>
                                        <input type="checkbox" name="is_active" class="form-check-input coupon_enabled" id="coupon_enabled" checked style="width: 44px; height: 24px; pointer-events: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 32px; height: 32px; background: #EFF6FF; color: #1D4ED8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-link"></i>
                            </div>
                            Eligibility Scope
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="agri-label">Applicable Products</label>
                                <select id="product_ids" class="form-agri select2" multiple>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="agri-label">Applicable Categories</label>
                                <select id="category_ids" class="form-agri select2" multiple>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="agri-label">Applicable Vendors</label>
                                <select id="vendor_ids" class="form-agri select2" multiple>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div style="margin-bottom: 0; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <label class="agri-label">Notes</label>
                        <textarea rows="4" class="form-agri coupon_description" id="coupon_description" placeholder="Write a short note about this coupon..." style="font-weight: 500; font-size: 13px;"></textarea>
                        <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Add a short note for your team.</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Image Upload Card --}}
            <div class="card-agri" style="padding: 24px; position: sticky; top: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <i class="fas fa-image me-2 text-muted"></i> Coupon Image
                </h4>
                
                <div style="border: 2px dashed var(--agri-border); border-radius: 16px; padding: 40px 20px; text-align: center; background: var(--agri-bg); transition: 0.3s;" id="drop-zone">
                    <div class="placeholder_img_thumb coupon_image" style="margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background: white; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; color: var(--agri-text-muted); font-size: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600; margin-bottom: 24px; padding: 0 20px;">Upload an image for this coupon.</p>
                    <label class="btn-agri" style="cursor: pointer; display: inline-block; background: white; border: 1px solid var(--agri-border); color: var(--agri-text-heading); font-weight: 700; padding: 10px 20px;">
                        <i class="fas fa-folder-open" style="margin-right: 8px;"></i> Browse Files
                        <input type="file" onChange="handleFileSelect(event)" style="display: none;">
                    </label>
                </div>
                
                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--agri-border);">
                    <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="width: 100%; height: 52px; font-weight: 800; font-size: 14px; margin-bottom: 12px; letter-spacing: 1px;">
                        <i class="fas fa-satellite-dish" style="margin-right: 8px;"></i> SAVE COUPON
                    </button>
                    @if($id != '')
                        <a href="{{route('admin.coupons')}}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">Cancel</a>
                    @else
                        <a href="{!! route('admin.coupons') !!}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">Cancel</a>
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
<script>
    var csrfToken = '{{ csrf_token() }}';

    $(document).ready(function () {
        $(".save-form-btn").click(function () {
            var code = $(".coupon_code").val().trim();
            var discountType = $("#coupon_discount_type").val();
            var value = $(".coupon_discount").val().trim();
            var vendorId = $("#vendor_restaurant_select").val();
            var expiresAt = $(".date_picker").val().trim();
            var startsAt = $(".starts_at").val();
            var isActive = $(".coupon_enabled").is(":checked") ? 1 : 0;
            var isVisibleToAll = $(".coupon_public").is(":checked") ? 1 : 0;
            var minOrder = $(".min_order").val();
            var maxDiscount = $(".max_discount").val();
            var usageLimit = $(".usage_limit").val();
            var perUserLimit = $(".per_user_limit").val();
            var productIds = $("#product_ids").val() || [];
            var categoryIds = $("#category_ids").val() || [];
            var vendorIds = $("#vendor_ids").val() || [];

            if (!code) {
                $(".error_top").show().html("<p>Please enter a coupon code.</p>");
                window.scrollTo(0, 0);
                return;
            }
            if (!value) {
                $(".error_top").show().html("<p>Please enter a discount value.</p>");
                window.scrollTo(0, 0);
                return;
            }

            jQuery("#data-table_processing").show();

            // Map display type to DB type
            var typeMap = { 'Percentage': 'percentage', 'Fix Price': 'fixed', 'percentage': 'percentage', 'fixed': 'fixed' };
            var dbType = typeMap[discountType] || discountType.toLowerCase();

            $.ajax({
                url: '{{ route("admin.coupons.store") }}',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    code: code,
                    type: dbType,
                    value: value,
                    vendor_id: vendorId || null,
                    starts_at: startsAt || null,
                    expires_at: expiresAt || null,
                    is_active: isActive,
                    is_visible_to_all: isVisibleToAll,
                    min_order: minOrder || null,
                    max_discount: maxDiscount || null,
                    usage_limit: usageLimit || null,
                    per_user_limit: perUserLimit || null,
                    product_ids: productIds,
                    category_ids: categoryIds,
                    vendor_ids: vendorIds
                },
                success: function (res) {
                    jQuery("#data-table_processing").hide();
                    if (res.success) {
                        window.location.href = res.redirect || '{{ route("admin.coupons") }}';
                    } else {
                        $(".error_top").show().html("<p>" + (res.message || 'Failed') + "</p>");
                        window.scrollTo(0, 0);
                    }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var errors = xhr.responseJSON;
                    var msg = (errors && errors.message) ? errors.message : 'Server error';
                    if (errors && errors.errors) {
                        msg = Object.values(errors.errors).flat().join('<br>');
                    }
                    $(".error_top").show().html("<p>" + msg + "</p>");
                    window.scrollTo(0, 0);
                }
            });
        });
    });
</script>
@endsection
