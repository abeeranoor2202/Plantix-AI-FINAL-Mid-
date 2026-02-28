@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.user_plural')}}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{trans('lang.user_table')}}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage your platform's farmers and customers efficiently.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{!! route('admin.users.create') !!}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i>
                {{trans('lang.user_create')}}
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                 <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Customer List</h4>
                 <div id="data-table_processing" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;"></div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: var(--agri-border);">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search farmers..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>

                <?php if (in_array('user.delete', json_decode(@session('admin_permissions'),true))) { ?>
                    <button id="deleteAll" class="btn-agri btn-agri-outline" style="color: var(--agri-error); border-color: var(--agri-error)20; background: var(--agri-error-light); padding: 8px 20px; font-size: 14px; height: 42px;">
                        <i class="fas fa-trash-alt" style="margin-right: 6px;"></i> Delete Selected
                    </button>
                <?php } ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="userTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <?php if (in_array('user.delete', json_decode(@session('admin_permissions'),true))) { ?>
                        <th style="padding: 16px 24px; border: none; width: 50px;">
                            <div class="form-check m-0">
                                <input type="checkbox" id="is_active" class="form-check-input" style="cursor: pointer;">
                            </div>
                        </th>
                        <?php } ?>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.extra_image')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.user_name')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.email')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.date')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.active')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Wallet</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">{{trans('lang.actions')}}</th>
                    </tr>
                </thead>
                <tbody id="append_list1">
                    {{-- Data injected via DataTables/Firestore --}}
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-4 px-4">
             {{-- Pagination handled by DataTables --}}
        </div>
    </div>
</div>

<style>
    /* Custom Slider for Active Toggle */
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
    input:checked + .slider {
        background-color: var(--agri-primary);
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    
    #userTable tbody tr:hover {
        background-color: rgba(var(--agri-primary-rgb), 0.02);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        border: 1px solid var(--agri-border) !important;
        margin: 0 2px;
        padding: 6px 14px !important;
        font-weight: 600;
        font-size: 13px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--agri-primary) !important;
        color: white !important;
        border-color: var(--agri-primary) !important;
    }
    .dataTables_wrapper .dataTables_info {
        color: var(--agri-text-muted) !important;
        font-size: 13px;
        font-weight: 500;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref = database.collection('users').where("role", "in", ["customer"]).orderBy('createdAt', 'desc');
    var placeholderImage = '';

    var user_permissions = '<?php echo @session("admin_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('user.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    function shortEmail(email) {
        if (email.length > 20) {
            return email.substr(0, 20) + '...';
        }
        return email;
    }

    $(document).ready(function () {
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        
        jQuery("#data-table_processing").show();

        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });

        const table = $('#userTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                
                // Adjust for Select Column
                const orderableColumns = (checkDeletePermission) 
                    ? ['', '', 'fullName', 'email', 'createdAt', '', '', ''] 
                    : ['', 'fullName', 'email', 'createdAt', '', '', ''];
                
                const orderByField = orderableColumns[orderColumnIndex] || 'createdAt';

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('#data-table_processing').hide();
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }

                    let filteredRecords = [];                  
                    querySnapshot.forEach(function (doc) {                        
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.fullName = (childData.firstName || '') + ' ' + (childData.lastName || '');
                        if (!childData.fullName.trim()) childData.fullName = "No Name";
                        
                        var date = '';
                        var time = '';
                        if (childData.hasOwnProperty("createdAt") && childData.createdAt) {
                            try {
                                date = childData.createdAt.toDate().toDateString();
                                time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                            } catch (err) {}
                        }
                        var createdAt = date + ' ' + time;
                        
                        if (searchValue) {                           
                            if (
                                (childData.fullName.toLowerCase().includes(searchValue)) ||
                                (createdAt.toLowerCase().includes(searchValue)) || 
                                (childData.email && childData.email.toString().toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'createdAt') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        }                        
                        if (orderDirection === 'asc') return (aValue > bValue) ? 1 : -1;
                        return (aValue < bValue) ? 1 : -1;
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    let records = [];Pag:

                    paginatedRecords.forEach(function (childData) {
                        var id = childData.id;
                        var editRoute = '{{route("admin.users.edit",":id")}}'.replace(':id', id);
                        var viewRoute = '{{route("admin.users.view",":id")}}'.replace(':id', id);
                        var walletRoute = '#'; // Wallet transactions feature not available

                        var date = '';
                        var time = '';
                        if (childData.hasOwnProperty("createdAt") && childData.createdAt) {
                            try {
                                date = childData.createdAt.toDate().toDateString();
                                time = childData.createdAt.toDate().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
                            } catch (err) {}
                        }

                        records.push([
                            checkDeletePermission ? '<div class="form-check m-0"><input type="checkbox" id="is_open_' + id + '" class="is_open form-check-input" dataId="' + id + '"><label class="form-check-label" for="is_open_' + id + '"></label></div>' : '',
                            '<div style="width:40px; height:40px; border-radius:8px; overflow:hidden; border: 1px solid var(--agri-border);"><img onerror="this.src=\'' + placeholderImage + '\'" style="width:100%; height:100%; object-fit:cover;" src="' + (childData.profilePictureURL || placeholderImage) + '" alt="image"></div>',
                            '<div style="font-weight:700; color:var(--agri-text-heading);"><a href="' + viewRoute + '" style="text-decoration:none; color:inherit;">' + childData.fullName + '</a></div>',
                            '<div style="font-size:13px; color:var(--agri-text-muted);">' + (childData.email ? shortEmail(childData.email) : '---') + '</div>',
                            '<div style="font-size:13px; font-weight:500;">' + date + '<br><small class="text-muted">' + time + '</small></div>',
                            '<label class="switch"><input type="checkbox" ' + (childData.active ? 'checked' : '') + ' id="' + id + '" name="isActive"><span class="slider"></span></label>',
                            '<a href="' + walletRoute + '" class="btn-agri btn-agri-outline" style="padding: 4px 10px; font-size:11px; text-decoration:none;"><i class="fas fa-wallet" style="margin-right:4px;"></i> View</a>',
                            '<div class="text-end" style="display:flex; justify-content:flex-end; gap:8px;">' +
                                '<a href="' + viewRoute + '" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 10px;" title="View"><i class="fas fa-eye"></i></a>' +
                                '<a href="' + editRoute + '" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 10px;" title="Edit"><i class="fas fa-edit"></i></a>' +
                                (checkDeletePermission ? '<a id="' + id + '" class="btn-agri delete-btn" name="user-delete" href="javascript:void(0)" style="padding: 8px; background: var(--agri-error-light); color: var(--agri-error); border-radius: 10px;"><i class="fas fa-trash"></i></a>' : '') +
                            '</div>'
                        ]);
                    });

                    $('#data-table_processing').hide();
                    callback({ draw: data.draw, recordsTotal: totalRecords, recordsFiltered: totalRecords, data: records });
                }).catch(function (error) {
                    console.error("Firestore Error:", error);
                    $('#data-table_processing').hide();
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                });
            },           
            order: (checkDeletePermission) ? [4, 'desc'] : [3, 'desc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0, 1, 5, 6, 7] : [0, 4, 5, 6] },
                { className: "px-4 py-3", targets: "_all" }
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            }
        });

        $('#search-input').on('keyup', function () {
            table.search(this.value).draw();
        });

        $("#is_active").click(function () {
            $("#userTable .is_open").prop('checked', $(this).prop('checked'));
        });

        $("#deleteAll").click(function () {
            if ($('#userTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#userTable .is_open:checked').each(function () {
                        var dataId = $(this).attr('dataId');
                        database.collection('users').doc(dataId).delete().then(function () {
                            deleteUserData(dataId);
                        });
                    });
                    setTimeout(() => { window.location.reload(); }, 3000);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    });

    async function deleteUserData(userId) {
        await database.collection('wallet').where('user_id', '==', userId).get().then(async function (snapshotsItem) {
            snapshotsItem.docs.forEach((temData) => {
                database.collection('wallet').doc(temData.data().id).delete();
            });
        });
        
        var dataObject = {"data": {"uid": userId}};
        var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
        jQuery.ajax({
            url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
            method: 'POST',
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(dataObject)
        });
    }    

    $(document).on("click", "a[name='user-delete']", function (e) {
        var id = this.id;
        if(confirm("Are you sure you want to delete this user?")){
            jQuery("#data-table_processing").show();
            database.collection('users').doc(id).delete().then(function () {
                deleteUserData(id);
                setTimeout(() => { window.location.reload(); }, 3000);
            });
        }
    });

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        database.collection('users').doc(id).update({'active': ischeck});
    });
</script>
@endsection