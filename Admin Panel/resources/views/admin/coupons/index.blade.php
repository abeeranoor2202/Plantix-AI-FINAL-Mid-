@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Incentives & Campaign Registry</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                Promotional Incentives
            </h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage promotional discounts, marketing campaigns, and platform vouchers.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            @if($id != '')
                <a href="{!! route('admin.coupons.create') !!}/{{$id}}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                    <i class="fas fa-plus"></i> Generate Campaign
                </a>
            @else
                <a href="{!! route('admin.coupons.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700;">
                    <i class="fas fa-plus"></i> Generate Campaign
                </a>
            @endif
        </div>
    </div>

    {{-- Store Tabs (Visible if $id is present) --}}
    @if($id != '')
    <div style="display: flex; gap: 32px; border-bottom: 1px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 0; overflow-x: auto;">
        <a href="{{route('admin.vendors.view', $id)}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_basic')}}</a>
        <a href="{{route('admin.products.index')}}?storeId={{$id}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_items')}}</a>
        <a href="{{route('admin.orders.index')}}?storeId={{$id}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_orders')}}</a>
        <a href="{{route('admin.coupons')}}" style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 800; font-size: 14px; border-bottom: 3px solid var(--agri-primary); white-space: nowrap;">{{trans('lang.tab_promos')}}</a>
        <a href="{{route('admin.vendors')}}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600; font-size: 14px; white-space: nowrap;">{{trans('lang.tab_payouts')}}</a>
    </div>
    @endif

    {{-- Strategy Filters --}}
    <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div>
                    <h4 style="font-size: 16px; font-weight: 800; color: var(--agri-text-heading); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Active Promotion Ledger</h4>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--agri-text-muted); font-weight: 600;">Monitor and control discount vectors across the ecosystem.</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px;"></i>
                    <input type="text" id="search-input" class="form-agri" placeholder="Scan by Hash or Campaign..." style="padding: 10px 16px 10px 44px; font-size: 13px; font-weight: 600; min-width: 280px;">
                </div>

                @if(in_array('coupons.delete', json_decode(@session('admin_permissions'), true)))
                    <a id="deleteAll" href="javascript:void(0)" class="btn-agri" style="color: var(--agri-error); font-size: 13px; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #FEF2F2; border-radius: 12px; border: 1px solid #FCA5A5;">
                        <i class="fas fa-trash-alt"></i> ELIMINATE SELECTED
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Coupons Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.9); color: var(--agri-primary); font-weight: 800; border-radius: 12px; z-index: 10; align-items: center; justify-content: center; height: 100%; width: 100%; position: absolute; top:0; left:0;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--agri-primary);"></div>
                <div>INITIALIZING TELEMETRY...</div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="couponTable" class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        @if(in_array('coupons.delete', json_decode(@session('admin_permissions'), true)))
                            <th style="padding: 20px 24px; border: none; width: 40px; border-top-left-radius: 12px;">
                                <div class="form-check" style="margin: 0; display: flex; justify-content: center;">
                                    <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer; width: 20px; height: 20px;">
                                </div>
                            </th>
                        @endif
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Incentive Hash</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Yield (Discount)</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Visibility</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Originating Node</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Expiration Vector</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Live Status</th>
                        <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none; border-top-right-radius: 12px;" class="text-end">Command</th>
                    </tr>
                </thead>
                <tbody id="append_list1"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .badge-agri { padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; }
    .badge-agri-success { background: #DCFCE7; color: #166534; border-color: #BBF7D0; }
    .badge-agri-error { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }
    .badge-agri-primary { background: var(--agri-primary-light); color: var(--agri-primary); border-color: var(--agri-primary); }

    table.dataTable tbody tr { background-color: white; border-bottom: 1px solid var(--agri-border); transition: 0.2s; }
    table.dataTable tbody tr:hover { background-color: var(--agri-bg); }
    table.dataTable.no-footer { border-bottom: none; }
    .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 8px; font-weight: 700; border: none; padding: 6px 14px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--agri-primary) !important; color: white !important; border: none; }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var getId = '{{$id}}';
    var user_permissions = '<?php echo @session("admin_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions || "[]"));
    var checkDeletePermission = $.inArray('coupons.delete', user_permissions) >= 0;

    var ref = (getId != '') ? database.collection('coupons').where('resturant_id', '==', getId) : database.collection('coupons');
    ref = ref.orderBy('expiresAt', 'desc');

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    database.collection('currencies').where('isActive', '==', true).get().then(async function (snapshots) {
        if(!snapshots.empty){
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            decimal_degits = currencyData.decimal_degits || 0;
        }
    });

    $(document).ready(function () {
        const table = $('#couponTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            lengthChange: false,
            info: false,
            dom: '<"top">rt<"bottom"p><"clear">',
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['','code', 'discount', '', '', 'expiresAt','',''] : ['code', 'discount', '', '', 'expiresAt', '', ''];
                const orderByField = orderableColumns[orderColumnIndex];

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }

                    let filteredRecords = [];
                    for (const doc of querySnapshot.docs) {
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.restaurantName = "System Default";
                        if(childData.resturant_id){
                             var storeSnap = await database.collection('vendors').doc(childData.resturant_id).get();
                             if(storeSnap.exists) childData.restaurantName = storeSnap.data().title;
                        }

                        if (searchValue) {
                             var expireStr = childData.expiresAt ? childData.expiresAt.toDate().toLocaleDateString() : '';
                             if(childData.code.toLowerCase().includes(searchValue) || 
                                childData.restaurantName.toLowerCase().includes(searchValue) ||
                                (childData.description && childData.description.toLowerCase().includes(searchValue))) 
                             {
                                 filteredRecords.push(childData);
                             }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }

                    filteredRecords.sort((a, b) => {
                        let aVal = a[orderByField] || '';
                        let bVal = b[orderByField] || '';
                        if (orderByField === 'expiresAt') {
                            aVal = a.expiresAt ? a.expiresAt.toDate().getTime() : 0;
                            bVal = b.expiresAt ? b.expiresAt.toDate().getTime() : 0;
                        }
                        return orderDirection === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = [];

                    paginatedRecords.forEach(function (childData) {
                        var routeEdit = '{{route("admin.coupons.edit", ":id")}}'.replace(':id', childData.id);
                        if(getId != '') routeEdit += '?eid=' + getId;

                        var discount_price = '';
                        if (childData.discountType == 'Percent' || childData.discountType == 'Percentage') {
                            discount_price = childData.discount + "%";
                        } else {
                            discount_price = currencyAtRight ? parseFloat(childData.discount).toFixed(decimal_degits) + currentCurrency : currentCurrency + parseFloat(childData.discount).toFixed(decimal_degits);
                        }

                        var dateStr = '—';
                        if (childData.expiresAt) {
                            var d = childData.expiresAt.toDate();
                            var diffTime = Math.abs(d - new Date());
                            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                            var isUpcoming = d > new Date();
                            
                            if (!isUpcoming) {
                                dateStr = '<div style="font-weight:800; color:var(--agri-error);">' + d.toLocaleDateString() + ' <span style="font-size:10px; opacity:0.8;">(EXPIRED)</span></div>';
                            } else {
                                dateStr = '<div style="font-weight:800; color:var(--agri-text-heading);">' + d.toLocaleDateString() + ' <span style="font-size:10px; color:var(--agri-primary);">' + diffDays + 'd left</span></div>';
                            }
                        }

                        var privacyHtml = childData.isPublic ? '<span class="badge-agri badge-agri-success"><i class="fas fa-globe"></i> Ecosystem</span>' : '<span class="badge-agri badge-agri-primary"><i class="fas fa-lock"></i> Restricted</span>';

                        records.push([
                            checkDeletePermission ? '<td style="padding: 24px; text-align: center;"><input type="checkbox" id="is_open_' + childData.id + '" class="is_open form-check-input" dataId="' + childData.id + '" style="margin:0; width:20px; height:20px;"></td>' : '',
                            '<a href="' + routeEdit + '" style="font-weight:900; color:var(--agri-primary-dark); text-decoration:none; font-family:\'Courier New\', Courier, monospace; font-size:16px; background:var(--agri-bg); padding:6px 14px; border-radius:8px; border:2px dashed var(--agri-primary)60; display:inline-block;">' + childData.code + '</a>',
                            '<div style="font-weight:900; color:var(--agri-text-heading); font-size:16px;">' + discount_price + ' <span style="font-size:11px; color:var(--agri-text-muted); text-transform:uppercase; font-weight:700;">YIELD</span></div>',
                            '<div class="text-center">' + privacyHtml + '</div>',
                            '<div style="font-weight:700; font-size:13px; color:var(--agri-text-muted); display:flex; align-items:center; gap:8px;"><i class="fas fa-server" style="font-size:10px; opacity:0.6;"></i> ' + childData.restaurantName + '</div>',
                            dateStr,
                            '<div class="form-check form-switch" style="display:flex; justify-content:center; margin:0;"><input type="checkbox" class="form-check-input" ' + (childData.isEnabled ? 'checked' : '') + ' id="' + childData.id + '" name="isActive" style="width:40px; height:22px; cursor:pointer;" title="Toggle Activity"></div>',
                            '<div class="text-end" style="display:flex; gap:8px; justify-content:flex-end;">' +
                            '<a href="' + routeEdit + '" class="btn-agri" style="padding:10px 14px; background:var(--agri-bg); color:var(--agri-text-heading); border-radius:12px; text-decoration:none; border:none;" title="Reconfigure Campaign"><i class="fas fa-cog"></i></a>' + 
                            (checkDeletePermission ? '<a id="' + childData.id + '" class="btn-agri delete-btn" style="padding:10px 14px; background:#FEF2F2; color:var(--agri-error); border-radius:12px; border:none;" title="Terminate Campaign"><i class="fas fa-trash-alt"></i></a>' : '') + '</div>'
                        ]);
                    });

                    callback({ draw: data.draw, recordsTotal: totalRecords, recordsFiltered: totalRecords, data: records });
                });
            },
            order: (checkDeletePermission) ? [[5, 'desc']] : [[4, 'desc']],
            columnDefs: [{ targets: '_all', orderable: false }, { orderable: true, targets: (checkDeletePermission) ? [1, 2, 5] : [0, 1, 4] }],
            language: { zeroRecords: "<div style='padding: 60px; text-align: center; color: var(--agri-text-muted);'><i class='fas fa-ticket-alt' style='font-size: 48px; opacity: 0.2; margin-bottom: 16px; display: block;'></i><h5 style='font-weight: 800; color: var(--agri-text-heading); margin: 0;'>NO CAMPAIGNS ACTIVE</h5><p style='margin: 8px 0 0 0; font-size: 14px;'>Adjust your hash filters or initiate a new campaign.</p></div>", emptyTable: "{{trans('lang.no_record_found')}}", processing: "" }
        });

        $('#search-input').on('keydown', function (e) {
            if(e.keyCode == 13) {
                 table.search($(this).val()).draw();
            }
        });
        
        // Auto search typing
        let typingTimer;
        $('#search-input').on('input', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                table.search($(this).val()).draw();
            }, 600);
        });
    });

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        database.collection('coupons').doc(this.id).update({'isEnabled': ischeck});
    });

    $(document).on("click", ".delete-btn", function (e) {
        if(confirm("CRITICAL: Terminate this campaign and revoke hashes from the ecosystem?")){
            database.collection('coupons').doc(this.id).delete().then(() => window.location.reload());
        }
    });

    $("#is_active").click(function () {
        $("#couponTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#couponTable .is_open:checked').length) {
            if (confirm("CRITICAL: Terminate selected campaigns and revoke hashes?")) {
                let promises = [];
                $('#couponTable .is_open:checked').each(function () {
                    promises.push(database.collection('coupons').doc($(this).attr('dataId')).delete());
                });
                Promise.all(promises).then(() => window.location.reload());
            }
        } else {
            alert("No hashes selected for termination.");
        }
    });
</script>
@endsection
