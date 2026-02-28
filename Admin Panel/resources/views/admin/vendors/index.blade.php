@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Ecosystem</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Stakeholder Management</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                @if(request()->is('vendors/approved'))
                    Verified Agriculture Experts
                @elseif(request()->is('vendors/pending'))
                    Pending Verification Queue
                @else
                    Service Partner Ecosystem
                @endif
            </h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Orchestrate and verify the credentials of agriculture consultants and vendors.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <div style="background: white; padding: 10px 20px; border-radius: 14px; border: 1px solid var(--agri-border); font-size: 13px; font-weight: 800; color: var(--agri-primary); display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <i class="fas fa-certificate"></i>
                QUALITY ASSURED PARTNERS
            </div>
        </div>
    </div>

    {{-- Strategy Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div style="padding: 24px 32px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center; background: white;">
            <div style="display: flex; gap: 8px; background: var(--agri-bg); padding: 6px; border-radius: 16px; border: 1px solid var(--agri-border);">
                <a href="{{url('/vendors/all')}}" class="btn-agri {{ !request()->is('vendors/approved') && !request()->is('vendors/pending') ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ !request()->is('vendors/approved') && !request()->is('vendors/pending') ? 'var(--agri-primary)' : 'transparent' }}; color: {{ !request()->is('vendors/approved') && !request()->is('vendors/pending') ? 'white' : 'var(--agri-text-muted)' }};">
                    Complete Ledger
                </a>
                <a href="{{url('/vendors/approved')}}" class="btn-agri {{ request()->is('vendors/approved') ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ request()->is('vendors/approved') ? 'var(--agri-primary)' : 'transparent' }}; color: {{ request()->is('vendors/approved') ? 'white' : 'var(--agri-text-muted)' }};">
                    Verified
                </a>
                <a href="{{url('/vendors/pending')}}" class="btn-agri {{ request()->is('vendors/pending') ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ request()->is('vendors/pending') ? 'var(--agri-primary)' : 'transparent' }}; color: {{ request()->is('vendors/pending') ? 'white' : 'var(--agri-text-muted)' }};">
                    Awaiting Audit
                </a>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-primary); font-size: 14px; opacity: 0.7;"></i>
                    <input type="text" id="search-input" placeholder="Search expert registry..." class="form-agri" style="padding-left: 44px; width: 300px; height: 44px; font-size: 14px; font-weight: 600;">
                </div>

                <?php if (
                    ($type == "approved" && in_array('approve.vendors.delete', json_decode(@session('admin_permissions'), true))) ||
                    ($type == "pending" && in_array('pending.vendors.delete', json_decode(@session('admin_permissions'), true))) ||
                    ($type == "all" && in_array('vendors.delete', json_decode(@session('admin_permissions'), true)))
                ) { ?>
                    <button id="deleteAll" class="btn-agri" style="color: var(--agri-error); font-size: 13px; font-weight: 800; border: none; border-radius: 12px; padding: 12px 20px; background: #FEF2F2; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-trash-alt"></i> Bulk Decommission
                    </button>
                <?php } ?>
            </div>
        </div>

        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.95); position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; z-index: 100;">
            <div style="text-align: center;">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                <div style="margin-top: 16px; font-weight: 800; color: var(--agri-primary); letter-spacing: 1px;">SYNCING REGISTRY...</div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="userTable" class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <?php if (
                            ($type == "approved" && in_array('approve.vendors.delete', json_decode(@session('admin_permissions'), true))) ||
                            ($type == "pending" && in_array('pending.vendors.delete', json_decode(@session('admin_permissions'), true))) ||
                            ($type == "all" && in_array('vendors.delete', json_decode(@session('admin_permissions'), true)))
                        ) { ?>
                            <th style="padding: 20px 32px; border: none; width: 40px;">
                                <div class="form-check m-0">
                                    <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer; width: 20px; height: 20px;">
                                </div>
                            </th>
                        <?php } ?>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Expert/Partner Profile</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Credential Channels</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Ecosystem Onboarding</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Verification Audit</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Operational Status</th>
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
    .expert-row:hover { background: var(--agri-bg); }
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var type = "{{$type}}";
    var user_permissions = '<?php echo @session("admin_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions || "[]"));
    var checkDeletePermission = false;

    if (
        (type == 'pending' && $.inArray('pending.vendors.delete', user_permissions) >= 0) ||
        (type == 'approved' && $.inArray('approve.vendors.delete', user_permissions) >= 0) ||
        (type == 'all' && $.inArray('vendors.delete', user_permissions) >= 0)
    ) {
        checkDeletePermission = true;
    }

    var ref = database.collection('users').where("role", "==", "vendor").orderBy('createdAt', 'desc');
    if (type == 'pending') {
        ref = database.collection('users').where("role", "==", "vendor").where("isDocumentVerify", "==", false).orderBy('createdAt', 'desc');
    } else if (type == 'approved') {
        ref = database.collection('users').where("role", "==", "vendor").where("isDocumentVerify", "==", true).orderBy('createdAt', 'desc');
    }

    var placeholderImage = '';

    $(document).ready(function () {
        database.collection('settings').doc('placeHolderImage').get().then(async function (snapshotsimage) {
            placeholderImage = snapshotsimage.data().image;
        });

        const table = $('#userTable').DataTable({
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
                
                // Adjusted for redesigned header indexes
                const orderableColumns = (checkDeletePermission) ? 
                    ['','','fullName', 'email', 'createdAt','','',''] : 
                    ['','fullName', 'email', 'createdAt','','',''];
                
                const orderByField = orderableColumns[orderColumnIndex];

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }

                    let filteredRecords = [];
                    querySnapshot.forEach(function (doc) {
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.fullName = (childData.firstName || '') + ' ' + (childData.lastName || '');

                        if (searchValue) {
                            var date = childData.createdAt ? childData.createdAt.toDate().toDateString() : '';
                            if (childData.fullName.toLowerCase().includes(searchValue) ||
                                childData.email.toLowerCase().includes(searchValue) ||
                                (childData.phoneNumber && childData.phoneNumber.toString().includes(searchValue)) ||
                                date.toLowerCase().includes(searchValue)) 
                            {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });

                    filteredRecords.sort((a, b) => {
                        let aVal = a[orderByField] || '';
                        let bVal = b[orderByField] || '';
                        if (orderByField === 'createdAt') {
                            aVal = a.createdAt ? a.createdAt.toDate().getTime() : 0;
                            bVal = b.createdAt ? b.createdAt.toDate().getTime() : 0;
                        }
                        return orderDirection === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = [];

                    for (const childData of paginatedRecords) { records.push(await buildHTML(childData)); }

                    callback({ draw: data.draw, recordsTotal: totalRecords, recordsFiltered: totalRecords, data: records });
                });
            },
            order: (checkDeletePermission) ? [[2, 'desc']] : [[1, 'desc']],
            columnDefs: [{ orderable: false, targets: '_all' }],
            dom: 't<"p-4 d-flex justify-content-between align-items-center"ip>',
            language: {
                zeroRecords: 'NO EXPERTS MATCH YOUR SEARCH CRITERIA',
                emptyTable: 'SERVICE PARTNER ECOSYSTEM IS EMPTY',
                processing: ""
            }
        });

        $('#search-input').on('keyup', function () { table.search($(this).val()).draw(); });

        $("#is_active").click(function () { $("#userTable .is_open").prop('checked', $(this).prop('checked')); });

        $(document).on("click", ".form-switch input", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            database.collection('users').doc(id).update({ 'active': ischeck, 'isActive': ischeck });
        });

        $(document).on("click", ".delete-btn", function (e) {
            if (confirm("CRITICAL: Decommission this expert? This will revoke all platform access and remove their entry from the ledger.")) {
                var id = $(this).data('id');
                database.collection('users').doc(id).delete().then(() => window.location.reload());
            }
        });

        $("#deleteAll").click(function () {
            if ($('#userTable .is_open:checked').length) {
                if (confirm("CRITICAL: Bulk decommission selected partners? This action is non-reversible.")) {
                    let promises = [];
                    $('#userTable .is_open:checked').each(function () { promises.push(database.collection('users').doc($(this).attr('dataId')).delete()); });
                    Promise.all(promises).then(() => window.location.reload());
                }
            } else { alert("Select at least one partner for bulk decommissioning."); }
        });
    });

    async function buildHTML(val) {
        var html = [];
        var id = val.id;

        // Selection
        if (checkDeletePermission) {
            html.push('<td style="padding: 24px 32px;"><div class="form-check"><input type="checkbox" id="is_open_' + id + '" class="is_open form-check-input" dataId="' + id + '" style="width: 20px; height: 20px;"></div></td>');
        }

        // Expert Profile
        var profilePic = val.profilePictureURL || placeholderImage;
        html.push('<td style="padding: 24px 32px;"><div style="display:flex; align-items:center; gap:16px;">' +
            '<div style="position:relative;"><img src="' + profilePic + '" onerror="this.src=\'' + placeholderImage + '\'" style="width:52px; height:52px; border-radius:14px; object-fit:cover; border:2px solid var(--agri-bg);">' +
            (val.isDocumentVerify ? '<div style="position:absolute; bottom:-4px; right:-4px; background:var(--agri-primary); color:white; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; border:2px solid white;"><i class="fas fa-check"></i></div>' : '') +
            '</div>' +
            '<div><div style="font-weight:800; color:var(--agri-text-heading); font-size:15px;">' + (val.firstName || '') + ' ' + (val.lastName || '') + '</div>' +
            '<div style="font-size:10px; font-weight:800; color:var(--agri-primary); text-transform:uppercase;">' + (val.role || 'Partner') + ' NODE</div></div></div></td>');

        // Contact Details
        html.push('<td style="padding: 24px 32px;"><div style="font-size:13px; color:var(--agri-text-heading); font-weight:700;">' + (val.email || '—') + '</div>' +
            '<div style="font-size:12px; color:var(--agri-text-muted); font-weight:600; margin-top:2px;"><i class="fas fa-phone-alt me-2" style="font-size:10px;"></i>' + (val.phoneNumber || 'N/A') + '</div></td>');

        // Joined Date
        var dateStr = '—';
        if (val.createdAt) {
            var d = val.createdAt.toDate();
            dateStr = '<div style="font-weight:700; font-size:13px; color:var(--agri-text-heading);">' + d.toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) + '</div>' +
                      '<div style="font-size:11px; color:var(--agri-text-muted); font-weight:600;">' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</div>';
        }
        html.push('<td style="padding: 24px 32px;">' + dateStr + '</td>');

        // Verification Audit
        var docUrl = "{{route('admin.vendors.document', ':id')}}".replace(':id', id);
        var auditStatus = val.isDocumentVerify ? 
            '<span style="background:var(--agri-primary-light); color:var(--agri-primary); padding:6px 16px; border-radius:12px; font-size:10px; font-weight:900;">AUDIT PASSED</span>' :
            '<span style="background:#FFFBEB; color:#B45309; padding:6px 16px; border-radius:12px; font-size:10px; font-weight:900;">PENDING AUDIT</span>';

        html.push('<td style="padding: 24px 32px;" class="text-center">' +
            '<div style="margin-bottom:8px;">' + auditStatus + '</div>' +
            '<a href="' + docUrl + '" style="font-size:11px; font-weight:800; color:var(--agri-primary); text-decoration:none; text-transform:uppercase; letter-spacing:0.5px;">Review Credentials <i class="fas fa-arrow-right ms-1"></i></a></td>');

        // Operational Status
        var statusBadge = val.active ? 
            '<span style="background:var(--agri-primary-light); color:var(--agri-primary); padding:2px 10px; border-radius:100px; font-size:10px; font-weight:800; border:1px solid var(--agri-primary)40;">OPERATIONAL</span>' :
            '<span style="background:#F3F4F6; color:#6B7280; padding:2px 10px; border-radius:100px; font-size:10px; font-weight:800; border:1px solid #D1D5DB;">SUSPENDED</span>';
        
        html.push('<td style="padding: 24px 32px;" class="text-center">' +
            '<div style="display:flex; flex-direction:column; align-items:center; gap:8px;">' +
            statusBadge +
            '<div class="form-check form-switch p-0 m-0"><input type="checkbox" class="form-check-input" ' + (val.active ? 'checked' : '') + ' id="' + id + '" style="width:40px; height:20px; cursor:pointer; margin:0;"></div>' +
            '</div></td>');

        // Actions
        if (checkDeletePermission) {
            html.push('<td style="padding: 24px 32px;" class="text-end"><div style="display:flex; justify-content:flex-end; gap:8px;">' +
                '<a href="' + "{{route('admin.vendors.edit', ':id')}}".replace(':id', id) + '" class="btn-agri" style="padding:8px 12px; background:var(--agri-bg); color:var(--agri-text-heading); border-radius:10px; text-decoration:none; font-size:12px; font-weight:700;"><i class="fas fa-edit"></i></a>' +
                '<button class="btn-agri delete-btn" data-id="' + id + '" style="padding:8px 12px; background:#FEF2F2; color:var(--agri-error); border:none; border-radius:10px; font-size:12px;"><i class="fas fa-trash-alt"></i></button>' +
                '</div></td>');
        }

        return html;
    }
</script>
@endsection