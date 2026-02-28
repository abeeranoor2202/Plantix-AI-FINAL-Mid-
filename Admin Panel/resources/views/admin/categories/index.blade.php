@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Catalog Management</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Taxonomic Registry</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.category_plural')}}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Architect and manage the hierarchical classification of products and services.</p>
        </div>
        <a href="{!! route('admin.categories.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
            <i class="fas fa-plus"></i> Instantiate Category
        </a>
    </div>

    {{-- Taxonomy Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div style="padding: 24px 32px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center; background: white;">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--agri-text-heading);">Classification Inventory</h4>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-primary); font-size: 14px; opacity: 0.7;"></i>
                    <input type="text" id="search-input" placeholder="Search categories..." class="form-agri" style="padding-left: 44px; width: 280px; height: 44px; font-size: 14px; font-weight: 600;">
                </div>

                <?php if (in_array('category.delete', json_decode(@session('admin_permissions'),true))) { ?>
                    <button id="deleteAll" class="btn-agri" style="color: var(--agri-error); font-size: 13px; font-weight: 700; border: none; border-radius: 12px; padding: 12px 20px; background: #FEF2F2; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-trash-alt"></i> Bulk Deletion
                    </button>
                <?php } ?>
            </div>
        </div>

        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.95); position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; z-index: 100;">
            <div style="text-align: center;">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                <div style="margin-top: 16px; font-weight: 800; color: var(--agri-primary); letter-spacing: 1px;">SYNCING CATALOG...</div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="categoriesTable" class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <?php if (in_array('category.delete', json_decode(@session('admin_permissions'),true))) { ?>
                            <th style="padding: 20px 32px; border: none; width: 40px;">
                                <div class="form-check m-0">
                                    <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer; width: 20px; height: 20px;">
                                </div>
                            </th>
                        <?php } ?>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Class Node</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Catalog Density</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Visibility</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody id="append_list1"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .dataTables_empty { padding: 80px 0 !important; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; font-size: 14px; }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref = database.collection('vendor_categories').orderBy('title');
    var placeholderImage = '';

    var user_permissions = '<?php echo @session("admin_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions || "[]"));
    var checkDeletePermission = false;
    if ($.inArray('category.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function () {
        database.collection('settings').doc('placeHolderImage').get().then(async function (snapshotsimage) {
            placeholderImage = snapshotsimage.data().image;
        });

        const table = $('#categoriesTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['','','title', 'totalProducts','',''] : ['','title', 'totalProducts','',''];
                const orderByField = orderableColumns[orderColumnIndex];

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }

                    let filteredRecords = [];                  
                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.totalProducts = childData.id ? await getProductTotal(childData.id) : 0;
                        
                        if (!searchValue || childData.title.toLowerCase().includes(searchValue)) {
                            filteredRecords.push(childData);
                        }
                    }));

                    filteredRecords.sort((a, b) => {
                        let aVal = a[orderByField] || '';
                        let bVal = b[orderByField] || '';
                        if (orderByField === 'totalProducts') {
                            aVal = parseInt(a[orderByField]) || 0;
                            bVal = parseInt(b[orderByField]) || 0;
                        }
                        return orderDirection === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
                    });

                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = [];
                    paginatedRecords.forEach(childData => { records.push(buildHTML(childData)); });

                    callback({ draw: data.draw, recordsTotal: filteredRecords.length, recordsFiltered: filteredRecords.length, data: records });
                });
            },           
            order: (checkDeletePermission) ? [[2, 'asc']] : [[1,'asc']],
            columnDefs: [{ orderable: false, targets: '_all' }],
            dom: 't<"p-4 d-flex justify-content-between align-items-center"ip>',
            language: { zeroRecords: "NO CLASSIFICATIONS MATCHING YOUR SEARCH", emptyTable: "TAXONOMIC REGISTRY IS EMPTY", processing: "" }
        });

        $('#search-input').on('keyup', function () { table.search($(this).val()).draw(); });

        $(document).on("click", ".form-switch input", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            database.collection('vendor_categories').doc(id).update({'publish': ischeck});
        });

        $(document).on("click", ".delete-btn", function (e) {
            if (confirm("CRITICAL: Excise this category branch? This action will impact all downstream sub-classifications and product alignments.")) {
                var id = $(this).data('id');
                database.collection('vendor_categories').doc(id).delete().then(() => window.location.reload());
            }
        });

        $("#is_active").click(function () { $("#categoriesTable .is_open").prop('checked', $(this).prop('checked')); });

        $("#deleteAll").click(function () {
            if ($('#categoriesTable .is_open:checked').length) {
                if (confirm("CRITICAL: Bulk excise selected classifications? This will decouple all associated product catalog associations.")) {
                    let promises = [];
                    $('#categoriesTable .is_open:checked').each(function () { promises.push(database.collection('vendor_categories').doc($(this).attr('dataId')).delete()); });
                    Promise.all(promises).then(() => window.location.reload());
                }
            } else { alert("Select at least one classification node."); }
        });
    });

    function buildHTML(val) {
        var html = [];
        var id = val.id;
        var editUrl = '{{route("admin.categories.edit",":id")}}'.replace(':id', id);
        var catalogUrl = '{{url("items?categoryID=id")}}'.replace("id", id);
        var photo = val.photo || placeholderImage;

        if (checkDeletePermission) {
            html.push('<td style="padding: 24px 32px;"><div class="form-check"><input type="checkbox" id="is_open_' + id + '" class="is_open form-check-input" style="width: 20px; height: 20px;" dataId="' + id + '"></div></td>');
        }

        html.push('<td style="padding: 24px 32px;"><div style="display: flex; align-items: center; gap: 16px;">' +
            '<div style="width: 52px; height: 52px; border-radius: 14px; overflow: hidden; border: 2px solid var(--agri-bg); background: white;">' +
            '<img src="' + photo + '" onerror="this.src=\'' + placeholderImage + '\'" style="width: 100%; height: 100%; object-fit: cover;"></div>' +
            '<div><div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">' + val.title + '</div>' +
            '<div style="font-size: 10px; font-weight: 800; color: var(--agri-primary); text-transform: uppercase; margin-top: 4px;">' + (val.id.substring(0,8)) + ' NODE</div></div></div></td>');

        html.push('<td style="padding: 24px 32px;" class="text-center"><a href="' + catalogUrl + '" style="background: var(--agri-primary-light); color: var(--agri-primary); padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 800; text-decoration: none; border: 1px solid var(--agri-primary)30;">' +
            '<i class="fas fa-layer-group me-2"></i>' + val.totalProducts + ' Nodes Registered</a></td>');

        var statusBadge = val.publish ? 
            '<span style="background:var(--agri-primary-light); color:var(--agri-primary); padding:2px 10px; border-radius:100px; font-size:10px; font-weight:900; border:1px solid var(--agri-primary)40;">LIVE</span>' :
            '<span style="background:#F3F4F6; color:#6B7280; padding:2px 10px; border-radius:100px; font-size:10px; font-weight:900; border:1px solid #D1D5DB;">DRAFT</span>';

        html.push('<td style="padding: 24px 32px;" class="text-center"><div style="display:flex; flex-direction:column; align-items:center; gap:8px;">' +
            statusBadge +
            '<div class="form-check form-switch p-0 m-0"><input type="checkbox" class="form-check-input" ' + (val.publish ? 'checked' : '') + ' id="' + id + '" style="width:40px; height:20px; cursor:pointer;"></div>' +
            '</div></td>');

        html.push('<td style="padding: 24px 32px;" class="text-end"><div style="display: flex; justify-content: flex-end; gap: 8px;">' +
            '<a href="' + editUrl + '" class="btn-agri" style="padding: 8px 12px; background: var(--agri-bg); color: var(--agri-text-heading); border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 700;"><i class="fas fa-edit"></i></a>' +
            (checkDeletePermission ? '<button class="btn-agri delete-btn" data-id="' + id + '" style="padding: 8px 12px; background: #FEF2F2; color: var(--agri-error); border: none; border-radius: 10px;"><i class="fas fa-trash-alt"></i></button>' : '') +
            '</div></td>');

        return html;
    }

    async function getProductTotal(id) {
        var snapshot = await database.collection('vendor_products').where('categoryID', '==', id).get();
        return snapshot.docs.length;
    }
</script>
@endsection
