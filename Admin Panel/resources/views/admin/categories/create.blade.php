@extends('layouts.app') 

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.categories') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Category List</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.category_create')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Create Category</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Add a new category to organize products and services.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card-agri" style="padding: 40px;">
                
                {{-- Navigation Tabs --}}
                <div style="margin-bottom: 32px; border-bottom: 1px solid var(--agri-border);">
                    <ul class="nav nav-tabs border-0 category-tabs" role="tablist">
                        <li class="nav-item">
                            <a href="#category_information" class="nav-link active" data-toggle="tab">
                                <i class="fas fa-layer-group"></i> {{trans('lang.category_information')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#review_attributes" class="nav-link" data-toggle="tab">
                                <i class="fas fa-sliders-h"></i> {{trans('lang.reviewattribute_plural')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#category_attributes" class="nav-link" data-toggle="tab">
                                <i class="fas fa-list-check"></i> Category Attributes
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="position-relative">
                    <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.85); z-index: 100; position: absolute; top:0; left:0; right:0; bottom:0; align-items: center; justify-content: center; color: var(--agri-primary); font-weight: 700;">
                        <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                        {{trans('lang.processing')}}
                    </div>
                    
                    <div class="error_top" style="display:none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

                    <div class="tab-content">
                        {{-- Category Info Tab --}}
                        <div role="tabpanel" class="tab-pane active" id="category_information">
                            <div style="margin-bottom: 32px;">
                                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-folder"></i> Category Details
                                </h4>

                                <div class="row g-4">
                                    <div class="col-md-12">
                                    <label class="agri-label">{{trans('lang.category_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-agri cat-name" placeholder="e.g. Organic Fertilizers" required>
                                    </div>

                                    <div class="col-md-12">
                                    <label class="agri-label">{{trans('lang.category_description')}}</label>
                                    <textarea rows="4" class="category_description form-agri" id="category_description" placeholder="Write a short category description..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-bottom: 32px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px;">Category Media</h4>
                                <label class="agri-label">Category Image</label>
                                <div style="background: white; border: 2px dashed var(--agri-border); border-radius: 16px; padding: 28px; text-align: center; position: relative; transition: 0.3s;" id="drop-zone">
                                        <div id="uploding_image" style="font-weight: 800; color: var(--agri-primary); margin-bottom: 12px;"></div>
                                        <div class="cat_image mb-3 d-flex justify-content-center"></div>
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                            <div style="width: 54px; height: 54px; border-radius: 50%; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div>
                                                <p style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">Click to select image</p>
                                                <p style="margin: 4px 0 0 0; color: var(--agri-text-muted); font-size: 12px;">Recommended: 512x512px SVG or PNG</p>
                                            </div>
                                            <input type="file" id="category_image" style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                                        </div>
                                </div>
                            </div>

                            <div style="margin-bottom: 32px; background: #fffbeb; padding: 24px; border-radius: 16px; border: 1px solid #fde68a;">
                                <h5 style="font-size: 15px; font-weight: 700; color: #92400e; margin-bottom: 14px;">Category Settings</h5>

                                <div class="row g-4" style="margin-bottom: 8px;">
                                    <div class="col-md-6" style="display:flex; align-items:center; justify-content:space-between;">
                                        <div>
                                            <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading); display:block;">{{trans('lang.item_publish')}}</span>
                                            <span style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Visible to users</span>
                                        </div>
                                        <label class="switch">
                                            <input class="item_publish" type="checkbox" id="item_publish">
                                            <span class="slider"></span>
                                        </label>
                                    </div>

                                    <div class="col-md-6" style="display:flex; align-items:center; justify-content:space-between;">
                                        <div>
                                            <span style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading); display:block;">{{trans('lang.show_in_home')}}</span>
                                            <span style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Show on home page</span>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" id="show_in_homepage">
                                            <span class="slider"></span>
                                        </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- Review Attributes Tab --}}
                        <div role="tabpanel" class="tab-pane" id="review_attributes">
                            <div style="margin-bottom: 24px;">
                                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-poll-h"></i> Review Options
                                </h4>
                                <p style="color: var(--agri-text-muted); margin: 0; font-size: 13px;">Choose which fields users can rate in product reviews for this category.</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 16px; padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                                        <div>
                                            <div style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Text Review</div>
                                            <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Show the written review field on product pages.</div>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" id="text_review_enabled" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 16px; padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                                        <div>
                                            <div style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Picture / Image Review</div>
                                            <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Allow customers to attach review photos.</div>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" id="image_review_enabled">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="category_attributes">
                            <div style="margin-bottom: 24px;">
                                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-list-check"></i> Select Attributes for Category
                                </h4>
                                <p style="color: var(--agri-text-muted); margin: 0; font-size: 13px;">Choose which product attributes belong to this category and mark required fields.</p>
                            </div>

                            <div class="row g-3" id="category_attribute_list">
                                @forelse($attributes as $attr)
                                    @php($attrName = $attr->name ?: $attr->title)
                                    <div class="col-md-6">
                                        <div style="background: white; border: 1px solid var(--agri-border); border-radius: 14px; padding: 16px;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px;">
                                                <label style="display:flex; align-items:center; gap:10px; margin:0; font-weight:700; color:var(--agri-text-heading); cursor:pointer;">
                                                    <input type="checkbox" class="category-attribute-check" value="{{ $attr->id }}" style="width:16px; height:16px;">
                                                    <span>{{ $attrName }}</span>
                                                </label>
                                                <span style="font-size:11px; font-weight:700; color:var(--agri-text-muted); text-transform:uppercase;">{{ $attr->type }}</span>
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:10px; border-top:1px dashed var(--agri-border);">
                                                <span style="font-size:12px; color:var(--agri-text-muted);">Required for products</span>
                                                <label class="switch" style="transform:scale(.9);">
                                                    <input type="checkbox" class="category-attribute-required" value="{{ $attr->id }}" disabled>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div style="padding:16px; border-radius:12px; background:#FEF9C3; color:#854D0E; font-weight:600;">No attributes available yet. Create attributes first, then assign them to categories.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Action Bar --}}
                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="flex: 2; height: 50px; font-size: 16px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save Category
                        </button>
                        <a href="{!! route('admin.categories') !!}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                            {{trans('lang.cancel')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .category-tabs {
        gap: 32px;
        background: transparent !important;
        border-bottom: 0 !important;
    }
    .category-tabs .nav-item {
        background: transparent !important;
        margin-bottom: 0;
    }
    .category-tabs .nav-link {
        background: transparent !important;
        background-color: transparent !important;
        color: var(--agri-text-muted);
        font-weight: 800;
        font-size: 14px;
        border: 0 !important;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 0;
        margin-bottom: 0;
        box-shadow: none !important;
    }
    .category-tabs .nav-link.active {
        color: var(--agri-primary);
        background: transparent !important;
        background-color: transparent !important;
        border-color: transparent !important;
        border-bottom-color: var(--agri-primary);
    }
    .category-tabs .nav-link:hover {
        color: var(--agri-primary);
        border-bottom-color: rgba(71, 142, 60, 0.25);
    }
    #drop-zone:hover { border-color: var(--agri-primary); background: white; }
    .attribute-card { background: white; border: 1px solid var(--agri-border); border-radius: 14px; padding: 16px 20px; transition: 0.2s; cursor: pointer; display: flex; align-items: center; gap: 12px; }
    .attribute-card:hover { border-color: var(--agri-primary); background: var(--agri-primary-light); }
    .attribute-card input:checked + .attr-label { color: var(--agri-primary) !important; font-weight: 800 !important; }
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

    @media (max-width: 768px) {
        .category-tabs { gap: 18px; }
        .category-tabs .nav-link { font-size: 13px; }
    }
</style>
@endsection

@section('scripts')
<script>
    var photo = "";
    var fileName = '';
    var csrfToken = '{{ csrf_token() }}';

    $(".save-form-btn").click(async function () {
        var title = $(".cat-name").val();
        var description = $(".category_description").val();
        var item_publish = $("#item_publish").is(":checked");
        var text_review_enabled = $("#text_review_enabled").is(":checked");
        var image_review_enabled = $("#image_review_enabled").is(":checked");
        var category_attributes = [];
        $('.category-attribute-check:checked').each(function () { category_attributes.push($(this).val()); });
        var required_attributes = [];
        $('.category-attribute-required:checked').each(function () { required_attributes.push($(this).val()); });

        if (title == '') {
            $(".error_top").show().html("<p>{{trans('lang.enter_cat_title_error')}}</p>");
            window.scrollTo(0, 0); return;
        }

            jQuery("#data-table_processing").css('display', 'flex');

        try {
            $.ajax({
                url: '{{ route("admin.categories.store") }}',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    name: title,
                    description: description,
                    active: item_publish ? 1 : 0,
                    text_review_enabled: text_review_enabled ? 1 : 0,
                    image_review_enabled: image_review_enabled ? 1 : 0,
                    category_attributes: category_attributes,
                    required_attributes: required_attributes,
                    image_base64: photo || ''
                },
                success: function (res) {
                    if (res.success) window.location.href = res.redirect;
                    else { jQuery("#data-table_processing").hide(); $(".error_top").show().html("<p>Error saving category.</p>"); }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                    $(".error_top").show().html("<p>" + msg + "</p>"); window.scrollTo(0, 0);
                }
            });
        } catch (error) {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html("<p>" + error + "</p>");
        }
    });

    $("#category_image").resizeImg({
        callback: function(base64str) {
            var val = $('#category_image').val().toLowerCase();
            var ext = val.split('.').pop();
            fileName = "cat_" + Number(new Date()) + "." + ext;
            photo = base64str;
            $(".cat_image").html('<img src="' + photo + '" style="width:100px;height:100px;border-radius:12px;object-fit:cover;border:2px solid white;box-shadow:0 10px 20px rgba(0,0,0,0.1);">');
            $("#uploding_image").text("Image ready");
        }
    });

    $('.category-attribute-check').on('change', function () {
        var id = $(this).val();
        var req = $('.category-attribute-required[value="' + id + '"]');
        req.prop('disabled', !$(this).is(':checked'));
        if (!$(this).is(':checked')) {
            req.prop('checked', false);
        }
    });
</script>
@endsection