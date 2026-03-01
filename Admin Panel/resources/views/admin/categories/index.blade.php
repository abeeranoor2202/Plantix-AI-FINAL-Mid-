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

        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.95); position: absolute; top: 0; left: 0; right: 0; bottom: 0; align-items: center; justify-content: center; z-index: 100;">
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
                <tbody id="append_list1">
                    @forelse($categories as $category)
                        @php
                            $editUrl = route('admin.categories.edit', $category->id);
                            $imgUrl  = $category->image ? (str_starts_with($category->image, 'http') ? $category->image : asset('storage/'.$category->image)) : '';
                            $canDel  = in_array('category.delete', json_decode(@session('admin_permissions'), true) ?? []);
                        @endphp
                        <tr>
                            @if($canDel)
                            <td style="padding:24px 32px;">
                                <div class="form-check"><input type="checkbox" class="is_open form-check-input" style="width:20px;height:20px;" data-id="{{ $category->id }}"></div>
                            </td>
                            @endif
                            <td style="padding:24px 32px;">
                                <div style="display:flex;align-items:center;gap:16px;">
                                    <div style="width:52px;height:52px;border-radius:14px;overflow:hidden;border:2px solid var(--agri-bg);background:white;display:flex;align-items:center;justify-content:center;">
                                        @if($imgUrl)<img src="{{ $imgUrl }}" style="width:100%;height:100%;object-fit:cover;">@else<i class="fas fa-tag" style="color:var(--agri-text-muted);"></i>@endif
                                    </div>
                                    <div>
                                        <div style="font-weight:800;color:var(--agri-text-heading);font-size:15px;">{{ $category->name }}</div>
                                        <div style="font-size:10px;font-weight:800;color:var(--agri-primary);text-transform:uppercase;margin-top:4px;">{{ str_pad($category->id,8,'0',STR_PAD_LEFT) }} NODE</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:24px 32px;" class="text-center">
                                <span style="background:var(--agri-primary-light);color:var(--agri-primary);padding:6px 16px;border-radius:12px;font-size:12px;font-weight:800;border:1px solid rgba(0,0,0,0.05);">
                                    <i class="fas fa-layer-group me-2"></i>{{ $category->products_count }} Nodes Registered
                                </span>
                            </td>
                            <td style="padding:24px 32px;" class="text-center">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                    @if($category->active)
                                        <span style="background:var(--agri-primary-light);color:var(--agri-primary);padding:2px 10px;border-radius:100px;font-size:10px;font-weight:900;border:1px solid rgba(0,128,0,0.2);">LIVE</span>
                                    @else
                                        <span style="background:#F3F4F6;color:#6B7280;padding:2px 10px;border-radius:100px;font-size:10px;font-weight:900;border:1px solid #D1D5DB;">DRAFT</span>
                                    @endif
                                    <div class="form-check form-switch p-0 m-0">
                                        <input type="checkbox" class="form-check-input cat-toggle" data-id="{{ $category->id }}" {{ $category->active ? 'checked' : '' }} style="width:40px;height:20px;cursor:pointer;">
                                    </div>
                                </div>
                            </td>
                            <td style="padding:24px 32px;" class="text-end">
                                <div style="display:flex;justify-content:flex-end;gap:8px;">
                                    <a href="{{ $editUrl }}" class="btn-agri" style="padding:8px 12px;background:var(--agri-bg);color:var(--agri-text-heading);border-radius:10px;text-decoration:none;font-size:12px;font-weight:700;"><i class="fas fa-edit"></i></a>
                                    @if($canDel)
                                    <button class="btn-agri delete-btn" data-id="{{ $category->id }}" style="padding:8px 12px;background:#FEF2F2;color:var(--agri-error);border:none;border-radius:10px;"><i class="fas fa-trash-alt"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5" style="font-weight:800;color:var(--agri-text-muted);text-transform:uppercase;">TAXONOMIC REGISTRY IS EMPTY</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
@endsection
@section('scripts')
<script type="text/javascript">
    var csrfToken = '{{ csrf_token() }}';
    var checkDeletePermission = <?php echo json_encode(in_array('category.delete', json_decode(@session('admin_permissions'), true) ?? [])); ?>;

    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#categoriesTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

        $(document).on('click', '.toggle-publish-btn', function () {
            var id   = $(this).data('id');
            var active = $(this).data('active') == 1 ? 0 : 1;
            var btn  = $(this);
            $.ajax({
                url: '{{ url("admin/categories/toggle") }}/' + id,
                method: 'POST',
                data: { _token: csrfToken, active: active },
                success: function (res) {
                    if (res.success) {
                        btn.data('active', active);
                        if (active) {
                            btn.removeClass('bg-secondary').addClass('bg-success').text('Active');
                        } else {
                            btn.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                        }
                    }
                }
            });
        });

        $(document).on('click', '.delete-cat-btn', function () {
            var id = $(this).data('id');
            if (confirm("Delete this category?")) {
                $.ajax({
                    url: '{{ url("admin/categories/delete") }}/' + id,
                    method: 'POST',
                    data: { _method: 'DELETE', _token: csrfToken },
                    success: function () { location.reload(); },
                    error: function () { alert('Delete failed.'); }
                });
            }
        });

        if (checkDeletePermission) {
            $('#select-all').on('change', function () {
                $('.row-check').prop('checked', $(this).prop('checked'));
            });

            $('#bulk-delete-btn').on('click', function () {
                var ids = $('.row-check:checked').map(function () { return $(this).data('id'); }).get();
                if (!ids.length) { alert('Select at least one item.'); return; }
                if (!confirm('Delete ' + ids.length + ' selected categories?')) return;
                var reqs = ids.map(function (id) {
                    return $.ajax({
                        url: '{{ url("admin/categories/delete") }}/' + id,
                        method: 'POST',
                        data: { _method: 'DELETE', _token: csrfToken }
                    });
                });
                $.when.apply($, reqs).then(function () { location.reload(); });
            });
        }
    });
</script>
@endsection
