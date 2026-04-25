@extends('vendor.layouts.app')
@section('title', $category ? 'Edit Category' : 'New Category')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            {{-- Breadcrumb --}}
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                    <a href="{{ route('vendor.categories.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Categories</a>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                    <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $category ? 'Edit' : 'Create' }}</span>
                </div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                    {{ $category ? 'Edit Category' : 'Create New Category' }}
                </h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">
                    {{ $category ? 'Update your category details.' : 'Add a new category visible to all vendors and admin.' }}
                </p>
            </div>

            <div class="card-agri">
                <div class="card-body p-4 p-md-5">

                    @if($category)
                        <form action="{{ route('vendor.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                    @else
                        <form action="{{ route('vendor.categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                    @endif

                        {{-- Name --}}
                        <div class="mb-4">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3">
                                    <i class="fas fa-shapes text-muted"></i>
                                </span>
                                <input type="text" name="name"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('name') is-invalid @enderror"
                                       value="{{ old('name', $category?->name) }}"
                                       placeholder="e.g. Organic Fertilizers" required>
                            </div>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Description</label>
                            <textarea name="description" rows="4"
                                      class="form-control bg-light border-0 rounded-3 @error('description') is-invalid @enderror"
                                      placeholder="Short description of this category...">{{ old('description', $category?->description) }}</textarea>
                            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Image --}}
                        <div class="mb-4">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Category Image</label>
                            <div style="border: 2px dashed var(--agri-border); border-radius: 16px; padding: 28px; text-align: center; background: var(--agri-bg); position: relative; transition: 0.3s;" id="drop-zone">
                                <div id="imagePreview" class="mb-3">
                                    @if($category?->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" id="previewImg"
                                             style="width:90px;height:90px;border-radius:12px;object-fit:cover;border:2px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                    @else
                                        <div id="previewImg" style="width:72px;height:72px;border-radius:16px;background:white;display:inline-flex;align-items:center;justify-content:center;font-size:28px;color:var(--agri-text-muted);box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </div>
                                <p style="font-size:12px;color:var(--agri-text-muted);font-weight:600;margin-bottom:12px;">
                                    Click to upload an image (PNG, JPG — 512×512px recommended)
                                </p>
                                <input type="hidden" name="image_base64" id="imageBase64">
                                <input type="file" id="categoryImageInput" accept="image/*"
                                       style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-5 p-4 rounded-4 d-flex align-items-center justify-content-between"
                             style="background: var(--agri-bg); border: 1px solid var(--agri-border);">
                            <div>
                                <span class="d-block fw-bold text-dark mb-1" style="font-size: 15px;">Category Status</span>
                                <span class="d-block small text-muted">Toggle to make this category active or inactive</span>
                                <span id="statusBadge" class="badge rounded-pill mt-2"
                                      style="font-size:11px;font-weight:700;text-transform:uppercase;padding:4px 10px;
                                             background:{{ old('active', $category?->active ?? true) ? '#ecfdf5' : '#f1f5f9' }};
                                             color:{{ old('active', $category?->active ?? true) ? '#059669' : '#64748b' }};">
                                    {{ old('active', $category?->active ?? true) ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <input type="hidden" name="active" value="0">
                                <label class="cat-toggle-switch mb-0" for="activeToggle">
                                    <input type="checkbox" name="active" value="1" id="activeToggle"
                                           @checked(old('active', $category?->active ?? true))>
                                    <span class="cat-toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                            <x-button :href="route('vendor.categories.index')" variant="outline">Cancel</x-button>
                            <x-button type="submit" variant="primary" icon="fas fa-save">
                                {{ $category ? 'Update Category' : 'Create Category' }}
                            </x-button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cat-toggle-switch {
        position: relative; display: inline-block;
        width: 52px; height: 28px; cursor: pointer;
    }
    .cat-toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .cat-toggle-slider {
        position: absolute; inset: 0;
        background-color: #e2e8f0; border-radius: 28px; transition: background-color .3s;
    }
    .cat-toggle-slider::before {
        content: ""; position: absolute;
        width: 20px; height: 20px; left: 4px; top: 4px;
        background: white; border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,.15); transition: transform .3s;
    }
    .cat-toggle-switch input:checked + .cat-toggle-slider { background-color: var(--agri-primary, #16a34a); }
    .cat-toggle-switch input:checked + .cat-toggle-slider::before { transform: translateX(24px); }
    #drop-zone:hover { border-color: var(--agri-primary); }
</style>
@endpush

@push('scripts')
<script>
// Image preview with base64
document.getElementById('categoryImageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('imageBase64').value = e.target.result;
        document.getElementById('imagePreview').innerHTML =
            '<img src="' + e.target.result + '" style="width:90px;height:90px;border-radius:12px;object-fit:cover;border:2px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.1);">';
    };
    reader.readAsDataURL(file);
});

// Status toggle badge sync
const toggle = document.getElementById('activeToggle');
const badge  = document.getElementById('statusBadge');
toggle.addEventListener('change', function () {
    if (this.checked) {
        badge.textContent = 'Active';
        badge.style.background = '#ecfdf5';
        badge.style.color = '#059669';
    } else {
        badge.textContent = 'Inactive';
        badge.style.background = '#f1f5f9';
        badge.style.color = '#64748b';
    }
});
</script>
@endpush
