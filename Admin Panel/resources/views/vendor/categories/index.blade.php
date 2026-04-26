@extends('vendor.layouts.app')
@section('title', 'Categories')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Categories</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Product Categories</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage your own product categories. Admin categories are visible but read-only.</p>
        </div>
        <x-button :href="route('vendor.categories.create')" variant="primary" icon="fas fa-plus">Create Category</x-button>
    </div>

    {{-- Filters --}}
    <x-card class="mb-4">
        <div class="p-3 p-lg-4">
            <form method="GET" action="{{ route('vendor.categories.index') }}" class="row g-3 align-items-end">
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
                        <option value="">All Categories</option>
                        <option value="mine"   {{ ($filters['scope'] ?? '') === 'mine'   ? 'selected' : '' }}>My Categories</option>
                        <option value="global" {{ ($filters['scope'] ?? '') === 'global' ? 'selected' : '' }}>Admin / Global</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <x-button type="submit" variant="primary" class="w-100">Apply</x-button>
                    <x-button :href="route('vendor.categories.index')" variant="outline" class="w-100">Clear</x-button>
                </div>
            </form>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card>
        @if($categories->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border mb-3" style="width:72px;height:72px;">
                    <i class="fas fa-shapes text-muted fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No categories found</h6>
                <p class="text-muted small mb-3">Create your first category to start organising your products.</p>
                <x-button :href="route('vendor.categories.create')" variant="primary" icon="fas fa-plus">Create Category</x-button>
            </div>
        @else
            <x-table>
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3 small text-muted text-uppercase">Category</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Owner</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Products</th>
                        <th class="px-4 py-3 small text-muted text-uppercase">Status</th>
                        <th class="px-4 py-3 small text-muted text-uppercase text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        @php $isOwned = $cat->isOwnedByVendor(auth('vendor')->user()->vendor->id); @endphp
                        <tr>
                            {{-- Name + image --}}
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if($cat->image)
                                        <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}"
                                             style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid var(--agri-border);">
                                    @else
                                        <div style="width:40px;height:40px;border-radius:10px;background:var(--agri-bg);border:1px solid var(--agri-border);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-shapes text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700;color:var(--agri-text-heading);">{{ $cat->name }}</div>
                                        @if($cat->description)
                                            <div style="font-size:12px;color:var(--agri-text-muted);">{{ Str::limit($cat->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Owner badge --}}
                            <td class="px-4 py-3">
                                @if($cat->isGlobal())
                                    <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-shield-alt me-1"></i> Admin
                                    </span>
                                @elseif($isOwned)
                                    <span class="badge rounded-pill" style="background:#ecfdf5;color:#059669;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-store me-1"></i> You
                                    </span>
                                @else
                                    <span class="badge rounded-pill" style="background:#fef9c3;color:#92400e;font-weight:700;font-size:10px;padding:4px 10px;">
                                        <i class="fas fa-user me-1"></i> {{ $cat->createdByVendor?->title ?? 'Vendor' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Products count --}}
                            <td class="px-4 py-3">
                                <span style="font-weight:700;color:var(--agri-primary-dark);">{{ $cat->products_count }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                @if($isOwned)
                                    <form method="POST" action="{{ route('vendor.categories.toggle', $cat->id) }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <div class="form-check form-switch p-0 m-0">
                                            <input class="form-check-input ms-0" type="checkbox" role="switch"
                                                   @checked($cat->active) onchange="this.form.submit()"
                                                   style="width:36px;height:18px;cursor:pointer;">
                                        </div>
                                        <span class="badge rounded-pill"
                                              style="background:{{ $cat->active ? '#ecfdf5' : '#f1f5f9' }};color:{{ $cat->active ? '#059669' : '#64748b' }};font-weight:700;font-size:10px;text-transform:uppercase;padding:4px 10px;">
                                            {{ $cat->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </form>
                                @else
                                    <span class="badge rounded-pill"
                                          style="background:{{ $cat->active ? '#ecfdf5' : '#f1f5f9' }};color:{{ $cat->active ? '#059669' : '#64748b' }};font-weight:700;font-size:10px;text-transform:uppercase;padding:4px 10px;">
                                        {{ $cat->active ? 'Active' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($isOwned)
                                        <a href="{{ route('vendor.categories.edit', $cat->id) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-action-delete" title="Delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteCatModal{{ $cat->id }}"
                                                data-toggle="modal" data-target="#deleteCatModal{{ $cat->id }}">
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
                    @endforeach
                </tbody>
            </x-table>

            @foreach($categories as $cat)
                @if($cat->isOwnedByVendor(auth('vendor')->user()->vendor->id))
                    {{-- Delete modal (outside table for stability) --}}
                    <div class="modal fade" id="deleteCatModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('vendor.categories.destroy', $cat->id) }}" method="POST" class="modal-content">
                                @csrf @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <p class="text-muted mb-0">Are you sure you want to delete <strong>{{ $cat->name }}</strong>?
                                        @if($cat->products_count > 0)
                                            <br><span class="text-danger small">This category has {{ $cat->products_count }} product(s) — deletion will be blocked.</span>
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

            @if($categories->hasPages())
                <div style="padding:24px;border-top:1px solid var(--agri-border);display:flex;justify-content:center;">
                    {{ $categories->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
