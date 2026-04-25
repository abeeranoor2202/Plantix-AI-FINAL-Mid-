@extends('vendor.layouts.app')
@section('title', 'Attributes')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Attributes</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Product Attributes</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage your own product attributes. Admin attributes are visible but read-only.</p>
        </div>
        <x-button :href="route('vendor.attributes.create')" variant="primary" icon="fas fa-plus">Create Attribute</x-button>
    </div>

    {{-- Filters --}}
    <x-card class="mb-4">
        <div class="p-3 p-lg-4">
            <form method="GET" action="{{ route('vendor.attributes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted small">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-agri border-start-0" style="margin-bottom:0;"
                               placeholder="Search by name" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Scope</label>
                    <select name="scope" class="form-agri">
                        <option value="">All Attributes</option>
                        <option value="mine"   {{ ($filters['scope'] ?? '') === 'mine'   ? 'selected' : '' }}>My Attributes</option>
                        <option value="global" {{ ($filters['scope'] ?? '') === 'global' ? 'selected' : '' }}>Admin / Global</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <x-button type="submit" variant="primary" class="w-100">Apply</x-button>
                    <x-button :href="route('vendor.attributes.index')" variant="outline" class="w-100">Clear</x-button>
                </div>
            </form>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card>
        @if($attributes->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border mb-3" style="width:72px;height:72px;">
                    <i class="fas fa-sliders-h text-muted fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No attributes found</h6>
                <p class="text-muted small mb-3">Create your first attribute to enrich your product listings.</p>
                <x-button :href="route('vendor.attributes.create')" variant="primary" icon="fas fa-plus">Create Attribute</x-button>
            </div>
        @else
            <x-table>
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3 small text-muted text-uppercase">Attribute</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Type</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Unit</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Options</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Owner</th>
                        <th class="px-4 py-3 small text-muted text-uppercase text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributes as $attr)
                        @php $isOwned = $attr->isOwnedByVendor(auth('vendor')->user()->vendor->id); @endphp
                        <tr>
                            {{-- Name --}}
                            <td class="px-4 py-3">
                                <div style="font-weight:700;color:var(--agri-text-heading);">{{ $attr->name ?: $attr->title }}</div>
                                @if($attr->categories_count > 0)
                                    <div style="font-size:11px;color:var(--agri-text-muted);">Used in {{ $attr->categories_count }} {{ Str::plural('category', $attr->categories_count) }}</div>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td class="px-4 py-3">
                                <span class="badge rounded-pill bg-light text-dark border" style="font-weight:700;font-size:11px;">
                                    {{ strtoupper($attr->type ?? 'TEXT') }}
                                </span>
                            </td>

                            {{-- Unit --}}
                            <td class="px-4 py-3">
                                <span style="color:var(--agri-text-muted);font-weight:600;">{{ $attr->unit ?: '—' }}</span>
                            </td>

                            {{-- Values count --}}
                            <td class="px-4 py-3">
                                @if(in_array($attr->type, ['select', 'multi-select']))
                                    <span style="font-weight:700;color:var(--agri-primary-dark);">{{ $attr->values_count }}</span>
                                    <span style="font-size:11px;color:var(--agri-text-muted);"> options</span>
                                @else
                                    <span style="color:var(--agri-text-muted);">—</span>
                                @endif
                            </td>

                            {{-- Owner badge --}}
                            <td class="px-4 py-3">
                                @if($attr->isGlobal())
                                    <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-shield-alt me-1"></i> Admin
                                    </span>
                                @elseif($isOwned)
                                    <span class="badge rounded-pill" style="background:#ecfdf5;color:#059669;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-store me-1"></i> You
                                    </span>
                                @else
                                    <span class="badge rounded-pill" style="background:#fef9c3;color:#92400e;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-user me-1"></i> {{ $attr->createdByVendor?->title ?? 'Vendor' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($isOwned)
                                        <a href="{{ route('vendor.attributes.edit', $attr->id) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-action-delete" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteAttrModal{{ $attr->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="btn-action" style="opacity:.35;cursor:not-allowed;" title="Read-only">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Delete modal (only for owned) --}}
                        @if($isOwned)
                        <div class="modal fade" id="deleteAttrModal{{ $attr->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('vendor.attributes.destroy', $attr->id) }}" method="POST" class="modal-content">
                                    @csrf @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Attribute</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-0">Are you sure you want to delete <strong>{{ $attr->name ?: $attr->title }}</strong>?
                                            @if($attr->categories_count > 0)
                                                <br><span class="text-danger small">This attribute is assigned to {{ $attr->categories_count }} {{ Str::plural('category', $attr->categories_count) }} — deletion will be blocked.</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </tbody>
            </x-table>

            @if($attributes->hasPages())
                <div style="padding:24px;border-top:1px solid var(--agri-border);display:flex;justify-content:center;">
                    {{ $attributes->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
