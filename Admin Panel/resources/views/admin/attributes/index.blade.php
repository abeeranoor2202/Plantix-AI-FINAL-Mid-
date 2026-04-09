@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.item_attribute_plural')}}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Categorize and define unique characteristics for vendor products.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{!! route('admin.attributes.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i>
                {{trans('lang.attribute_create')}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-1 px-4">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <a href="{!! url()->current() !!}" style="text-decoration: none; color: var(--agri-primary); font-weight: 700; font-size: 15px; border-bottom: 3px solid var(--agri-primary); padding-bottom: 12px;">
                            <i class="fas fa-list" style="margin-right: 8px;"></i>
                            {{trans('lang.attribute_table')}}
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.8); color: var(--agri-primary); font-weight: 700;">
                        <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                        {{trans('lang.processing')}}
                    </div>

                    <div class="table-responsive">
                        <table id="attributeTable" class="table mb-0" style="vertical-align: middle;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.attribute_name')}}</th>
                                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: right;">{{trans('lang.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody id="append_list1">
                            @forelse($attributes as $attribute)
                            <tr>
                                <td>{{ $attribute->title }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn-agri btn-agri-outline me-1" style="border-radius:8px;font-weight:700; text-decoration: none; padding: 6px 12px;">
                                        <i class="fas fa-edit me-1"></i>{{ trans('lang.edit') }}
                                    </a>
                                    <button class="btn-agri delete-attr-btn" data-id="{{ $attribute->id }}" style="border-radius:8px;font-weight:700; padding: 6px 12px; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">
                                        <i class="fas fa-trash me-1"></i>{{ trans('lang.delete') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center py-4 text-muted">No attributes found.</td></tr>
                            @endforelse
                                {{-- Loaded via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var csrfToken = '{{ csrf_token() }}';

    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#attributeTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

        $(document).on('click', '.delete-attr-btn', function () {
            var id = $(this).data('id');
            if (confirm("Delete this attribute?")) {
                $.ajax({
                    url: '{{ url("admin/attributes/delete") }}/' + id,
                    method: 'POST',
                    data: { _method: 'DELETE', _token: csrfToken },
                    success: function () { location.reload(); },
                    error: function () { alert('Delete failed.'); }
                });
            }
        });
    });
</script>
@endsection
