@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.attributes') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.item_attribute_plural')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.attribute_create')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.attribute_create')}}</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card-agri" style="padding: 40px;">
                
                <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700;">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    {{trans('lang.processing')}}
                </div>

                <div class="error_top" style="display:none; background: var(--agri-error-light); color: var(--agri-error); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;"></div>

                <form>
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 24px;">General Information</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.attribute_name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri attribute-name" placeholder="e.g. Weight, NPK Ratio, Soil Type">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 6px;">{{ trans("lang.attribute_name_help") }}</div>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Attribute Type <span class="text-danger">*</span></label>
                                <select class="form-agri attribute-type">
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="select">Select</option>
                                    <option value="multi-select">Multi-Select</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Unit (Optional)</label>
                                <input type="text" class="form-agri attribute-unit" placeholder="e.g. kg, %, ml">
                            </div>
                            <div class="col-12" id="attribute-values-wrap" style="display:none;">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">Predefined Values</label>
                                <textarea class="form-agri attribute-values" rows="4" placeholder="One value per line&#10;Loamy&#10;Sandy&#10;Clay"></textarea>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 6px;">Used for select and multi-select attributes.</div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary save-form-btn" style="flex: 2; height: 48px; font-size: 15px;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> {{trans('lang.save')}}
                        </button>
                        <a href="{!! route('admin.attributes') !!}" class="btn-agri btn-agri-outline" style="flex: 1; height: 48px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 15px;">
                             {{trans('lang.cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';

    $(document).ready(function () {
        $(".save-form-btn").click(function () {
            var name = $(".attribute-name").val().trim();
            var type = $(".attribute-type").val();
            var unit = $(".attribute-unit").val().trim();
            var values = $(".attribute-values").val().split('\n').map(function(v){ return v.trim(); }).filter(Boolean);

            if (!name) {
                $(".error_top").show().html("<p>Please enter an attribute title.</p>");
                window.scrollTo(0, 0);
                return;
            }

            if ((type === 'select' || type === 'multi-select') && values.length === 0) {
                $(".error_top").show().html("<p>Please add at least one predefined value for this type.</p>");
                window.scrollTo(0, 0);
                return;
            }

            jQuery("#data-table_processing").show();
            $.ajax({
                url: '{{ route("admin.attributes.store") }}',
                method: 'POST',
                data: { _token: csrfToken, name: name, type: type, unit: unit, values: values },
                success: function (res) {
                    jQuery("#data-table_processing").hide();
                    if (res.success) {
                        window.location.href = res.redirect || '{{ route("admin.attributes") }}';
                    } else {
                        $(".error_top").show().html("<p>" + (res.message || 'Failed') + "</p>");
                        window.scrollTo(0, 0);
                    }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
                    $(".error_top").show().html("<p>" + msg + "</p>");
                    window.scrollTo(0, 0);
                }
            });
        });

        function toggleValuesInput() {
            var type = $(".attribute-type").val();
            $("#attribute-values-wrap").toggle(type === 'select' || type === 'multi-select');
        }

        $(".attribute-type").on('change', toggleValuesInput);
        toggleValuesInput();
    });
</script>
@endsection
