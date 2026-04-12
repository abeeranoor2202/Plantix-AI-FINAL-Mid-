@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{route('admin.coupons')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_plural')}}</a>
            <?php } else { ?>
                <a href="{!! route('admin.coupons') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_plural')}}</a>
            <?php } ?>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.coupon_edit')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.coupon_edit')}}</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update the coupon details, rules, and image.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); z-index: 10; position: absolute; top:0; left:0; width:100%; height:100%; border-radius: 16px; align-items: center; justify-content: center;">
                    <div class="spinner-border spinner-border-sm text-success"></div> {{trans('lang.processing')}}
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
                                <label class="agri-label">{{trans('lang.coupon_code')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri coupon_code" placeholder="e.g. SAVE20" style="text-transform: uppercase; font-family: 'Courier New', Courier, monospace; font-weight: 800; font-size: 16px; letter-spacing: 2px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ trans("lang.coupon_code_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_discount_type')}}</label>
                                <select id="coupon_discount_type" class="form-agri" style="font-weight: 700;">
                                    <option value="Percentage">{{trans('lang.coupon_percent')}}</option>
                                    <option value="Fix Price">{{trans('lang.coupon_fixed')}}</option>
                                </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ trans("lang.coupon_discount_type_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_discount')}} <span class="text-danger">*</span></label>
                                <input type="number" class="form-agri coupon_discount" placeholder="0" style="font-weight: 800; font-size: 16px;">
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ trans("lang.coupon_discount_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Start Date</label>
                                <input type="date" class="form-agri starts_at" style="font-weight: 700;">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Minimum Cart Value</label>
                                <input type="number" class="form-agri min_order" min="0" step="0.01" placeholder="0" style="font-weight: 700;">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Max Discount Cap</label>
                                <input type="number" class="form-agri max_discount" min="0" step="0.01" placeholder="0" style="font-weight: 700;">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Global Usage Limit</label>
                                <input type="number" class="form-agri usage_limit" min="1" step="1" placeholder="Optional" style="font-weight: 700;">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">Per User Limit</label>
                                <input type="number" class="form-agri per_user_limit" min="1" step="1" placeholder="1" style="font-weight: 700;">
                            </div>

                            <div class="col-md-6">
                                <label class="agri-label">{{trans('lang.coupon_expires_at')}} <span class="text-danger">*</span></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-agri date_picker" placeholder="MM/DD/YYYY" style="border-top-right-radius: 0; border-bottom-right-radius: 0; font-weight: 700;"/>
                                    <span class="input-group-text" style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-left: none; border-radius: 0 12px 12px 0;">
                                        <i class="fas fa-calendar-alt" style="color: var(--agri-primary);"></i>
                                    </span>
                                </div>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ trans("lang.coupon_expires_at_help") }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 32px; height: 32px; background: #EFF6FF; color: #1D4ED8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-link"></i>
                            </div>
                            Eligibility & Scope
                        </h4>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="agri-label">Applicable Products</label>
                                <select id="product_ids" class="form-agri select2" multiple>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ $coupon->products->pluck('id')->contains($product->id) ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="agri-label">Applicable Categories</label>
                                <select id="category_ids" class="form-agri select2" multiple>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $coupon->categories->pluck('id')->contains($category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="agri-label">Applicable Vendors</label>
                                <select id="vendor_ids" class="form-agri select2" multiple>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ $coupon->applicableVendors->pluck('id')->contains($vendor->id) ? 'selected' : '' }}>{{ $vendor->title }}</option>
                                    @endforeach
                                </select>
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
                            <div class="col-md-12">
                                <label class="agri-label">{{trans('lang.coupon_store_id')}}</label>
                                <select id="vendor_restaurant_select" class="form-agri select2">
                                            <option value="">-- Select Vendor --</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ $coupon->vendor_id == $vendor->id ? "selected" : "" }}>{{ $vendor->title }}</option>
                                            @endforeach
                                        </select>
                                <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted);">{{ trans("lang.coupon_store_id_help") }}</div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s;">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0;">
                                        <label class="agri-label mb-0" for="coupon_public" style="cursor: pointer;">
                                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{trans('lang.coupon_public')}}</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Visible to all customers</div>
                                        </label>
                                        <label class="switch" for="coupon_public" style="flex-shrink: 0;">
                                            <input type="checkbox" name="is_visible_to_all" class="coupon_public" id="coupon_public" {{ $coupon->is_visible_to_all ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div style="background: var(--agri-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--agri-border); transition: 0.2s;">
                                    <div class="form-check form-switch" style="padding-left: 0; display: flex; justify-content: space-between; align-items: center; margin: 0;">
                                        <label class="agri-label mb-0" for="coupon_enabled" style="cursor: pointer;">
                                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{trans('lang.coupon_enabled')}}</div>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--agri-text-muted); margin-top: 4px; text-transform: none; letter-spacing: 0;">Status: Active or inactive</div>
                                        </label>
                                        <label class="switch" for="coupon_enabled" style="flex-shrink: 0;">
                                            <input type="checkbox" name="is_active" class="coupon_enabled" id="coupon_enabled" {{ $coupon->is_active ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div style="margin-bottom: 0; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                        <label class="agri-label">{{trans('lang.coupon_description')}}</label>
                        <textarea rows="4" class="form-agri coupon_description" id="coupon_description" placeholder="Write a short note about this coupon..." style="font-weight: 500; font-size: 13px;"></textarea>
                        <div class="form-text mt-2" style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">{{ trans("lang.coupon_description_help") }}</div>
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
                            <i class="fas fa-image"></i>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600; margin-bottom: 24px; padding: 0 20px;">Upload an image for this coupon.</p>
                    <label class="btn-agri" style="cursor: pointer; display: inline-block; background: white; border: 1px solid var(--agri-border); color: var(--agri-text-heading); font-weight: 700; padding: 10px 20px;">
                        <i class="fas fa-folder-open" style="margin-right: 8px;"></i> Change Image
                        <input type="file" onChange="handleFileSelect(event)" style="display: none;">
                    </label>
                </div>
                
                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--agri-border);">
                    <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="width: 100%; height: 52px; font-weight: 800; font-size: 14px; margin-bottom: 12px; letter-spacing: 1px;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> UPDATE COUPON
                    </button>
                    <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                        <a href="{{route('admin.coupons')}}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">{{trans('lang.cancel')}}</a>
                    <?php } else { ?>
                        <a href="{!! route('admin.coupons') !!}" class="btn-agri" style="width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; background: var(--agri-bg); color: var(--agri-text-muted); border: 1px solid var(--agri-border);">{{trans('lang.cancel')}}</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 13px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 10px; display: block; }
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .switch input:checked + .slider {
        background-color: var(--agri-primary);
    }
    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }
</style>
@endsection

@section('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';
    var couponId = '{{ $coupon->id }}';

    $(document).ready(function () {
        // Pre-fill coupon fields
        $(".coupon_code").val('{{ $coupon->code }}');
        var typeVal = '{{ $coupon->type }}';  // 'percentage' or 'fixed'
        var displayType = typeVal === 'percentage' ? 'Percentage' : 'Fix Price';
        $("#coupon_discount_type").val(displayType).trigger('change');
        $(".coupon_discount").val('{{ $coupon->value }}');
        $(".starts_at").val('{{ optional($coupon->starts_at)->format("Y-m-d") }}');
        $(".min_order").val('{{ $coupon->min_order }}');
        $(".max_discount").val('{{ $coupon->max_discount }}');
        $(".usage_limit").val('{{ $coupon->usage_limit }}');
        $(".per_user_limit").val('{{ $coupon->per_user_limit ?? 1 }}');
        @if($coupon->expires_at)
        $(".date_picker").val('{{ \Carbon\Carbon::parse($coupon->expires_at)->format("Y-m-d") }}');
        @endif
        $("#coupon_public").prop('checked', {{ $coupon->is_visible_to_all ? 'true' : 'false' }});
        $("#coupon_enabled").prop('checked', {{ $coupon->is_active ? 'true' : 'false' }});

        $(".save-form-btn, .edit-form-btn").click(function () {
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

            jQuery("#data-table_processing").show();

            var typeMap = { 'Percentage': 'percentage', 'Fix Price': 'fixed', 'percentage': 'percentage', 'fixed': 'fixed' };
            var dbType = typeMap[discountType] || discountType.toLowerCase();

            $.ajax({
                url: '{{ url("admin/coupons/update") }}/' + couponId,
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
                        window.location.href = '{{ route("admin.coupons") }}';
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
