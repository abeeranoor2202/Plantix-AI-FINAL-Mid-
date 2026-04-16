@extends('vendor.layouts.app')

@section('title', 'Return Reasons')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4" style="gap: 12px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin: 0;">Return Reasons</h1>
            <p class="text-muted mb-0">Manage return reason options used by your customers.</p>
        </div>
        <x-button :href="route('vendor.returns.index')" variant="outline" icon="fas fa-arrow-left">Back to Return Requests</x-button>
    </div>

    <x-card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Reason List</h4>
                <form method="GET" action="{{ route('vendor.return-reasons.index') }}" class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                    <div class="agri-search-wrap" style="width: 320px;">
                        <i class="fas fa-search agri-search-icon"></i>
                        <input type="text" name="search" class="form-agri agri-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search reasons...">
                    </div>

                    <select name="status" class="form-agri" style="height: 42px; min-width: 140px; margin-bottom: 0;">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                    </select>

                    <x-button type="submit" variant="primary">Apply Filters</x-button>
                    <x-button :href="route('vendor.return-reasons.index')" variant="outline">Clear</x-button>
                </form>
            </div>
        </x-slot>

        <x-table>
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Name</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Description</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Status</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Usage Count</th>
                    <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reasons as $reason)
                    @php
                        $isOwned = (int) ($reason->vendor_id ?? 0) === (int) auth('vendor')->user()->vendor->id;
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark">{{ $reason->name }}</div>
                            <small class="text-muted">Created {{ $reason->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::limit($reason->description ?: 'No description added.', 85) }}</td>
                        <td class="px-4 py-3">
                            @if($isOwned)
                                <form method="POST" action="{{ route('vendor.return-reasons.toggle', $reason->id) }}" class="d-inline-flex align-items-center" style="gap: 8px;">
                                    @csrf
                                    @method('PATCH')
                                    <x-toggle :checked="$reason->is_active" onchange="this.form.submit()" />
                                    <x-badge :variant="$reason->is_active ? 'success' : 'secondary'">{{ $reason->is_active ? 'Active' : 'Inactive' }}</x-badge>
                                </form>
                            @else
                                <x-badge :variant="$reason->is_active ? 'success' : 'secondary'">{{ $reason->is_active ? 'Active' : 'Inactive' }}</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="info">{{ $reason->returns_count }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-end d-flex justify-content-end" style="gap: 8px;">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center js-view-reason"
                                    style="width: 34px; height: 34px;"
                                    title="View"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewReasonModal"
                                    data-name="{{ $reason->name }}"
                                    data-description="{{ $reason->description }}"
                                    data-status="{{ $reason->is_active ? 'Active' : 'Inactive' }}"
                                    data-usage="{{ $reason->returns_count }}"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($isOwned)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center js-edit-reason"
                                        style="width: 34px; height: 34px;"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editReasonModal"
                                        data-id="{{ $reason->id }}"
                                        data-name="{{ $reason->name }}"
                                        data-description="{{ $reason->description }}"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <form method="POST" action="{{ route('vendor.return-reasons.destroy', $reason->id) }}" class="d-inline" onsubmit="return confirm('Delete this return reason?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Edit unavailable" disabled>
                                        <i class="fas fa-pen text-muted"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Delete unavailable" disabled>
                                        <i class="fas fa-trash text-muted"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fs-2 d-block mb-2"></i>
                                <div class="fw-bold">No return reasons yet</div>
                                <div class="small">Create your first reason to help customers submit accurate return requests.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if($reasons->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $reasons->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </x-card>

    <x-card class="mt-4">
        <div class="p-4">
            <h5 class="mb-3">Add New Return Reason</h5>
            <form method="POST" action="{{ route('vendor.return-reasons.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label text-muted small">Name</label>
                    <input type="text" name="reason" class="form-agri" required maxlength="255" value="{{ old('reason') }}" placeholder="e.g. Item arrived damaged">
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted small">Description</label>
                    <input type="text" name="description" class="form-agri" maxlength="1000" value="{{ old('description') }}" placeholder="Optional context shown in management screens">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small">Status</label>
                    <select name="is_active" class="form-agri">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <x-button type="submit" variant="primary" class="w-100">Add</x-button>
                </div>
            </form>
        </div>
    </x-card>
</div>

<div class="modal fade" id="viewReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Return Reason Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><strong>Name:</strong> <span id="viewReasonName"></span></div>
                <div class="mb-2"><strong>Description:</strong> <span id="viewReasonDescription"></span></div>
                <div class="mb-2"><strong>Status:</strong> <span id="viewReasonStatus"></span></div>
                <div><strong>Usage Count:</strong> <span id="viewReasonUsage"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editReasonForm" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title">Edit Return Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Name</label>
                    <input type="text" name="reason" id="editReasonName" class="form-agri" required maxlength="255">
                </div>
                <div>
                    <label class="form-label text-muted small">Description</label>
                    <textarea name="description" id="editReasonDescription" class="form-agri" rows="4" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .agri-search-wrap {
        position: relative;
    }

    .agri-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--agri-text-muted);
        font-size: 14px;
        pointer-events: none;
    }

    .agri-search-input {
        margin-bottom: 0;
        height: 42px;
        padding-left: 36px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewName = document.getElementById('viewReasonName');
    const viewDescription = document.getElementById('viewReasonDescription');
    const viewStatus = document.getElementById('viewReasonStatus');
    const viewUsage = document.getElementById('viewReasonUsage');

    document.querySelectorAll('.js-view-reason').forEach(function (btn) {
        btn.addEventListener('click', function () {
            viewName.textContent = this.getAttribute('data-name') || '-';
            viewDescription.textContent = this.getAttribute('data-description') || 'No description added.';
            viewStatus.textContent = this.getAttribute('data-status') || '-';
            viewUsage.textContent = this.getAttribute('data-usage') || '0';
        });
    });

    const editForm = document.getElementById('editReasonForm');
    const editName = document.getElementById('editReasonName');
    const editDescription = document.getElementById('editReasonDescription');

    document.querySelectorAll('.js-edit-reason').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            editForm.action = '{{ route('vendor.return-reasons.update', '__ID__') }}'.replace('__ID__', id);
            editName.value = this.getAttribute('data-name') || '';
            editDescription.value = this.getAttribute('data-description') || '';
        });
    });
});
</script>
@endpush
