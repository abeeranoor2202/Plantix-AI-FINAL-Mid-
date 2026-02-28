@extends('layouts.app') 

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.categories') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Taxonomic Registry</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.category_create')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Instantiate Classification Node</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create a new taxonomic branch to organize platform products and services.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                
                {{-- Navigation Tabs --}}
                <div style="background: var(--agri-bg); border-bottom: 1px solid var(--agri-border); padding: 0 40px;">
                    <ul class="nav nav-tabs border-0" role="tablist" style="gap: 32px;">
                        <li class="nav-item">
                            <a href="#category_information" class="nav-link active border-0 py-4 px-0" data-toggle="tab" style="background: transparent; color: var(--agri-primary); font-weight: 800; font-size: 14px; border-bottom: 3px solid var(--agri-primary) !important; border-radius: 0; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-layer-group"></i> {{trans('lang.category_information')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#review_attributes" class="nav-link border-0 py-4 px-0" data-toggle="tab" style="background: transparent; color: var(--agri-text-muted); font-weight: 800; font-size: 14px; border-radius: 0; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-sliders-h"></i> {{trans('lang.reviewattribute_plural')}}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); z-index: 100; position: absolute; top:0; left:0; right:0; bottom:0; display: flex; align-items: center; justify-content: center;">
                        <div class="spinner-border text-success" role="status"></div>
                    </div>
                    
                    <div class="alert alert-danger error_top m-4" style="display:none; border-radius: 12px; border: none; background: #FEF2F2; color: var(--agri-error); font-weight: 700;"></div>

                    <div class="tab-content" style="padding: 40px;">
                        {{-- Category Info Tab --}}
                        <div role="tabpanel" class="tab-pane active" id="category_information">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="agri-label">{{trans('lang.category_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-agri cat-name" placeholder="e.g. Organic Fertilizers" required style="height: 52px; font-size: 16px; font-weight: 600;">
                                </div>

                                <div class="col-md-12">
                                    <label class="agri-label">{{trans('lang.category_description')}}</label>
                                    <textarea rows="4" class="category_description form-agri" id="category_description" placeholder="Provide a detailed classification scope..." style="padding: 16px;"></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="agri-label">Primary Node Representation (Image)</label>
                                    <div style="background: var(--agri-bg); border: 2px dashed var(--agri-border); border-radius: 20px; padding: 32px; text-align: center; position: relative; transition: 0.3s;" id="drop-zone">
                                        <div id="uploding_image" style="font-weight: 800; color: var(--agri-primary); margin-bottom: 12px;"></div>
                                        <div class="cat_image mb-3 d-flex justify-content-center"></div>
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                            <div style="width: 54px; height: 54px; border-radius: 50%; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div>
                                                <p style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">Click to select node visual</p>
                                                <p style="margin: 4px 0 0 0; color: var(--agri-text-muted); font-size: 12px;">Recommended: 512x512px SVG or PNG</p>
                                            </div>
                                            <input type="file" id="category_image" style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-5">
                                    <h5 style="font-size: 15px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-eye" style="color: var(--agri-primary);"></i> Visibility & Discovery
                                    </h5>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div style="background: var(--agri-bg); padding: 16px 20px; border-radius: 16px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: white; color: var(--agri-primary); display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-globe"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px;">{{trans('lang.item_publish')}}</div>
                                                        <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Active in ecosystem</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input item_publish" type="checkbox" id="item_publish" style="width: 44px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="background: var(--agri-bg); padding: 16px 20px; border-radius: 16px; border: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: white; color: var(--agri-secondary); display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px;">{{trans('lang.show_in_home')}}</div>
                                                        <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 600;">Featured on explorer</div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" id="show_in_homepage" style="width: 44px; height: 22px; cursor: pointer;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Review Attributes Tab --}}
                        <div role="tabpanel" class="tab-pane" id="review_attributes">
                            <div style="text-align: center; margin-bottom: 32px;">
                                <div style="width: 60px; height: 60px; background: var(--agri-bg); color: var(--agri-secondary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px;">
                                    <i class="fas fa-poll-h"></i>
                                </div>
                                <h4 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading);">Sentiment Parameters</h4>
                                <p style="color: var(--agri-text-muted); max-width: 500px; margin: 8px auto 0 auto; font-size: 13px;">Define which specific attributes users can rate when reviewing products within this classification.</p>
                            </div>
                            <div id="review_attributes_list" class="row g-3">
                                {{-- Dynamically populated --}}
                            </div>
                        </div>
                    </div>

                    {{-- Action Bar --}}
                    <div style="padding: 32px 40px; background: var(--agri-bg); border-top: 1px solid var(--agri-border); display: flex; justify-content: flex-end; gap: 16px;">
                        <a href="{!! route('admin.categories') !!}" class="btn-agri btn-agri-outline" style="padding: 12px 32px; text-decoration: none; font-weight: 700; min-width: 140px; display: flex; align-items: center; justify-content: center;">{{trans('lang.cancel')}}</a>
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="padding: 12px 48px; font-weight: 800; font-size: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle"></i> Commit Node
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .nav-tabs .nav-link:hover { color: var(--agri-primary) !important; border-bottom: 3px solid var(--agri-primary)20 !important; }
    #drop-zone:hover { border-color: var(--agri-primary); background: white; }
    .attribute-card { background: white; border: 1px solid var(--agri-border); border-radius: 14px; padding: 16px 20px; transition: 0.2s; cursor: pointer; display: flex; align-items: center; gap: 12px; }
    .attribute-card:hover { border-color: var(--agri-primary); background: var(--agri-primary-light); }
    .attribute-card input:checked + .attr-label { color: var(--agri-primary) !important; font-weight: 800 !important; }
</style>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var ref = database.collection('vendor_categories');
    var photo = "";
    var fileName='';
    var id_category = "<?php echo uniqid();?>";
    var placeholderImage = '';
    var ref_review_attributes = database.collection('review_attributes');

    $(document).ready(function () {
        // Load attributes
        ref_review_attributes.get().then(async function (snapshots) {
            var ra_html = '';
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                ra_html += '<div class="col-md-6 col-lg-4"><label class="attribute-card m-0">' +
                           '<input type="checkbox" class="form-check-input" value="' + data.id + '" style="margin:0; width:18px; height:18px;">' +
                           '<span class="attr-label" style="font-size:13px; font-weight:600; color:var(--agri-text-heading);">' + data.title + '</span>' +
                           '</label></div>';
            });
            $('#review_attributes_list').html(ra_html);
        });

        $(".save-form-btn").click(async function () {
            var title = $(".cat-name").val();
            var description = $(".category_description").val();
            var item_publish = $("#item_publish").is(":checked");
            var show_in_homepage = $("#show_in_homepage").is(":checked");

            var review_attributes = [];
            $('#review_attributes_list input:checked').each(function () {
                review_attributes.push($(this).val());
            });

            if (title == '') {
                $(".error_top").show().html("<p>{{trans('lang.enter_cat_title_error')}}</p>");
                window.scrollTo(0, 0);
                return;
            }

            if (show_in_homepage) {
                var homeCount = await database.collection('vendor_categories').where('show_in_homepage', "==", true).get();
                if (homeCount.docs.length >= 5) {
                    alert("Quota limit reached: Already 5 categories are active for homepage featured status.");
                    return;
                }
            }

            jQuery("#data-table_processing").show();

            try {
                var IMG = await storeImageData();
                await database.collection('vendor_categories').doc(id_category).set({
                    'id': id_category,
                    'title': title,
                    'description': description,
                    'photo': IMG,
                    'review_attributes': review_attributes,
                    'publish': item_publish,
                    'show_in_homepage': show_in_homepage,
                });
                window.location.href = '{{ route("admin.categories")}}';
            } catch (error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>" + error + "</p>");
            }
        });
    });

    // Image logic (simplified for clean code)
    var storageRef = firebase.storage().ref('images');
    async function storeImageData() {
        if (!photo || !photo.includes('base64')) return photo;
        var p = photo.replace(/^data:image\/[a-z]+;base64,/, "");
        var uploadTask = await storageRef.child(fileName).putString(p, 'base64', {contentType: 'image/jpg'});
        return await uploadTask.ref.getDownloadURL();
    }

    $("#category_image").resizeImg({
        callback: function(base64str) {
            var val = $('#category_image').val().toLowerCase();
            var ext = val.split('.').pop();
            var timestamp = Number(new Date());
            fileName = "cat_" + timestamp + "." + ext;
            photo = base64str;
            $(".cat_image").html('<img src="' + photo + '" style="width:100px; height:100px; border-radius:12px; object-fit:cover; border:2px solid white; box-shadow:0 10px 20px rgba(0,0,0,0.1);">');
            $("#uploding_image").text("Visual Node Prepared");
        }
    });

</script>
@endsection