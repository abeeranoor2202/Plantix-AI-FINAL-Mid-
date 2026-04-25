@extends('vendor.layouts.app')
@section('title', 'Return Reasons')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    {{-- Breadcrumbs --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('vendor.returns.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Returns</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Return Reasons</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Return Reasons</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Customize the options customers can select when requesting a return.</p>
            </div>
            <a href="{{ route('vendor.returns.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to Returns
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Add New Reason Card --}}
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 32px; background: white; position: sticky; top: 24px;">
                <h5 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 8px;">
                    <i class="fas fa-plus-circle me-2" style="color: var(--agri-primary);"></i> Add New Reason
                </h5>
                <p class="text-muted mb-4" style="font-size: 13px; line-height: 1.5;">Define a new return category to help categorize customer return requests effectively.</p>

                <form method="POST" action="{{ route('vendor.return-reasons.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Reason Title <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-agri @error('reason') is-invalid @enderror" required maxlength="255" value="{{ old('reason') }}" placeholder="e.g. Damaged during shipping">
                        @error('reason') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Description <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="description" class="form-agri @error('description') is-invalid @enderror" rows="4" maxlength="1000" placeholder="Provide extra details for internal use...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Initial Status</label>
                        <select name="is_active" class="form-agri">
                            <option value="1" @selected(old('is_active', '1') === '1')>Active (Visible to customers)</option>
                            <option value="0" @selected(old('is_active') === '0')>Inactive (Hidden)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; height: 48px; font-weight: 700;">
                        <i class="fas fa-plus me-2"></i> Save Return Reason
                    </button>
                </form>
            </div>
        </div>

        {{-- Reasons List --}}
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Active Reasons</h4>
                    <form method="GET" action="{{ route('vendor.return-reasons.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="agri-search-wrap" style="width: 260px;">
                            <i class="fas fa-search agri-search-icon"></i>
                            <input type="text" name="search" class="form-agri agri-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search reasons...">
                        </div>
                        <select name="status" class="form-agri" style="height: 42px; min-width: 130px; margin-bottom: 0;">
                            <option value="">All Status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                        <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reason</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                                <th class="text-center" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Usage</th>
                                <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reasons as $reason)
                                @php $isOwned = (int) ($reason->vendor_id ?? 0) === (int) auth('vendor')->user()->vendor->id; @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $reason->name }}</div>
                                        <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 2px;">
                                            {{ Str::limit($reason->description ?: 'No additional context.', 60) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($isOwned)
                                            <form method="POST" action="{{ route('vendor.return-reasons.toggle', $reason->id) }}" class="d-flex align-items-center gap-2">
                                                @csrf @method('PATCH')
                                                <div class="form-check form-switch p-0 m-0">
                                                    <input class="form-check-input ms-0" type="checkbox" role="switch" @checked($reason->is_active) onchange="this.form.submit()" style="width: 36px; height: 18px; cursor: pointer;">
                                                </div>
                                                <span class="badge rounded-pill" style="background: {{ $reason->is_active ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $reason->is_active ? '#059669' : '#64748b' }}; font-weight: 700; font-size: 10px; text-transform: uppercase; padding: 4px 10px;">
                                                    {{ $reason->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </form>
                                        @else
                                            <span class="badge rounded-pill" style="background: #f1f5f9; color: #64748b; font-weight: 700; font-size: 10px; text-transform: uppercase; padding: 4px 10px; border: 1px solid #e2e8f0;">Global</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span style="font-weight: 800; font-size: 14px; color: var(--agri-primary-dark);">{{ $reason->returns_count }}</span>
                                        <div style="font-size: 10px; color: var(--agri-text-muted); font-weight: 700; text-transform: uppercase;">Requests</div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn-action btn-action-view js-view-reason" title="View"
                                                data-bs-toggle="modal" data-bs-target="#viewReasonModal"
                                                data-name="{{ $reason->name }}" data-description="{{ $reason->description }}"
                                                data-status="{{ $reason->is_active ? 'Active' : 'Inactive' }}" data-usage="{{ $reason->returns_count }}">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            @if($isOwned)
                                                <button type="button" class="btn-action btn-action-edit js-edit-reason" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#editReasonModal"
                                                    data-id="{{ $reason->id }}" data-name="{{ $reason->name }}" data-description="{{ $reason->description }}">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <form method="POST" action="{{ route('vendor.return-reasons.destroy', $reason->id) }}" onsubmit="return confirm('Delete this return reason?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div style="color: var(--agri-text-muted); font-weight: 600;">No return reasons found.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reasons->hasPages())
                    <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                        {{ $reasons->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="viewReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color: var(--agri-primary-dark);">Reason Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="mb-3">
                    <label class="agri-label-small">Reason Name</label>
                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 16px;" id="viewReasonName"></div>
                </div>
                <div class="mb-3">
                    <label class="agri-label-small">Description</label>
                    <div style="color: var(--agri-text-muted); line-height: 1.6;" id="viewReasonDescription"></div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label class="agri-label-small">Status</label>
                        <div id="viewReasonStatus"></div>
                    </div>
                    <div class="col-6">
                        <label class="agri-label-small">Total Usage</label>
                        <div style="font-weight: 800; font-size: 18px; color: var(--agri-primary);" id="viewReasonUsage"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editReasonForm" class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            @csrf @method('PATCH')
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color: var(--agri-primary-dark);">Edit Return Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Reason Title</label>
                    <input type="text" name="reason" id="editReasonName" class="form-agri" required maxlength="255">
                </div>
                <div>
                    <label class="form-label fw-bold small text-dark">Internal Description</label>
                    <textarea name="description" id="editReasonDescription" class="form-agri" rows="4" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-agri btn-agri-primary px-4">Update Reason</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .agri-search-wrap { position: relative; }
    .agri-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px; pointer-events: none; }
    .agri-search-input { margin-bottom: 0; height: 42px; padding-left: 36px; }
    .agri-label-small { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--agri-text-muted); margin-bottom: 4px; display: block; letter-spacing: 0.5px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── View modal ────────────────────────────────────────────────────────────
    const viewName        = document.getElementById('viewReasonName');
    const viewDescription = document.getElementById('viewReasonDescription');
    const viewStatus      = document.getElementById('viewReasonStatus');
    const viewUsage       = document.getElementById('viewReasonUsage');

    document.querySelectorAll('.js-view-reason').forEach(function (btn) {
        btn.addEventListener('click', function () {
            viewName.textContent        = this.dataset.name        || '-';
            viewDescription.textContent = this.dataset.description || 'No description added.';
            viewStatus.textContent      = this.dataset.status      || '-';
            viewUsage.textContent       = this.dataset.usage       || '0';
        });
    });

    // ── Edit modal ────────────────────────────────────────────────────────────
    const editForm        = document.getElementById('editReasonForm');
    const editName        = document.getElementById('editReasonName');
    const editDescription = document.getElementById('editReasonDescription');
    const updateBaseUrl   = "{{ rtrim(route('vendor.return-reasons.index'), '/') }}/";

    document.querySelectorAll('.js-edit-reason').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            editForm.action       = updateBaseUrl + id;
            editName.value        = this.dataset.name        || '';
            editDescription.value = this.dataset.description || '';
        });
    });
});
</script>
@endpush
