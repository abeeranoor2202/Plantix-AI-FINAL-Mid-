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
<script type="text/javascript">

    var database = firebase.firestore();
    var ref = database.collection('vendor_attributes').orderBy('title');
    var append_list = '';

    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('attributes.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        const table = $('#attributeTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = ['title'];
                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let filteredRecords = [];
                    querySnapshot.forEach(function (doc) {
                        let childData = doc.data();
                        childData.id = doc.id;
                        if (searchValue) {
                            if (childData.title && childData.title.toLowerCase().toString().includes(searchValue)) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        return orderDirection === 'asc' ? (aValue > bValue ? 1 : -1) : (aValue < bValue ? 1 : -1);
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = [];

                    await Promise.all(paginatedRecords.map(async (childData) => {
                        var getData = await buildHTML(childData);
                        records.push(getData);
                    }));

                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: records
                    });
                }).catch(function (error) {
                    console.error("Firestore error:", error);
                    $('#data-table_processing').hide();
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                });
            },
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [1] }],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            },
            dom: '<"d-flex justify-content-between align-items-center pt-3 px-4"f>t<"d-flex justify-content-between align-items-center py-3 px-4"ip>'
        });
    });

    function buildHTML(val) {
        var html = [];
        var id = val.id;
        var route1 = '{{route("admin.attributes.edit",":id")}}';
        route1 = route1.replace(':id', id);

        html.push('<div style="display: flex; align-items: center; gap: 12px;"><div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-tag"></i></div><a href="' + route1 + '" style="font-weight: 700; color: var(--agri-text-heading); text-decoration: none; font-size: 15px;">' + val.title + '</a></div>');
        
        var actionHtml = '<div style="display: flex; justify-content: flex-end; gap: 8px;">';
        actionHtml += '<a href="' + route1 + '" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-secondary-dark); border-radius: 10px; border: 1px solid var(--agri-border); text-decoration: none;" title="Edit"><i class="fas fa-edit"></i></a>';
        if (checkDeletePermission) {
            actionHtml += '<a id="' + val.id + '" name="attribute-delete" class="btn-agri" style="padding: 8px; background: var(--agri-error-light); color: var(--agri-error); border-radius: 10px; border: none; text-decoration: none;" href="javascript:void(0)" title="Delete"><i class="fas fa-trash"></i></a>';
        }
        actionHtml += '</div>';
        
        html.push(actionHtml);
        return html;
    }

    $(document).on("click", "a[name='attribute-delete']", function (e) {
        var id = this.id;
        if(confirm('Are you sure you want to delete this attribute?')) {
            jQuery("#data-table_processing").show();
            database.collection('vendor_attributes').doc(id).delete().then(function (result) {
                window.location.href = '{{ route("admin.attributes")}}';
            });
        }
    });

</script>
@endsection
