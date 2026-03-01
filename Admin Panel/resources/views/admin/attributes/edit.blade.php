@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{!! route('admin.attributes') !!}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.item_attribute_plural')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.attribute_edit')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.attribute_edit')}}</h1>
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
                        <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 24px;">Modify Attribute Details</h4>
                        <div class="row g-4">
                            <div class="col-12">
                                <label style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); margin-bottom: 8px; display: block;">{{trans('lang.attribute_name')}} <span class="text-danger">*</span></label>
                                <input type="text" class="form-agri attribute-name" placeholder="e.g. Color, Size, Voltage, Material">
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 6px;">{{ trans("lang.attribute_name_help") }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                        <button type="button" class="btn-agri btn-agri-primary edit-form-btn" style="flex: 2; height: 48px; font-size: 15px;">
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
    var attributeId = '{{ $attribute->id }}';

    $(document).ready(function () {
        // Pre-fill existing title
        $(".attribute-name").val('{{ addslashes($attribute->title) }}');

        $(".save-form-btn").click(function () {
            var title = $(".attribute-name").val().trim();
            if (!title) {
                $(".error_top").show().html("<p>Please enter an attribute title.</p>");
                window.scrollTo(0, 0);
                return;
            }

            jQuery("#data-table_processing").show();
            $.ajax({
                url: '{{ url("admin/attributes/update") }}/' + attributeId,
                method: 'POST',
                data: { _token: csrfToken, title: title },
                success: function (res) {
                    jQuery("#data-table_processing").hide();
                    if (res.success) {
                        window.location.href = '{{ route("admin.attributes") }}';
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
    });
</script>
@endsection
