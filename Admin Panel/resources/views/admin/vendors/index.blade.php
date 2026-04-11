@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Vendors</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Vendors</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage and verify service partners.</p>
        </div>
        <a href="{{ route('admin.vendors.create') }}" class="btn-agri btn-agri-primary" style="height: 44px; border: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> Add Vendor
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Vendor List</h4>
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                    <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                </span>
                <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search vendors..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
            </div>
        </div>

        <div class="table-responsive">
            <table id="vendorTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Vendor</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Owner</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Phone</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Approval</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $vendor->title }}</div>
                                <div style="font-size: 12px; color: var(--agri-text-muted);">{{ \Illuminate\Support\Str::limit($vendor->description, 55) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-size: 14px; color: var(--agri-text-heading);">{{ $vendor->author?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div style="font-size: 14px; color: var(--agri-text-main);">{{ $vendor->phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $vendor->is_approved ? 'bg-success' : 'bg-warning' }}">{{ $vendor->is_approved ? 'Approved' : 'Pending' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill {{ $vendor->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.vendors.view', $vendor->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.vendors.delete', $vendor->id) }}" style="display: inline;" onsubmit="return confirm('Delete this vendor? If the vendor has orders, it will be archived instead.');">
                                        @csrf
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">No vendors found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vendors->hasPages())
            <div class="px-4 py-3 bg-white border-top">{{ $vendors->links() }}</div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#vendorTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

});
</script>
@endsection
